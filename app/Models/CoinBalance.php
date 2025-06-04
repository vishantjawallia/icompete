<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class CoinBalance extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id', 'balance',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
