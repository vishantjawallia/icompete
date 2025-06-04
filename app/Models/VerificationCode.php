<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class VerificationCode extends Model
{
    use HasUlids;

    protected $fillable = [
        'user_id',
        'type',
        'code',
        'expires_at',
        'verified_at',
    ];

    protected $dates = [
        'expires_at',
        'verified_at',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
