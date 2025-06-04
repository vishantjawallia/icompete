<?php

namespace App\Http\Controllers\Contestant;

use App\Http\Controllers\Controller;
use App\Models\Contest;
use App\Models\Submission;
use App\Traits\ApiResponse;
use App\Traits\ContestTrait;
use App\Traits\ParticipantTrait;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ContestController extends Controller
{
    use ApiResponse, ContestTrait, ParticipantTrait;

    public function index(Request $request)
    {
        $pp = $request->count ?? 20;
        $query = Contest::whereIn('status', ['active', 'completed', 'ongoing'])->orderByDesc('id');

        // contest status if there is status, get the status else get only ac
        if ($request->status) {
            $validStatuses = ['active', 'ongoing'];

            if (in_array($request->status, $validStatuses)) {
                $query->whereStatus($request->status);
            }
        }

        if ($request->search) {
            $query->searchContest($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($item) => $this->contestObject($item),
            $pp,
            true
        );

        return $this->paginatedResponse('Contests retrieved successfully.', $objectData, $result);
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

    // enter contest
    public function enterContest($id, Request $request)
    {
        $contest = Contest::whereId($id)->whereIn('status', ['active', 'ongoing'])->first();

        if (! $contest) {
            return $this->notFoundResponse('Contest not found.');
        }
        // check if max entri has been reached.
        $entries = $contest->entry->count();

        if ($contest->max_entries <= $entries) {
            return $this->errorResponse('Maximum entries reached for this contests.', 403);
        }

        // Check if voting is allowed
        if (! now()->between($contest->start_date, $contest->end_date)) {
            return $this->errorResponse('Registration is not allowed for this contest at the moment.', 403);
        }

        try {
            $processedSubmission = $this->processSubmission($contest, $request);
            $organizer = $contest->organizer;
            $submission = Submission::create([
                'contest_id' => $contest->id,
                'user_id'    => \Auth::id(),
                'response'   => $processedSubmission,
                'status'     => 'pending',
                'title'      => $request->title,
                'type'       => 'submission',
            ]);
            // notifiy organizer
            sendNotification('ORG_NEW_SUBMISSION', $organizer, [
                'username'        => $organizer->username,
                'contest_name'    => $contest->title,
                'contestant_name' => $submission->user->username,
                'submission_time' => show_datetime($submission->created_at),
                'entry_title'     => $submission->title,
            ], [
                'user_id'    => $organizer->id,
                'contest_id' => $contest->id,
                'entry_id'   => $submission->id,
                'type'       => 'ORGANIZER_NEW_ENTRY',
            ]);

            return $this->successResponse('Contest submission successful', $this->submissionObject($submission));
        } catch (ValidationException $e) {
            return $this->errorResponse($e->getMessage(), 422, $e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Unable to process your submission', 500);
        }
    }

    // entered contests
    public function contestEntries(Request $request)
    {
        $user = $request->user();
        $pp = $request->count ?? 30;
        $query = Submission::whereUserId($user->id)->whereType('submission')->orderByDesc('id');

        if ($request->search) {
            $query->search($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($item) => $this->submissionObject($item),
            $pp
        );

        return $this->paginatedResponse('Submitted Entries.', $objectData, $result);
    }

    public function activeEntries(Request $request)
    {
        $user = $request->user();
        $pp = $request->count ?? 30;
        $query = Submission::whereUserId($user->id)->whereType('entry')->orderByDesc('id');

        if ($request->search) {
            $query->search($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($item) => $this->participantObject($item),
            $pp,
        );

        return $this->paginatedResponse('Approved Entries.', $objectData, $result);
    }

    // entry details
    public function showEntry($id)
    {
        $entry = Submission::whereUserId(\Auth::id())->whereId($id)->first();

        if (! $entry) {
            return $this->notFoundResponse('Entry not found.');
        }

        return $this->successResponse('Entry retrieved successfully.', $this->entryObject($entry));
    }

    public function updateEntry(Request $request, $id)
    {
        $entry = Submission::whereUserId(\Auth::id())->whereId($id)->first();

        if (! $entry) {
            return $this->notFoundResponse('Entry not found.');
        }

        if ($entry->type == 'entry') {
            return $this->errorResponse('You can only update pending entries.');
        }
        // validate request
        $request->validate([
            'title'    => 'sometimes|string|max:100',
            'response' => 'nullable|array',
        ]);
        $contest = $entry->contest;
        [
            'old' => $entry->response,
            'new' => $request->response,
        ];

        try {
            $updatedResponses = $this->processUpdates(
                $entry->response,
                $request->input('response', []),
                $contest->id
            );

            $entry->update([
                'title'    => $request->title ?? $entry->title,
                'response' => $updatedResponses,
                'status'   => 'pending',
            ]);

            return $this->successResponse('Entry updated successfully.', $this->entryObject($entry));

        } catch (ValidationException $e) {
            return response()->json([
                'status'  => 'error',
                'message' => $e->getMessage(),
                'errors'  => $e->errors(),
            ], 422);
        }
    }

    // delete entry
    public function deleteEntry($id)
    {
        $entry = Submission::whereUserId(\Auth::id())->whereId($id)->first();

        if (! $entry) {
            return $this->notFoundResponse('Entry not found.');
        }

        if ($entry->type == 'entry') {
            return $this->errorResponse('You can only update pending entries.');
        }
        $entry->delete();

        // do some more logic?
        return $this->successResponse('Entry was deleted.');
    }
}
