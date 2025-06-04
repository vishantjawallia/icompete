<?php

namespace Database\Seeders;

use App\Models\CoinSetting;
use Illuminate\Database\Seeder;

class CoinSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            ['key' => 'coin_rates', 'value' => ([
                ['coins' => 100, 'price' => 0.99],
                ['coins' => 500, 'price' => 4.49],
                ['coins' => 1000, 'price' => 8.99],
                ['coins' => 5000, 'price' => 39.99],
                ['coins' => 10000, 'price' => 74.99],
                ['coins' => 20000, 'price' => 139.99],
            ])],
            ['key' => 'coin_usage', 'value' => ([
                'basic_competition'   => 50,
                'premium_competition' => 200,
                'power_ups'           => 100,
                'exclusive_features'  => [500, 1000],
            ])],
            ['key' => 'coin_rewards', 'value' => ([
                'watch_ads'   => 5,
                'daily_login' => 10,
                'challenge'   => 20,
                'referral'    => 50,
            ])],
        ];

        foreach ($settings as $setting) {
            CoinSetting::create($setting);
        }
    }
}
