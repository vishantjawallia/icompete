<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;

class Vote extends Model
{
    use HasUlids;

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'submission_id',
        'contest_id',
        'voter_type',
        'voter_id',
        'guest_token',
        'quantity',
        'type',
        'amount',
        'ip_address',
        'meta',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'meta' => 'array',
    ];

    /**
     * Get the submission associated with the vote.
     */
    public function submission()
    {
        return $this->belongsTo(Submission::class);
    }

    public function participant()
    {
        return $this->belongsTo(Submission::class, 'submission_id');
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    /**
     * Get the user who cast the vote (if voter is a registered user).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    // search
    public function scopeSearch($query, $search)
    {
        return $query->whereHas('participant', function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%");
            $query->orWhere('id', 'like', "%{$search}%");
        })->orWhereHas('contest', function ($query) use ($search) {
            $query->where('title', 'like', "%{$search}%");
            $query->orWhere('id', 'like', "%{$search}%");
        })->orWhereHas('user', function ($query) use ($search) {
            $query->where('username', 'like', "%{$search}%");
            $query->orWhere('first_name', 'like', "%{$search}%");
            $query->orWhere('last_name', 'like', "%{$search}%");
            $query->orWhere('id', 'like', "%{$search}%");
        })->orWhere('quantity', 'like', "%{$search}%")
            ->orWhere('amount', 'like', "%{$search}%");
    }
}
