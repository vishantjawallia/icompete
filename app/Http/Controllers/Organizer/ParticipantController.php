<?php

namespace App\Http\Controllers\Organizer;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use App\Traits\ApiResponse;
use App\Traits\ParticipantTrait;
use Illuminate\Http\Request;

class ParticipantController extends Controller
{
    use ApiResponse, ParticipantTrait;

    public function index(Request $request)
    {
        $user = \Auth::user();
        $pp = $request->count ?? 30;
        $page = $request->input('page', 1);
        $contestIds = $user->contests()->pluck('id');
        $query = Submission::whereIn('contest_id', $contestIds)->whereType('entry')->orderByDesc('id');

        if ($request->search) {
            $query->search($request->search);
        }

        [$result, $objectData] = $this->paginateAndTransform(
            $query,
            fn ($item) => $this->participantObject($item),
            $pp,
            true
        );

        return $this->paginatedResponse('Participants retrieved successfully.', $objectData, $result, 200);

    }

    public function show($id)
    {
        $submission = Submission::whereType('entry')->find($id);

        if (! $submission) {
            return $this->errorResponse('Participant not found', 404);
        }
        $contest = $submission->contest;

        if ($contest->organizer_id !== \Auth::id()) {
            return $this->errorResponse('You do not have permission to view this participant.', 403);
        }

        return $this->successResponse('Participant retrieved successfully.', $this->participantObject($submission));
    }

    public function updateStatus($id, Request $request)
    {
        $request->validate([
            'status' => 'required|in:enabled,disabled',
        ]);
        $submission = Submission::whereType('entry')->find($id);

        if (! $submission) {
            return $this->notFoundResponse('Participant not found');
        }
        $user = \Auth::user();
        $contest = $submission->contest;

        if ($contest->organizer_id !== $user->id) {
            return $this->errorResponse('You do not have permission to update this participant.', 403);
        }
        $status = $request->status;
        // make sure user have access to the submissions's contests
        $submission->update([
            'vote_status' => $status,
        ]);

        return $this->successResponse("Particpant {$status} successfully", $this->participantObject($submission));
    }

    // delete participants
    public function delete($id)
    {
        $submission = Submission::whereType('entry')->find($id);

        if (! $submission) {
            return $this->errorResponse('Participant not found', 404);
        }
        $contest = $submission->contest;

        if ($contest->organizer_id !== \Auth::id()) {
            return $this->errorResponse('You do not have permission to delete this participant.', 403);
        }
        // subtract total point from contest??
        $contest->update([
            'total_points' => $contest->total_points - $submission->vote_count,
        ]);
        $submission->delete();

        return $this->successResponse('Participant deleted successfully.');
    }

    // submissions
    public function showSubmission($id)
    {
        $submission = Submission::whereType('submission')->find($id);

        if (! $submission) {
            return $this->errorResponse('Submission not found', 404);
        }
        $contest = $submission->contest;

        if ($contest->organizer_id !== \Auth::id()) {
            return $this->errorResponse('You do not have permission to view this entry.', 403);
        }

        return $this->successResponse('Submission retrieved successfully.', $this->submissionObject($submission));
    }

    // approve submission
    public function approveSubmission(Request $request, $id)
    {
        $submission = Submission::whereType('submission')->find($id);

        if (! $submission) {
            return $this->errorResponse('Submission not found', 404);
        }
        $contest = $submission->contest;

        if ($contest->organizer_id !== \Auth::id()) {
            return $this->errorResponse('You do not have permission to update this entry.', 403);
        }

        // approve
        if ($submission->status == 'pending') {
            $submission->update(['status' => 'approved', 'vote_status' => 'enabled', 'type' => 'entry']);
        } else {
            return $this->errorResponse('Submission already approved.', 400);
        }
        // send notification to contestant
        sendNotification('ENTRY_STATUS', $submission->user, [
            'username'        => $submission->user->username,
            'contest_name'    => $contest->title,
            'status'          => $submission->status,
            'submission_date' => show_datetime($submission->created_at),
            'entry_title'     => $submission->title,
        ], [
            'user_id'    => $submission->user_id,
            'contest_id' => $contest->id,
            'entry_id'   => $submission->id,
            'type'       => 'ENTRY_APPROVED',
        ]);

        return $this->successResponse('Submission approved successfully.', $this->submissionObject($submission));
    }

    // reject submission
    public function rejectSubmission(Request $request, $id)
    {
        $submission = Submission::whereType('submission')->find($id);

        if (! $submission) {
            return $this->errorResponse('Submission not found', 404);
        }
        $contest = $submission->contest;

        if ($contest->organizer_id !== \Auth::id()) {
            return $this->errorResponse('You do not have permission to update this entry.', 403);
        }

        // approve
        if ($submission->status == 'pending') {
            $submission->update(['status' => 'rejected']);
        }
        // send notification to contestant
        sendNotification('ENTRY_STATUS', $submission->user, [
            'username'        => $submission->user->username,
            'contest_name'    => $contest->title,
            'status'          => $submission->status,
            'submission_date' => show_datetime($submission->created_at),
            'entry_title'     => $submission->title,
        ], [
            'user_id'    => $submission->user_id,
            'contest_id' => $contest->id,
            'entry_id'   => $submission->id,
            'type'       => 'ENTRY_REJECTED',
        ]);

        return $this->successResponse('Submission rejected successfully.', $this->submissionObject($submission));
    }

    // delete submission
    public function deleteSubmission($id)
    {
        $submission = Submission::whereType('submission')->find($id);

        if (! $submission) {
            return $this->errorResponse('Entry not found', 404);
        }
        $contest = $submission->contest;

        if ($contest->organizer_id !== \Auth::id()) {
            return $this->errorResponse('You do not have permission to delete this entry.', 403);
        }
        $submission->delete();

        return $this->successResponse('Submission deleted successfully.');
    }
}
