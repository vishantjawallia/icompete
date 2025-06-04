<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, HasUlids, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'username',
        'email',
        'password',
        'role',
        'email_verified_at',
        'email_verify',
        'bio',
        'gender',
        'phone',
        'image',
        'push_token', 'organization_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
            'bank_details'      => 'object',
        ];
    }

    public function verificationCodes()
    {
        return $this->hasMany(VerificationCode::class);
    }

    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    public function notifys()
    {
        return $this->hasMany(Notify::class);
    }

    public function coins()
    {
        $coins = $this->hasOne(CoinBalance::class)->first();

        if (! $coins) {
            // Create new coin balance if doesn't exist
            $coins = CoinBalance::create([
                'user_id' => $this->id,
            ]);
        }

        return $this->hasOne(CoinBalance::class);
    }

    public function coinTransaction()
    {
        return $this->hasMany(CoinTransaction::class);
    }

    public function referBy()
    {
        return $this->belongsTo(User::class, 'ref_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'ref_id')->where('ref_id', $this->id);
    }

    // withdrawals
    public function withdrawals()
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function pendingWithdrawals()
    {
        return $this->hasMany(Withdrawal::class)->wherestatus('pending');
    }

    // contests
    public function contests()
    {
        return $this->hasMany(Contest::class, 'organizer_id');
    }

    public function loginHistory()
    {
        return $this->hasMany(LoginHistory::class, 'user_id');
    }

    // Entries
    public function entry()
    {
        return $this->hasMany(Submission::class);
    }

    // Posts
    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // search scope
    public function scopeSearchUser($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('first_name', 'like', "%$search%")
                ->orWhere('last_name', 'like', "%$search%")
                ->orWhere('gender', 'like', "%$search%")
                ->orWhere('id', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('username', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhere('bio', 'like', "%$search%");
            // ->orWhere('bonus', 'like', "%$search%");
        });
    }

    public function isVerified()
    {
        return $this->email_verify || $this->phone_verified || $this->id_verified;
    }
}
