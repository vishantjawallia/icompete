<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Contest;
use App\Models\Vote;
use App\Traits\ContestTrait;
use Illuminate\Http\Request;

class ContestController extends Controller
{
    use ContestTrait;

    public function index(Request $request)
    {

        $statuses = Contest::select('status')->distinct()->orderBy('status')->get();

        $query = Contest::orderByDesc('created_at');

        if ($request->has('search') && $request->search) {
            $query->searchContest($request->search);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        $contests = $query->paginate(30);

        return view('admin.contests.index', compact('contests', 'statuses'));
    }

    public function show($id)
    {
        $categories = cache()->remember('contest_categories', 60 * 60, function () {
            return Category::all();
        });
        $contest = Contest::with(['organizer'])->findOrFail($id);

        return view('admin.contests.view', compact('contest', 'categories'));
    }

    // Approve a contest
    public function approve($id)
    {
        $contest = Contest::findOrFail($id);
        $contest->update(['status' => 'active']);
        // send Notification
        $organizer = $contest->organizer;
        sendNotification('ORG_CONTEST_APPROVED', $organizer, [
            'username'     => $organizer->username,
            'contest_name' => $contest->title,
        ], [
            'user_id'    => $organizer->id,
            'contest_id' => $contest->id,
            'type'       => 'CONTEST_APPROVED',
        ]);

        return back()->with('success', 'Contest approved successfully.');
    }

    // Reject a contest
    public function reject($id)
    {
        $contest = Contest::findOrFail($id);
        $contest->update(['status' => 'canceled']);
        // send Notification
        $organizer = $contest->organizer;
        sendNotification('ORG_CONTEST_STATUS', $organizer, [
            'username'       => $organizer->username,
            'contest_name'   => $contest->title,
            'contest_status' => $contest->status,
            'entry_count'    => $contest->entry->count(),
            'vote_count'     => $contest->votes->count(),
            'time_remaining' => $contest->voting_end_date->diffForHumans(),
        ], [
            'user_id'    => $organizer->id,
            'contest_id' => $contest->id,
            'type'       => 'CONTEST_REJECTED',
        ]);

        return back()->with('error', 'Contest rejected.');
    }

    // Update contest details
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'category_id'       => 'required',
            'title'             => 'required',
            'description'       => 'nullable',
            'type'              => 'required',
            'status'            => 'required|string',
            'amount'            => 'nullable|numeric',
            'entry_type'        => 'required',
            'entry_fee'         => 'nullable|numeric',
            'prize'             => 'required|numeric|min:1|max:100',
            'start_date'        => 'required|date',
            'end_date'          => 'required|date|after:start_date',
            'voting_start_date' => 'required|date',
            'voting_end_date'   => 'required|date|after:voting_start_date',
            'image'             => 'nullable|image',
            'rules'             => 'nullable|string',
            'requirements'      => 'nullable|array',
            'featured'          => 'sometimes|numeric',
        ]);
        $contest = Contest::findOrFail($id);
        $validated['image'] = $contest->image;
        $validated['featured'] = $request->featured ?? 0;

        // upload image
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $extension = $image->getClientOriginalExtension();
            $fileName = now()->timestamp . '-' . \Str::random(26) . '.' . $extension;
            $image->move(public_path('uploads/contests'), $fileName);

            // Delete old image if it exists
            if ($contest->image && file_exists(public_path('uploads/' . $contest->image))) {
                unlink(public_path('uploads/' . $contest->image));
            }
            $validated['image'] = 'contests/' . $fileName;
        }

        // send notify only if status changes
        if ($contest->status != $request->status) {
            $organizer = $contest->organizer;
            sendNotification('ORG_CONTEST_STATUS', $organizer, [
                'username'       => $organizer->username,
                'contest_name'   => $request->title,
                'contest_status' => $request->status,
                'entry_count'    => $contest->entry->count(),
                'vote_count'     => $contest->votes->count(),
                'time_remaining' => $contest->voting_end_date->diffForHumans(),
            ], [
                'user_id'    => $organizer->id,
                'contest_id' => $contest->id,
                'type'       => 'CONTEST_STATUS',
            ]);

            if ($request->status == 'completed') {
                $this->closeContest($contest); // close and give rewards
            }
        }
        $contest->update($validated);

        return back()->with('success', 'Contest updated successfully.');
    }

    public function delete($id)
    {
        $contest = Contest::findOrFail($id);
        $contest->delete();

        return redirect()->route('admin.contest.index')->with('success', 'Contest deleted successfully.');
    }

    // View all participants in a contest
    public function participants($id)
    {
        $contest = Contest::findOrFail($id);
        $submissions = $contest->participants()->paginate(30);

        return view('admin.contests.participants', compact('contest', 'submissions'));
    }

    public function submissions($id)
    {
        $contest = Contest::findOrFail($id);
        $submissions = $contest->submissions()->paginate(30);

        return view('admin.contests.participants', compact('contest', 'submissions'));
    }

    public function votes($id, Request $request)
    {
        $contest = Contest::findOrFail($id);
        $votes = $contest->votes()->orderByDesc('created_at')->paginate(50);

        return view('admin.contests.votes', compact('contest', 'votes'));
    }

    public function removeVote($id)
    {
        $vote = Vote::findOrFail($id);
        $contest = $vote->contest;
        $contestant = $vote->submission;

        if ($contest) {
            $contest->decrement('voting_coins', $vote->amount);
        }

        if ($contestant) {
            $contestant->decrement('vote_count', $vote->quantity);
        }
        \Cache::forget("contest_{$contest->id}_submissions");
        // delete vote:
        $vote->delete();

        return ['status' => 'success', 'message' => 'Vote was successfully deleted.'];
    }

    // Contest settings
    public function settings()
    {
        return view('admin.contests.settings');
    }
}
