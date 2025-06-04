<?php

namespace App\Traits;

trait VoteTrait
{
    use RankTrait;

    protected function sendVoteNotifications($user, $participant, $contest, $quantity)
    {
        $organizer = $contest->organizer;
        // Send to organizer
        sendNotification(
            'ORG_NEW_VOTE',
            $organizer,
            [
                'username'        => $organizer->username ?? '',
                'contest_name'    => $contest->title,
                'total_votes'     => $contest->votes->count(),
                'contestant_name' => $participant->title ?? '',
                'time_remaining'  => $contest->voting_end_date->diffForHumans(),
            ],
            [
                'user_id'       => $organizer->id,
                'contest_id'    => $contest->id,
                'contestant_id' => $participant->id,
                'type'          => 'ORGANIZER_VOTE_RECEIVED',
            ]
        );

        // notify user (voter)

        // send notification to participant
        sendNotification(
            'VOTE_RECEIVED',
            $participant->user,
            [
                'username'       => $participant->user->username ?? '',
                'contest_name'   => $contest->title,
                'total_votes'    => $participant->vote_count,
                'current_rank'   => $this->getRank($participant),
                'time_remaining' => $contest->voting_end_date->diffForHumans(),
            ],
            [
                'user_id'    => $participant->user_id,
                'contest_id' => $contest->id,
                'entry_id'   => $participant->id,
                'type'       => 'VOTE_RECEIVED',
            ]
        );

    }

    protected function sendGuestVoteNotifications($participant, $contest, $quantity)
    {
        // send notification to participant
        sendNotification(
            'VOTE_RECEIVED',
            $participant->user,
            [
                'username'       => $participant->user->username ?? '',
                'contest_name'   => $contest->title,
                'total_votes'    => $participant->vote_count,
                'current_rank'   => $this->getRank($participant),
                'time_remaining' => $contest->voting_end_date->diffForHumans(),
            ],
            [
                'user_id'       => $participant->user_id,
                'contest_id'    => $contest->id,
                'contestant_id' => $participant->id,
                'type'          => 'VOTE_RECEIVED',
            ]
        );
        $organizer = $contest->organizer;
        // Send to organizer
        sendNotification(
            'ORG_NEW_VOTE',
            $organizer,
            [
                'username'        => $organizer->username ?? '',
                'contest_name'    => $contest->title,
                'total_votes'     => $contest->votes->count(),
                'contestant_name' => $participant->title ?? '',
                'time_remaining'  => $contest->voting_end_date->diffForHumans(),
            ],
            [
                'user_id'       => $organizer->id,
                'contest_id'    => $contest->id,
                'contestant_id' => $participant->id,
                'type'          => 'ORGANIZER_VOTE_RECEIVED',
            ]
        );
    }

    /**
     * Checks if voting is allowed for the given contest.
     *
     * @param  \App\Models\Contest  $contest
     * @return bool
     */
    protected function isVotingAllowed($contest)
    {
        return $contest->status === 'active' &&
            now()->between($contest->voting_start_date, $contest->voting_end_date);
    }
}
