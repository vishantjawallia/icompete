<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CoinSetting extends Model
{
    protected $fillable = ['key', 'value'];

    protected $casts = [
        'value' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            \Cache::forget('coin_settings');
        });
    }
}
