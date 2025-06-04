<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Submission extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'contest_id',
        'title',
        'description',
        'type',
        'meta',
        'votes',
        'status',
        'response',
        'vote_status',
        'rejection_reason',
        'is_winner',
    ];

    protected $casts = [
        'media'    => 'array', // Automatically casts JSON to array
        'response' => 'object',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function contest()
    {
        return $this->belongsTo(Contest::class);
    }

    public function votes()
    {
        return $this->hasMany(Vote::class);
    }

    // Search
    public function scopeSearch($query, $search)
    {
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%")
                    ->orWhere('contest_id', 'like', "%{$search}%")
                    ->orWhere('user_id', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    // get rank
    public function ranking()
    {
        if (! $this->contest) {
            return 'N/A'; // Return 'N/A' to ensure rank displays properly in template
        }

        // If item has 0 votes, return N/A
        if ($this->vote_count <= 0) {
            return 'N/A';
        }

        $cacheKey = "contest_{$this->contest_id}_submissions";

        // Cache for 5 minutes since ranks can change frequently with votes
        $submissions = \Cache::remember($cacheKey, 1 * 60, function () {
            return Submission::where('contest_id', $this->contest_id)
                ->where('type', 'entry')
                ->where('vote_status', 'enabled')
                ->where('vote_count', '>', 0) // Only include submissions with votes
                ->orderByDesc('vote_count')
                ->select(['id', 'vote_count'])
                ->get();
        });

        // Use collection methods for better performance
        $rank = $submissions->pluck('id')->search($this->id);

        // Add 1 to convert from 0-based index to 1-based rank
        return $rank !== false ? $rank + 1 : 'N/A'; // Return 'N/A' if rank not found
    }
}
