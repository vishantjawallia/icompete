<?php

namespace App\Http\Controllers;

use App\Models\CoinBalance;
use App\Models\CoinTransaction;
use App\Models\Submission;
use App\Models\Vote;
use App\Traits\ApiResponse;
use App\Traits\VoteTrait;
use Illuminate\Http\Request;

class VotingController extends Controller
{
    use ApiResponse, VoteTrait;

    // guest voting
    public function guestVote(Request $request, $id)
    {
        $request->validate([
            'guest_token' => 'required|string', // A unique identifier for the guest
        ]);
        // get participant
        $participant = Submission::whereType('entry')->find($id);

        if (! $participant) {
            return $this->notFoundResponse('Participant not found.');
        }
        $contest = $participant->contest;

        if ($contest->type !== 'free') {
            return $this->errorResponse('Guests can only vote in free contests.', 403);
        }

        // Check if voting is allowed
        if (! $this->isVotingAllowed($contest)) {
            return $this->errorResponse('Voting is not allowed for this contest at the moment.', 403);
        }

        return $this->processGuestVote($request->guest_token, $participant, $contest, 1, $request->ip());
    }

    public function vote(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:1|max:100',
        ]);
        // get participant
        $participant = Submission::whereType('entry')->find($id);

        if (! $participant) {
            return $this->notFoundResponse('Participant not found.');
        }

        if ($participant->vote_status != 'enabled') {
            return $this->errorResponse('This participant is no longer active.', 403);
        }
        // Fetch the contest
        $contest = $participant->contest;
        $user = \Auth::user();

        // Check if voting is allowed
        if (! $this->isVotingAllowed($contest)) {
            return $this->errorResponse('Voting is not allowed for this contest at the moment.', 403);
        }

        if ($contest->type === 'free') {
            return $this->processFreeVote($user, $participant, $contest, 1, $request->ip());
        }

        return $this->processPaidVote($user, $participant, $contest, $request->quantity, $request->ip());

    }

    // free vote;
    protected function processFreeVote($user, $participant, $contest, $quantity, $ipAddress)
    {
        // check if user has voted already.
        $existingVote = Vote::where([
            ['submission_id', '=', $participant->id],
            ['voter_type', '=', 'user'],
            ['voter_id', '=', $user->id],
        ])->first();

        if ($existingVote) {
            return $this->errorResponse('You have already voted for this participant.', 403);
        }
        // Create vote history
        Vote::create([
            'submission_id' => $participant->id,
            'contest_id'    => $contest->id,
            'voter_type'    => 'user',
            'voter_id'      => $user->id,
            'type'          => 'free',
            'quantity'      => $quantity,
            'amount'        => 0, // Free vote, no cost
            'ip_address'    => $ipAddress,
        ]);

        // Increase participant votes
        $participant->increment('vote_count', $quantity);

        // Send notifications
        $this->sendVoteNotifications($user, $participant, $contest, $quantity);
        // clear chache
        \Cache::forget("contest_{$contest->id}_submissions");

        return $this->successResponse('Voting successful.');
    }

    // paid vote
    protected function processPaidVote($user, $participant, $contest, $quantity, $ipAddress)
    {
        // Check if the user has sufficient coins for voting
        $userCoin = CoinBalance::firstOrCreate(['user_id' => $user->id]);
        $price = $contest->amount ?? 10; // Default to 10 coins per vote
        $totalPrice = $price * $quantity;

        if ($userCoin->balance < $totalPrice) {
            return $this->errorResponse('Insufficient coins to vote.', 403);
        }

        $oldBal = $userCoin->balance;
        // Debit user
        debitUser($userCoin, $totalPrice);
        $userCoin->refresh();

        $ref = getTrx(13);

        // Create coin transaction
        CoinTransaction::create([
            'user_id'     => $user->id,
            'coins'       => $totalPrice,
            'amount'      => convertCoinsToFiat($totalPrice),
            'type'        => 'debit',
            'service'     => 'voting',
            'gateway'     => 'system',
            'code'        => $ref,
            'response'    => null,
            'description' => "Vote for {$participant->title} Entry in {$contest->title} contest",
            'newbal'      => $userCoin->balance,
            'oldbal'      => $oldBal,
        ]);

        // Create vote history
        Vote::create([
            'submission_id' => $participant->id,
            'contest_id'    => $contest->id,
            'voter_type'    => 'user',
            'type'          => 'paid',
            'voter_id'      => $user->id,
            'quantity'      => $quantity,
            'amount'        => $totalPrice,
            'ip_address'    => $ipAddress,
        ]);

        // Increase participant votes and contest coins
        $participant->increment('vote_count', $quantity);
        $contest->increment('voting_coins', $totalPrice);

        // clear chache
        \Cache::forget("contest_{$contest->id}_submissions");
        // Send notifications
        $this->sendVoteNotifications($user, $participant, $contest, $quantity);

        return $this->successResponse('Voting successful.');
    }

    // guest vote
    protected function processGuestVote($token, $participant, $contest, $quantity, $ipAddress)
    {
        $existingVote = Vote::where([
            ['submission_id', '=', $participant->id],
            ['voter_type', '=', 'guest'],
            ['guest_token', '=', $token],
        ])->first();

        if ($existingVote) {
            return $this->errorResponse('You have already voted for this participant.', 403);
        }

        // Create vote history for guest
        Vote::create([
            'submission_id' => $participant->id,
            'contest_id'    => $contest->id,
            'voter_type'    => 'guest', // Indicate that this is a guest vote
            'voter_id'      => null, // No user ID since it's a guest
            'quantity'      => $quantity,
            'amount'        => 0, // Free vote, no cost
            'ip_address'    => $ipAddress,
            'type'          => 'free',
            'guest_token'   => $token,
        ]);

        // Increase participant votes
        $participant->increment('vote_count', $quantity);

        // clear chache
        \Cache::forget("contest_{$contest->id}_submissions");

        // Send notifications (optional)
        $this->sendGuestVoteNotifications($participant, $contest, $quantity);

        return $this->successResponse('Voting successful.');
    }
}
