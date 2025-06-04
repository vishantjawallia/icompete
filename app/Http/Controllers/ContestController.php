<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Contest;
use App\Models\Submission;
use App\Traits\ApiResponse;
use App\Traits\ContestTrait;
use App\Traits\ParticipantTrait;
use Illuminate\Http\Request;

class ContestController extends Controller
{
    use ApiResponse, ContestTrait, ParticipantTrait;

    public function categories()
    {
        $categories = cache()->remember('contest_categories', 60 * 60, function () {
            return Category::all();
        });
        $activeCategories = $categories->filter(function ($category) {
            return $category->status === 'enabled';
        });

        return $this->successResponse('Categories retrieved successfully', $activeCategories, 200);
    }

    // Fetch all contests
    public function index(Request $request)
    {
        $pp = $request->count ?? 20;
        $page = $request->input('page', 1);
        $query = Contest::whereIn('status', ['active', 'completed', 'ongoing'])->orderByDesc('id');

        if ($request->status) {
            $validStatuses = ['active', 'ongoing'];

            if (in_array($request->status, $validStatuses)) {
                $query->whereStatus($request->status);
            }
        } else {
            $query->whereStatus('active');
        }

        if ($request->search) {
            $query->searchContest($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($contest) => $this->contestObject($contest),
            $pp,
            true
        );

        return $this->paginatedResponse('Contests retrieved successfully.', $objectData, $result);
    }

    // filter contests
    public function filterContest(Request $request)
    {
        $pp = $request->count ?? 30;
        $page = $request->input('page', 1);
        $query = Contest::whereIn('status', ['active', 'completed', 'ongoing'])->orderByDesc('id');

        if ($request->status) {
            $validStatuses = ['active', 'ongoing', 'completed'];

            if (in_array($request->status, $validStatuses)) {
                $query->whereStatus($request->status);
            }
        } else {
            $query->whereStatus('active');
        }

        if ($request->category_id) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->type) {
            $query->where('type', $request->type);
        }

        if ($request->entry_type) {
            $query->where('entry_type', $request->entry_type);
        }

        if ($request->search) {
            $query->searchContest($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($contest) => $this->contestObject($contest),
            $pp,
            true
        );

        return $this->paginatedResponse(
            'Contests retrieved successfully.',
            $objectData,
            $result
        );
    }

    public function featuredContests(Request $request)
    {
        $pp = $request->count ?? 30;
        $query = Contest::whereIn('status', ['active', 'ongoing'])->orderByDesc('id')->orderBy('featured', 'desc');

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($contest) => $this->contestObject($contest),
            $pp,
            true
        );

        return $this->paginatedResponse(
            'Featured Contests retrieved successfully.',
            $objectData,
            $result
        );
    }

    // show contest
    public function show($id)
    {
        $contest = Contest::whereId($id)->whereIn('status', ['active', 'completed', 'ongoing'])->first();

        if (! $contest) {
            return $this->notFoundResponse('Contest not found.');
        }

        return $this->successResponse('Contest retrieved successfully.', $this->contestObjectFull($contest));
    }

    public function participants(Request $request, $id)
    {
        $pp = $request->count ?? 50;
        $page = $request->input('page', 1);

        $contest = Contest::whereId($id)->first();

        if (! $contest) {
            return $this->notFoundResponse('Contest not found.');
        }
        $query = Submission::whereType('entry')->whereContestId($id)->orderByDesc('vote_count')->orderBy('vote_status');

        if ($request->search) {
            $query->search($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($item) => $this->participantObjectShort($item),
            $pp,
            true
        );

        return $this->paginatedResponse('Participants Fetched successfully.', $objectData, $result);
    }

    // participant details
    public function showParticipant($id)
    {
        $submission = Submission::whereType('entry')->find($id);

        if (! $submission) {
            return $this->notFoundResponse('Participant not found');
        }

        return $this->successResponse(
            'Participant retrieved successfully.',
            $this->participantObject($submission)
        );
    }

    public function contestLeaderboard(Request $request, $id)
    {
        $contest = Contest::whereId($id)->whereIn('status', ['active', 'completed', 'ongoing'])->first();

        if (! $contest) {
            return $this->notFoundResponse('Contest not found.');
        }
        $rankedParticipants = $contest->participants()->where('vote_status', 'enabled')->orderByDesc('vote_count')->get();
        $objectData = $rankedParticipants->transform(function ($item) {
            return $this->rankObject($item);
        });

        $data = [
            'contest' => $this->contestStats($contest),
            'results' => $objectData,
        ];

        return $this->successResponse('Contest Leaderboards', $data);
    }
}
