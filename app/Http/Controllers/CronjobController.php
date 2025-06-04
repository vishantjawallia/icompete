<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Admin\EmailController;
use App\Jobs\SendRankingNotification;
use App\Models\CoinPayment;
use App\Models\Contest;
use App\Models\LoginHistory;
use App\Models\Newsletter;
use App\Models\Notify;
use App\Models\Setting;
use App\Models\Submission;
use App\Models\User;
use App\Traits\ContestTrait;
use Illuminate\Http\Request;

class CronjobController extends Controller
{
    use ContestTrait;

    public function initCronjob(Request $request)
    {
        $this->scheduledEmail();
        $this->runCommands();
        $this->deleteOldPayments();
        $this->contestCron();
        $this->deleteLoginHistory();
        $this->deleteOldSubmissions();
        // $this->deleteOldAccessTokens();
        $this->deleteOldNotifications();

        // update last cron job
        $sett = Setting::first();
        $sett->last_cron = now();
        $sett->save();

        return 'success';
    }

    // contests cron jobs
    public function contestCron()
    {
        // contest statuses
        $this->openContests();
        $this->closeContests();

        // ranking
        if (date('H') % 6 == 0) {
            $cacheKey = 'ranking_notifications_' . date('Y-m-d_H');

            if (! cache()->has($cacheKey)) {
                $this->sendRankingNotifications();
                cache()->put($cacheKey, true, now()->addHours(6));
            }
        }
        // send available contests notification to voters
        $hour = (int) date('H');

        if ($hour === 7 || $hour === 19) {
            $cacheKey = 'available_contests_' . date('Y-m-d_H');

            if (! cache()->has($cacheKey)) {
                $this->availableContests();
                cache()->put($cacheKey, true, now()->addHours(1));
            }
        }
    }

    private function openContests()
    {
        $now = now();
        // Find contests that should be opened
        $contestsToOpen = Contest::where('status', 'active')
            ->where('voting_start_date', '<=', $now)->where('start_notify', 0)
            ->get();

        foreach ($contestsToOpen as $contest) {
            // Update status
            $contest->update(['start_notify' => 1]);

            // Notify organizer
            sendNotification('ORG_VOTING_START', $contest->organizer, [
                'username'     => $contest->organizer->username,
                'contest_name' => $contest->title,
                'entry_count'  => $contest->entry->count(),
                'voting_end'   => $contest->voting_end_date->diffForHumans(),
            ], [
                'user_id'    => $contest->organizer_id,
                'contest_id' => $contest->id,
                'type'       => 'ORGANIZER_VOTING_START',
            ]);

            // Notify participants
            $notifiedUsers = [];
            foreach ($contest->participants()->get() as $participant) {
                // Skip if we've already notified this user
                if (in_array($participant->user_id, $notifiedUsers)) {
                    continue;
                }
                sendNotification('VOTING_START', $participant->user, [
                    'username'        => $participant->user->username,
                    'contest_name'    => $contest->title,
                    'voting_duration' => $contest->voting_end_date->diffForHumans(),
                    'entry_count'     => $contest->entry->count(),
                    'prize_pool'      => $contest->prize,
                    'time_remaining'  => $contest->end_date->diffForHumans(),
                ], [
                    'user_id'    => $participant->user_id,
                    'contest_id' => $contest->id,
                    'entry_id'   => $participant->id,
                    'type'       => 'CONTEST_STARTED',
                ]);

                // Add user to notified list
                $notifiedUsers[] = $participant->user_id;
            }
        }
    }

    private function closeContests()
    {
        $now = now();
        // Find contests that should be closed
        $contestsToClose = Contest::whereIn('status', ['active', 'ongoing'])
            ->where('voting_end_date', '<=', $now)->where('voting_ended', 0)
            ->get();

        foreach ($contestsToClose as $contest) {
            $this->closeContest($contest);
        }
    }

    private function availableContests()
    {
        // get contestants
        $contestants = User::where('role', 'contestant')->get();
        foreach ($contestants as $contestant) {
            // Get contests approved ny admin in the last 24 hours
            $now = now();
            $activeContests = Contest::whereIn('status', ['active'])
                ->where('start_date', '<=', $now)
                ->where('created_at', '>=', $now->subDay())
                ->get();

            // if contests has participants
            if ($activeContests->count() > 0) {
                $randomContest = $activeContests->random();
                // send available contests notification to contestants
                sendNotification('AVAILABLE_CONTESTS', $contestant, [
                    'username'      => $contestant->username,
                    'contest_count' => $activeContests->count(),
                    'contest_name'  => $randomContest->title,
                    'contest_desc'  => $randomContest->description,
                    'total_prize'   => $randomContest->prize,
                    'entry_fee'     => $randomContest->entry_fee,
                ], [
                    'user_id'       => $contestant->id,
                    'contest_id'    => $randomContest->id,
                    'contest_count' => $activeContests->count(),
                    'type'          => 'AVAILABLE_CONTESTS',
                ]);
            }
        }
    }

