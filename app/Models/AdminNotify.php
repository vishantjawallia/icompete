<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class AdminNotify extends Model
{
    use HasUlids;

    protected $fillable = [
        'type',
        'title',
        'message',
        'url',
        'data',
        'read_at',
    ];

    protected $casts = [
        'data' => 'array',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            \Cache::forget('admin_notifications');
            \Cache::forget('admin_notifications_unread_count');
        });
        static::deleted(function () {
            \Cache::forget('admin_notifications');
            \Cache::forget('admin_notifications_unread_count');
        });
    }
}
