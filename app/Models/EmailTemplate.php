<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory, HasUlids;

    protected $casts = [
        'shortcodes' => 'object',
    ];

    protected $fillable = [
        'name',
        'type',
        'content', 'status', 'subject', 'shortcodes',
    ];
}
