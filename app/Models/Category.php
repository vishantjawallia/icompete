<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasUlids;

    protected $fillable = [
        'name',
        'slug',
        'image',
        'description',
        'status',
    ];

    protected static function boot()
    {
        parent::boot();
        static::saved(function () {
            \Cache::forget('contest_categories');
        });
    }

    protected $hidden = [
        'created_at',
        'updated_at', 'image',
    ];

    // contests
    public function contests()
    {
        return $this->hasMany(Contest::class);
    }
}
