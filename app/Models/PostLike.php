<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PostLike extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = ['user_id', 'post_id'];

    /**
     * Get the post that this like belongs to.
     */
    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Get the user who liked this post.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
