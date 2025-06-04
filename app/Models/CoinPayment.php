<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class CoinPayment extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'gateway',
        'coins',
        'amount',
        'reference',
        'status',
        'response',
        'data',
        'expires_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    protected $hidden = ['user_id'];

    protected $casts = [
        'expires_at' => 'datetime',
        'data'       => 'array',
        'response'   => 'array',
    ];
}
