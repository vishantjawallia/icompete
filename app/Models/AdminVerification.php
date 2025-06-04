<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class AdminVerification extends Model
{
    use HasUlids;

    protected $fillable = [
        'admin_id',
        'type',
        'code',
        'expires_at',
        'verified_at',
    ];

    protected $dates = [
        'expires_at',
        'verified_at',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
