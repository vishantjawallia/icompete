<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Withdrawal>
 */
class WithdrawalFactory extends Factory
{
    public function definition(): array
    {
        $amount = fake()->numberBetween(15, 100);
        $coins = (int) $this->calculateCoins($amount); // cast to integer
        $method = fake()->randomElement(['paypal', 'bank_transfer']);
        $paymentDetails = $this->getPaymentDetails($method);
        $balance = fake()->numberBetween(100, 1000);

        return [
            'user_id'         => User::inRandomOrder()->first()?->id,
            'amount'          => $amount,
            'coins'           => $coins,
            'method'          => $method,
            'payment_details' => $paymentDetails,
            'status'          => fake()->randomElement(['pending', 'completed', 'canceled', 'processing', 'approved']),
            'code'            => getTrx(6),
            'fee'             => fake()->numberBetween(1, 10),
            'oldbal'          => $balance,
            'newbal'          => $balance - $coins,
        ];
    }

    private function calculateCoins($amount)
    {
        // 10 dollars = 1000 coins
        return ($amount / 10) * 1000;
    }

    private function getPaymentDetails($method)
    {
        if ($method === 'paypal') {
            return ['email' => fake()->safeEmail()];
        }

        if ($method === 'bank_transfer') {
            return [
                'bank_code'     => fake()->randomNumber(4),
                'bank_name'     => fake()->company(),
                'account_name'  => fake()->name(),
                'account_number'=> fake()->bankAccountNumber(),
            ];
        }

    }
}
