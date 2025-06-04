<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contest;
use App\Models\Submission;
use App\Services\NotificationService;
use App\Traits\ApiResponse;
use App\Traits\ContestTrait;
use App\Traits\ParticipantTrait;
use Auth;
use Illuminate\Http\Request;
use Purify;

class ContestController extends Controller
{
    use ApiResponse, ContestTrait, ParticipantTrait;

    // fetch categories
    public function categories(Request $request)
    {
        $categories = cache()->remember('contest_categories', 60 * 60, function () {
            return Category::all();
        });
        $activeCategories = $categories->filter(function ($category) {
            return $category->status === 'enabled';
        });

        return $this->successResponse('Categories retrieved successfully', $activeCategories, 200);
    }

    // get all contests
    public function index(Request $request)
    {
        $pp = $request->count ?? 20;
        $page = $request->input('page', 1);
        $user = Auth::user();
        $query = Contest::whereOrganizerId($user->id)->orderByDesc('id');

        if ($request->status) {
            $query->whereStatus($request->status);
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->entry_type) {
            $query->where('entry_type', $request->entry_type);
        }

        if ($request->search) {
            $query->searchContest($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn($item) => $this->contestObject($item),
            $pp,
        );

        return $this->paginatedResponse('Contests retrieved successfully.', $objectData, $result, 200);
    }

    // create contest
    public function store(Request $request)
    {
        $validated = $this->validateContest($request);
        $req = Purify::clean($validated);
        $user = Auth::user();
        $slug = uniqueSlug($req['title'], 'contests');
        $image = null;

        if ($request->image != null) {
            $image = $this->moveImage('contests', $request->image);
        }
        if (sys_setting('contest_status') != 1) {
            return $this->errorResponse('Contest Creation is currently disabled.', 403);
        }
        $status = 'pending';

        if (sys_setting('contest_approval') == 1) {
            $status = 'active';
        }
        $amount = $this->getContestPrice($req['type']);
        $entryPrice = $this->getEntryPrice($req['entry_type']);
        $contest = Contest::create([
            'title'             => $req['title'],
            'category_id'       => $req['category_id'],
            'description'       => $req['description'],
            'type'              => $req['type'],
            'amount'            => $amount ?? 0,
            'image'             => $image,
            'entry_type'        => $req['entry_type'],
            'entry_fee'         => $entryPrice,
            'prize'             => $req['prize'],
            'status'            => $status,
            'start_date'        => $req['start_date'],
            'end_date'          => $req['end_date'],
            'voting_start_date' => $req['voting_start_date'] ?? null,
            'voting_end_date'   => $req['voting_end_date'] ?? null,
            'max_entries'       => $req['max_entries'] ?? null,
            'rules'             => $req['rules'],
            'requirements'      => $req['requirements'],
            'slug'              => $slug,
            'organizer_id'      => $user->id,
        ]);

        // send notification to admin
        notifyAdmin('ADMIN_NEW_CONTEST', [
            'contest_name'     => $contest->title,
            'organizer_name'   => $user->full_name,
            'contest_category' => $contest->category->name ?? '',
            'prize_pool'       => $contest->prize,
            'entry_fee'        => $contest->entry_fee,
            'contest_duration' => show_datetime($contest->end_date),
            'contest_link'     => route('admin.contest.show', ['id' => $contest->id]),
            'link'             => route('admin.contest.show', ['id' => $contest->id]),

        ], [
            'user_id'      => $user->id,
            'contest_id'   => $contest->id,
            'contest_name' => $contest->title,
            'type'         => 'ADMIN_NEW_ENTRY',
        ]);

        return $this->successResponse('Contest created successfully', $this->contestObject($contest), 201);
    }

    // show contest
    public function show($id)
    {
        $contest = Contest::whereOrganizerId(Auth::id())->whereId($id)->first();

        if (! $contest) {
            return $this->errorResponse('Contest not found.', 404);
        }

        return $this->successResponse('Contest retrieved successfully', $this->contestObjectFull($contest), 200);
    }

    // update contest
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        $contest = Contest::whereOrganizerId($user->id)->whereId($id)->first();

        if (! $contest) {
            return $this->notFoundResponse('Contest not found.');
        }

        if ($contest->status === 'completed') {
            return $this->forbiddenResponse('You can\'t update this contest.');
        }
        $validated = $this->updateValidation($request);
        $req = Purify::clean($validated);

        // Image
        if ($request->image != null) {
            $image = $this->moveImage('contests', $request->image);
            $req['image'] = $image;
        }
        // update contest
        $contest->update($req);

        return $this->successResponse('Contest updated successfully', $this->contestObject($contest), 200);
    }

