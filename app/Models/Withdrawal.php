<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Withdrawal extends Model
{
    use HasFactory, HasUlids;

    protected $fillable = [
        'user_id',
        'amount',
        'coins',
        'method',
        'fee',
        'payment_details',
        'status',
        'code',
        'new_balance',
        'old_balance',
        'response', 'reference', 'admin_notes',
    ];

    protected $casts = [
        'payment_details' => 'object',
        'response'        => 'object',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $params = ['user:username', 'user:name', 'user:id', 'user:email'];
            $query->where('code', 'like', "%$search%")
                ->orWhere('method', 'like', "%$search%")
                ->orWhere('amount', 'like', "%$search%")
                ->orWhere('payment_details', 'like', "%$search%")
                ->orWhere('coins', 'like', "%$search%")
                ->orWhere('id', 'like', "%$search%")
                ->orWhere('user_id', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%");
        });
    }

    private function relationSearch($query, $relation, $columns, $search)
    {
        foreach (explode(',', $columns) as $column) {
            $query->orWhereHas($relation, function ($q) use ($column, $search) {
                $q->where($column, 'like', "%$search%");
            });
        }

        return $query;
    }
}
