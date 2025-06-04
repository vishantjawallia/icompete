<?php

namespace App\Traits;

trait WithdrawalTrait
{
    /**
     * Trait containing withdrawal-related functionality
     *
     * @trait WithdrawalTrait
     */

    /**
     * Formats withdrawal data into a standardized object
     *
     * @param  object  $item  The withdrawal record to format
     * @return array Formatted withdrawal data including user details if available
     */
    private function withdrawObject($item)
    {
        $data = [
            'id'              => $item->id,
            'status'          => $item->status,
            'amount'          => $item->amount,
            'coins'           => $item->coins,
            'fee'             => $item->fee,
            'code'            => $item->code,
            'method'          => $item->method,
            'payment_details' => ($item->payment_details),
            'status'          => $item->status,
            'created_at'      => $item->created_at,
            'updated_at'      => $item->updated_at,
            'new_balance'     => $item->newbal,
        ];

        if ($item->user) {
            $data['user'] = [
                'id'       => $item->user_id,
                'username' => $item->user->username,
                'name'     => $item->user->fullname,
                'image'    => ($item->user->image) ? my_asset($item->user->image) : my_asset('users/default.jpg'),
            ];
        }

        return $data;
    }

    /**
     * Calculates coins based on withdrawal amount using 10:1000 ratio
     *
     * @param  float  $amount  The withdrawal amount in dollars
     * @return float The equivalent amount in coins
     */
    private function calculateCoins($amount)
    {
        // Calculate coins based on the rate: 10 dollars = 1000 coins
        return ($amount / 10) * 1000;
    }

    /**
     * Retrieves payment details based on withdrawal method and user
     *
     * @param  string  $method  The payment method (paypal, bank_transfer)
     * @param  object  $user  The user object containing payment details
     * @return array|null Payment details array or null if not available
     */
    private function getPaymentDetails($method, $user)
    {
        switch ($method) {
            case 'paypal':
                return $user->paypal_email ? ['email' => $user->paypal_email] : null;
            case 'bank_transfer':
                return $user->bank_details ? $user->bank_details : null;
            default:
                return;
        }
    }

    /**
     * Checks for potentially fraudulent withdrawal activity
     *
     * @param  object  $user  The user attempting withdrawal
     * @param  float  $amount  The withdrawal amount
     * @return bool True if suspicious activity detected, false otherwise
     */
    private function detectFraudulentActivity($user, $amount)
    {
        // Implement actual fraud detection logic
        $hourlyWithdrawals = $user->withdrawals()
            ->where('created_at', '>=', now()->subHour())
            ->sum('amount');

        return $hourlyWithdrawals + $amount > 500;
    }
}