    public function endContest(Request $request, $id)
    {
        $user = Auth::user();
        $contest = Contest::whereOrganizerId($user->id)->whereId($id)->first();

        if (! $contest) {
            return $this->notFoundResponse('Contest not found.');
        }

        if ($contest->status != 'active') {
            return $this->errorResponse('You can only end an active contest.');
        }

        $this->closeContest($contest);

        return $this->successResponse('Contest Closed Successfully');
    }

    // delete contest
    public function destroy($id)
    {
        $user = Auth::user();
        $contest = Contest::whereOrganizerId($user->id)->whereId($id)->first();

        if (! $contest) {
            return $this->errorResponse('Contest not found.', 404);
        }

        if (sys_setting('contest_delete') != 1) {
            return $this->errorResponse('Contest Deletion is currently disabled.', 403);
        }
        $contest->delete();

        return $this->successResponse('Contest deleted successfully.');
    }

    // fetch all participants
    public function participants(Request $request, $id)
    {
        $contest = Contest::find($id);

        if (! $contest) {
            return $this->errorResponse('Contest not found.', 404);
        }
        $pp = $request->count ?? 30;
        $query = Submission::whereContestId($id)->whereType('entry')->orderByDesc('id');

        if ($request->search) {
            $query->search($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn($item) => $this->participantObject($item),
            $pp
        );

        return $this->paginatedResponse('Participants retrieved successfully.', $objectData, $result, 200);
    }

    public function submissions(Request $request, $id)
    {
        $contest = Contest::find($id);

        if (! $contest) {
            return $this->errorResponse('Contest not found.', 404);
        }
        $pp = $request->count ?? 30;
        $query = Submission::whereContestId($id)->whereType('submission')->orderByDesc('id');

        if ($request->search) {
            $query->search($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn($item) => $this->submissionObject($item),
            $pp
        );

        return $this->paginatedResponse('Submissions retrieved successfully.', $objectData, $result, 200);
    }

    // leaderboards:
    public function leaderboards(Request $request, $id)
    {
        $contest = Contest::whereOrganizerId(Auth::id())->whereId($id)->with('participants')->first();

        if (! $contest) {
            return $this->errorResponse('Contest not found.', 404);
        }
        $rankedParticipants = $contest->participants()->where('vote_status', 'enabled')->orderByDesc('vote_count')->get();
        $leaderboard = $rankedParticipants->transform(function ($item) {
            return $this->rankObject($item);
        });

        return $this->successResponse('Contest Leaderboards.', $leaderboard);
    }

    // send notifications to participants
    public function sendNotification(Request $request, $id)
    {
        $request->validate([
            'title'   => 'required|string|max:50',
            'message' => 'required|string|max:300',
        ]);

        $contest = Contest::whereOrganizerId(Auth::id())->whereId($id)->first();

        if (! $contest) {
            return $this->errorResponse('Contest not found.', 404);
        }
        // send notifications to participants
        $ns = new NotificationService();
        $participants = Submission::whereContestId($contest->id)->with('user')->select('user_id')->distinct()->whereType('entry')->get();
        foreach ($participants as $participant) {
            $user = $participant->user;

            if ($user) {
                $user->notifys()->create([
                    'title'   => $request->title,
                    'message' => $request->message,
                ]);
                $ns->sendCustom($user, [
                    'title'   => $request->title,
                    'message' => $request->message,
                ], ['push']);
            }
        }

        return $this->successResponse('Notification sent successfully.');
    }
}
