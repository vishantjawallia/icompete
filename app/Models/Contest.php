<?php

namespace App\Models;

use App\Traits\ContestWinnerTrait;
use App\Traits\SearchTrait;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Contest extends Model
{
    use ContestWinnerTrait, HasFactory, HasUlids, SearchTrait, SoftDeletes;

    protected $fillable = [
        'organizer_id',
        'category',
        'category_id',
        'title',
        'description',
        'slug',
        'image',
        'type',
        'amount',
        'entry_type',
        'entry_fee',
        'prize',
        'status',
        'start_date',
        'end_date',
        'voting_start_date',
        'voting_end_date',
        'max_entries',
        'featured',
        'rules',
        'requirements',
        'meta',
        'custom',
        'start_notify',
        'voting_ended',
        'winner_amount',
        'organizer_amount',
        'admin_amount',
    ];

    protected $casts = [
        'start_date'        => 'datetime',
        'end_date'          => 'datetime',
        'voting_start_date' => 'datetime',
        'voting_end_date'   => 'datetime',
        'amount'            => 'double',
        'entry_fee'         => 'double',
        'meta'              => 'array', // Automatically casts JSON to array
        'requirements'      => 'array',
        'custom'            => 'array',
    ];

    // Relationships
    public function organizer()
    {
        return $this->belongsTo(User::class, 'organizer_id');
    }

    // category
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // participants
    public function participants()
    {
        return $this->hasMany(Submission::class)->where('type', 'entry');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class)->whereType('submission');
    }

    public function entry()
    {
        return $this->hasMany(Submission::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    // search
}
