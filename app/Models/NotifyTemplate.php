<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NotifyTemplate extends Model
{
    use HasUlids, SoftDeletes;

    protected $casts = [
        'shortcodes' => 'object',
        'channels'   => 'array',
    ];

    protected $fillable = [
        'name', 'channels', 'title', 'email_subject', 'message', 'email_content',
        'type',
        'content', 'status', 'subject', 'shortcodes',
    ];
}
