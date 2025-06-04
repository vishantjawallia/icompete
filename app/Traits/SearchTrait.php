<?php

namespace App\Traits;

trait SearchTrait
{
    public function scopeSearchPost($query, $search)
    {
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('type', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function scopeSearchComment($query, $search)
    {
        if ($search) {
            $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%")
                    ->orWhere('id', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function scopeSearchContest($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('title', 'like', "%$search%")
                ->orWhere('category_id', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%")
                ->orWhere('slug', 'like', "%$search%")
                ->orWhere('rules', 'like', "%$search%")
                ->orWhere('organizer_id', 'like', "%$search%")
                ->orWhere('type', 'like', "%$search%")
                ->orWhere('status', 'like', "%$search%")
                ->orWhere('requirements', 'like', "%$search%")
                ->orWhere('start_date', 'like', "%$search%")
                ->orWhere('end_date', 'like', "%$search%")
                ->orWhere('voting_start_date', 'like', "%$search%")
                ->orWhere('voting_end_date', 'like', "%$search%")
                ->orWhere('id', 'like', "%$search%");
        });
    }

    public function scopeSearchUser($query, $search)
    {
        return $query->where(function ($query) use ($search) {
            $query->where('firstname', 'like', "%$search%")
                ->orWhere('gender', 'like', "%$search%")
                ->orWhere('country', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhere('username', 'like', "%$search%")
                ->orWhere('phone', 'like', "%$search%")
                ->orWhere('lastname', 'like', "%$search%")
                // ->orWhere('bonus', 'like', "%$search%")
                ->orWhere('address', 'like', "%$search%");
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
