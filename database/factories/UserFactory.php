<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = fake()->firstName();
        $lastName = fake()->lastName();
        $username = strtolower($firstName . fake()->numberBetween(1, 999));
        
        return [
            'id'                => (string) Str::ulid(), // char(26)
            'first_name'        => $firstName, // varchar(255), NOT NULL
            'last_name'         => fake()->optional()->lastName(), // varchar(255), NULL
            'username'          => $username, // varchar(255), NOT NULL, UNIQUE
            'email'             => fake()->unique()->safeEmail(), // varchar(255), NOT NULL, UNIQUE
            'role'              => fake()->randomElement(['organizer', 'contestant', 'voter', 'guest']), // enum, NOT NULL, default 'guest'
            'phone'             => fake()->optional()->phoneNumber(), // varchar(255), NULL
            'gender'            => fake()->optional()->randomElement(['male', 'female', 'other']), // varchar(255), NULL
            'image'             => fake()->optional()->imageUrl(), // varchar(255), NULL
            'bio'               => fake()->optional()->paragraph(), // text, NULL
            'organization_name' => fake()->optional()->company(), // varchar(255), NULL
            'social_links'      => fake()->optional()->url(), // varchar(255), NULL
            'phone_number'      => fake()->optional()->phoneNumber(), // varchar(255), NULL
            'code_sent'         => fake()->optional()->dateTime(), // datetime, NULL
            'verify_code'       => fake()->optional()->numerify('######'), // varchar(20), NULL
            'email_verify'      => fake()->boolean(80), // tinyint(1), NOT NULL, default 0
            'status'            => fake()->randomElement(['active', 'inactive', 'banned']), // enum, NOT NULL, default 'active'
            'email_verified_at' => now(), // timestamp, NULL
            'password'          => static::$password ??= Hash::make('password'), // varchar(255), NOT NULL
            'remember_token'    => Str::random(10), // varchar(100), NULL
            'push_token'        => fake()->optional()->uuid(), // varchar(255), NULL
            'created_at'        => now(), // timestamp, NULL
            'updated_at'        => now(), // timestamp, NULL
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
            'email_verify'      => false,
        ]);
    }
}
