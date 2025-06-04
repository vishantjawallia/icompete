<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CoinTransaction>
 */
class CoinTransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'        => User::inRandomOrder()->first()->id,
            'amount'         => $this->faker->numberBetween(10, 100),
            'coins'          => $this->faker->numberBetween(90, 1000),
            'description'    => $this->faker->sentence(),
            'code'           => getTrx(),
            'type'           => $this->faker->randomElement(['credit', 'debit']),
            'service'        => $this->faker->randomElement(['earn', 'reward', 'referral', 'spend', 'reward']),
            'oldbal'         => $this->faker->numberBetween(10, 100),
            'newbal'         => $this->faker->numberBetween(10, 100),
        ];
    }
}
