<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function index(Request $request)
    {
        $title = 'All Submissions';
        $query = Submission::whereType('submission')->orderByDesc('created_at');

        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }
        $submissions = $query->paginate(30);

        return view('admin.submissions.index', compact('submissions', 'title'));
    }

    public function entry(Request $request)
    {
        $title = 'All Entries';
        $query = Submission::whereType('entry')->orderByDesc('created_at');

        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }
        $submissions = $query->paginate(30);

        return view('admin.submissions.index', compact('submissions', 'title'));
    }

    // View a specific submission
    public function show($id)
    {
        $submission = Submission::findOrFail($id);

        return view('admin.submissions.view', compact('submission'));
    }

    // Update submission details
    public function update(Request $request, $id)
    {
        $submission = Submission::findOrFail($id);
        $validated = $request->validate([
            'title'       => 'required|string|max:255',
            'vote_status' => 'sometimes|string|in:enabled,disabled',
            'status'      => 'sometimes|string|in:approved,rejected,pending',
            'description' => 'nullable|string',
        ]);
        $submission->update($validated);

        if ($request->status == 'approved') {
            // change type to entry
            $submission->update([
                'type' => 'entry',
            ]);
            $contest = $submission->contest;
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
        }

        return back()->with('success', 'Submission updated successfully.');
    }

    // Approve a submission
    public function approve($id)
    {
        $submission = Submission::findOrFail($id);
        $submission->update(['status' => 'approved', 'vote_status' => 'enabled', 'type' => 'entry']);
        $contest = $submission->contest;
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

        return redirect()->back()->with('success', 'Submission approved.');
    }

    // Reject a submission
    public function reject($id)
    {
        $submission = Submission::findOrFail($id);
        $submission->update(['status' => 'rejected']);
        $contest = $submission->contest;
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

        return back()->with('success', 'Submission rejected.');
    }

    // Delete a submission
    public function delete($id)
    {
        $submission = Submission::findOrFail($id);
        $submission->delete();

        return back()->with('success', 'Participants deleted successfully.');
    }

    // Contestant Votes
    public function votes($id)
    {
        $submission = Submission::findOrFail($id);
        $votes = $submission->votes()->orderByDesc('id')->paginate(50);

        return view('admin.submissions.votes', compact('submission', 'votes'));
    }
}
