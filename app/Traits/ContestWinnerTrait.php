<?php

namespace App\Traits;

use App\Models\CoinTransaction;

trait ContestWinnerTrait
{
    public function processWinner()
    {
        // get price
        $total = $this->voting_coins + $this->entry_coins;
        $adminRate = 10; // percentage
        $adminAmount = $total * $adminRate / 100;
        $totalCoinsEarned = $total - $adminAmount;
        // deduct admin rate from total coins
        $contestantRate = $this->prize;
        $organizerRate = 100 - $contestantRate;
        // calculate amount
        $winnerAmount = $totalCoinsEarned * $contestantRate / 100;
        $organizerAmount = $totalCoinsEarned * $organizerRate / 100;

        // update the contest
        $this->update([
            'winner_amount'    => $winnerAmount,
            'organizer_amount' => $organizerAmount,
            'admin_amount'     => $adminAmount,
        ]);

        // get and update winner
        $winner = $this->participants()->where('vote_status', 'enabled')->where('vote_count', '>', 0)->orderBy('vote_count', 'desc')->first();

        if ($winner) {
            $winner->update(['is_winner' => 1]);
            // Process winner payment
            $this->creditWinner($winner, $winnerAmount);

            // Process organizer payment if applicable
            if ($organizerAmount > 0) {
                $this->creditOrganizer($organizerAmount);
            }
        }

        return $winner;
    }

    protected function creditWinner($winner, $amount)
    {
        $participant = $winner->user;
        creditUser($participant->coins, $amount);
        $participant->coins->increment('total_earned', $amount);

        $transaction = $this->createTransaction($participant, $amount,
            'Reward for being the winner of ' . $this->title);

        // Send transaction notification
        sendNotification('COIN_TRX', $participant,
            $this->getTransactionNotificationData($transaction),
            [
                'user_id' => $participant->id,
                'coins'   => $amount,
                'amount'  => convertCoinsToFiat($amount),
                'type'    => 'COIN_TRANSACTION',
            ]
        );

        // Send winner notification
        sendNotification('CONTEST_WINNER', $winner->user, [
            'username'     => $winner->user->username,
            'contest_name' => $this->title,
            'prize_amount' => $amount,
            'vote_count'   => $winner->vote_count,
            'final_score'  => $winner->ranking(),
        ], [
            'user_id'    => $winner->user_id,
            'contest_id' => $this->id,
            'entry_id'   => $winner->id,
            'type'       => 'CONTEST_WINNER',
        ]);
    }

    protected function creditOrganizer($amount)
    {
        creditUser($this->organizer->coins, $amount);
        $this->organizer->coins->increment('total_earned', $amount);

        $transaction = $this->createTransaction($this->organizer, $amount,
            'Reward for being the organizer of ' . $this->title);

        sendNotification('COIN_TRX', $this->organizer,
            $this->getTransactionNotificationData($transaction),
            [
                'user_id' => $this->organizer_id,
                'coins'   => $amount,
                'amount'  => convertCoinsToFiat($amount),
                'type'    => 'COIN_TRANSACTION',
            ]
        );
    }

    private function createTransaction($user, $amount, $description)
    {
        return CoinTransaction::create([
            'user_id'     => $user->id,
            'coins'       => $amount,
            'amount'      => convertCoinsToFiat($amount),
            'type'        => 'credit',
            'service'     => 'contest',
            'gateway'     => 'contest',
            'code'        => getTrx(13),
            'description' => $description,
            'oldbal'      => $user->coins->balance,
            'newbal'      => $user->coins->balance + $amount,
        ]);
    }

    private function getTransactionNotificationData($transaction)
    {
        return [
            'username'    => $transaction->user->username,
            'name'        => $transaction->user->name,
            'coins'       => $transaction->coins,
            'trx_type'    => $transaction->type,
            'amount'      => $transaction->amount,
            'trx_code'    => $transaction->code,
            'new_balance' => $transaction->newbal,
            'trx_details' => $transaction->description,
            'service'     => $transaction->service,
        ];
    }
}
