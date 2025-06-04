<?php

namespace App\Traits;

use App\Models\Submission;

trait RankTrait
{
    /**
     * Creates a standardized JSON object for a submission item
     *
     * @param  Submission  $item
     * @return array
     */
    protected function rankObject($item)
    {
        $data = [
            'id'    => $item->id,
            'title' => $item->title,
            'media' => $this->getMedia($item),
        ];

        if ($item->contest) {
            $data['contest'] = [
                'id'       => $item->contest_id,
                'category' => $item->contest->category->name ?? '',
                'name'     => $item->contest->title,
                'image'    => ($item->contest->image) ? my_asset($item->contest->image) : my_asset('contests/default.jpg'),
            ];
        }
        $rank = $this->getRank($item);
        $data['results'] = [
            'votes' => $item->vote_count,
        ];
        $data['rank'] = $rank;

        // add votes object
        return $data;
    }

    /**
     * Calculates and returns the rank of a submission item within its contest.
     * If the item is not associated with a contest, returns null.
     * Caches the contest submissions for 30 minutes to optimize performance.
     *
     * @param  Submission  $item  The submission item for which to calculate the rank.
     * @return int|null The rank of the submission in the contest, or null if not applicable.
     */
    private function getRank($item)
    {
        if (! $item->contest) {
            return 'N/A'; // Return 'N/A' to ensure rank displays properly in template
        }

        // If item has 0 votes, return N/A
        if ($item->vote_count <= 0) {
            return 'N/A';
        }

        $cacheKey = "contest_{$item->contest_id}_submissions";

        // Cache for 5 minutes since ranks can change frequently with votes
        $submissions = \Cache::remember($cacheKey, 10 * 60, function () use ($item) {
            return Submission::where('contest_id', $item->contest_id)
                ->where('type', 'entry')
                ->where('vote_status', 'enabled')
                ->where('vote_count', '>', 0) // Only include submissions with votes
                ->orderByDesc('vote_count')
                ->select(['id', 'vote_count'])
                ->get();
        });

        // Use collection methods for better performance
        $rank = $submissions->pluck('id')->search($item->id);

        // Add 1 to convert from 0-based index to 1-based rank
        return $rank !== false ? $rank + 1 : 'N/A'; // Return 'N/A' if rank not found
    }
}