    private function sendRankingNotifications()
    {
        $now = now();
        $activeContests = Contest::whereIn('status', ['active'])
            ->where('start_date', '<=', $now)
            ->where('end_date', '>=', $now)
            ->where(function ($query) use ($now) {
                $query->whereNull('rank_notification_time')
                      ->orWhere('rank_notification_time', '<=', $now);
            })
            ->with('participants')
            ->cursor();

        foreach ($activeContests as $contest) {
            // Update rank_notification_time to a random interval (e.g., 1-3 hours later)
            $contest->update(['rank_notification_time' => $now->addMinutes(rand(60, 180))]);

            if ($contest->participants()->count() > 1) {
                $notifiedUsers = [];
                $topParticipants = Submission::whereType('entry')->where('contest_id', $contest->id)
                    ->orderBy('vote_count', 'desc')->take(5)->get();

                foreach ($topParticipants as $index => $participant) {
                    if ($participant->vote_count > 0) {
                        $cacheKey = "contest_{$contest->id}_user_{$participant->user_id}_rank";
                        $newRank = $this->calculateRank($participant);
                        $lastRank = cache()->get($cacheKey);

                        if ($lastRank !== $newRank) {
                            cache()->put($cacheKey, $newRank, now()->addHours(6));

                            $delay = now()->addSeconds($index * 10);
                            SendRankingNotification::dispatch(
                                'RANK_UPDATE',
                                $participant->user,
                                [
                                    'username'        => $participant->user->username,
                                    'contest_name'    => $contest->title,
                                    'new_rank'        => $newRank,
                                    'vote_count'      => $participant->vote_count,
                                    'hours_remaining' => intval($contest->voting_end_date->diffInHours($now)) . ' ' . __('Hours'),
                                ],
                                [
                                    'user_id'    => $participant->user_id,
                                    'contest_id' => $contest->id,
                                    'rank'       => $newRank,
                                    'type'       => 'CONTEST_RANKING',
                                ]
                            )->delay($delay);

                            $notifiedUsers[] = $participant->user_id;
                        }
                    }
                }
            }
        }
    }

    private function calculateRank($item)
    {
        if (! $item->contest) {
            return 'N/A';
        }

        if ($item->vote_count <= 0) {
            return 'N/A';
        }

        $cacheKey = "contest_{$item->contest_id}_submissions";

        $submissions = \Cache::remember($cacheKey, 10 * 60, function () use ($item) {
            return Submission::where('contest_id', $item->contest_id)
                ->where('type', 'entry')
                ->where('vote_status', 'enabled')
                ->where('vote_count', '>', 0)
                ->orderByDesc('vote_count')
                ->select(['id', 'vote_count'])
                ->get();
        });

        // Use collection methods for better performance
        $rank = $submissions->pluck('id')->search($item->id);

        return $rank !== false ? $rank + 1 : 'N/A';
    }

    /**
     * Sends scheduled newsletters
     *
     * @return void
     */
    public function scheduledEmail()
    {
        $currentDateTime = \Carbon\Carbon::now();
        $newsletters = Newsletter::where('status', 2)->where('date', '<=', $currentDateTime)->get();

        foreach ($newsletters as $newsletter) {
            $newsletter->status = 1;
            $newsletter->save();

            try {
                $emc = new EmailController();
                $emc->dispatchNewsletterJobs($newsletter);

                return true;
            } catch (\Exception $e) {
                \Log::error('Newsletter Processing Error: ' . $e->getMessage());

                return false;
            }
        }
    }

    /**
     * Run all scheduled commands.
     *
     * @return void
     */
    public function runCommands()
    {
        \Artisan::call('schedule:run');

        return 'success';
    }

    /**
     * Deletes CoinPayment records that are pending and older than 5 days.
     *
     * @return string Returns "success" once the operation is complete.
     */
    public function deleteOldPayments()
    {
        $daysAgo = \Carbon\Carbon::now()->subDays(5);
        // Retrieve deposits matching the specified criteria and delete them
        CoinPayment::where('status', 'pending')->where('created_at', '<', $daysAgo)->delete();

        return 'success';
    }

    /**
     * Deletes LoginHistory records that are older than 17 days.
     *
     * @return int Returns the number of deleted records.
     */
    public function deleteLoginHistory()
    {
        $sevenDaysAgo = \Carbon\Carbon::now()->subDays(17);
        $deleted = LoginHistory::where('created_at', '<', $sevenDaysAgo)->delete();

        return $deleted;
    }

    /**
     * Deletes contests that are older than 1 year.     *
     *
     * @return int Returns the number of deleted records.
     */
    public function deleteOldContests()
    {
        $oneYearAgo = \Carbon\Carbon::now()->subYear();
        $deleted = Contest::where('created_at', '<', $oneYearAgo)->delete();

        return $deleted;
    }

    /**
     * Deletes submissions that are older than 1 year.
     *
     * @return int Returns the number of deleted records.
     */
    public function deleteOldSubmissions()
    {
        $oneYearAgo = \Carbon\Carbon::now()->subYear();
        $deleted = Submission::where('created_at', '<', $oneYearAgo)->delete();

        return $deleted;
    }

    /**
     * Deletes old or unused personal access tokens.
     *
     * @return array Returns the number of deleted records for each condition.
     */
    public function deleteOldAccessTokens()
    {
        $twoMonthsAgo = now()->subMonths(2);
        $oneDayAgo = now()->subDay();

        $unusedTokensDeleted = \DB::table('personal_access_tokens')
            ->where('last_used_at', null)
            ->where('created_at', '<', $oneDayAgo)
            ->delete();

        $oldTokensDeleted = \DB::table('personal_access_tokens')
            ->where('created_at', '<', $twoMonthsAgo)
            ->delete();

        return [
            'unused_tokens_deleted' => $unusedTokensDeleted,
            'old_tokens_deleted' => $oldTokensDeleted
        ];
    }

    public function deleteOldNotifications()
    {
        $timeAgo = \Carbon\Carbon::now()->subWeek();
        $monthAgo = \Carbon\Carbon::now()->subMonths(2);
        Notify::where('created_at', '<', $monthAgo)->delete(); // notifications over 2 months
        $deleted = Notify::where('created_at', '<', $timeAgo)->whereNotNull('read_at')->delete();
        return $deleted;

    }
}
