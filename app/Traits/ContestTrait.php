<?php

namespace App\Traits;

use App\Models\Contest;
use App\Models\Submission;
use Illuminate\Http\Request;
use Str;

trait ContestTrait
{
    // validate contest
    public function validateContest(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'title'                      => 'required|string|max:100',
            'category_id'                => 'required|exists:categories,id',
            'description'                => 'nullable|string',
            'type'                       => 'required|in:free,paid,exclusive',
            'image'                      => 'nullable|string',
            'entry_type'                 => 'required|in:free,paid,exclusive',
            'prize'                      => 'required|numeric|min:10|max:100',
            'start_date'                 => 'required|date|after_or_equal:today',
            'end_date'                   => 'required|date|after:start_date',
            'voting_start_date'          => 'nullable|date|after:start_date',
            'voting_end_date'            => 'nullable|date|after:voting_start_date',
            'max_entries'                => 'nullable|integer|min:1',
            'rules'                      => 'nullable|string',
            'requirements'               => 'required|array',
            'requirements.*.name'        => 'required|string|max:100',
            'requirements.*.type'        => 'required|in:image,video,text',
            'requirements.*.description' => 'nullable|string|max:500',
        ], [
            'category_id.required'           => 'The category is required.',
            'category_id.exists'             => 'The selected category is invalid.',
            'requirements.array'             => 'Requirements must be a valid array.',
            'requirements.*.name.required'   => 'Each requirement must have a name.',
            'requirements.*.name.max'        => 'The requirement name must not exceed 100 characters.',
            'requirements.*.type.required'   => 'Each requirement must have a type.',
            'requirements.*.type.in'         => 'The requirement type must be one of: image, video, or text.',
            'requirements.*.description.max' => 'The requirement description must not exceed 500 characters.',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (isset($request->requirements)) {
                $names = array_column($request->requirements, 'name');

                if (count($names) !== count(array_unique($names))) {
                    $validator->errors()->add('requirements', 'Requirement names must be unique.');
                }
            }
        });

        return $validator->validate();
    }

    public function updateValidation(Request $request)
    {
        $validator = \Validator::make($request->all(), [
            'title'                      => 'nullable|string|max:100',
            'category_id'                => 'nullable|exists:categories,id',
            'description'                => 'nullable|string',
            'type'                       => 'nullable|in:free,paid,exclusive',
            'amount'                     => 'nullable|numeric|min:5',
            'image'                      => 'nullable|string',
            'entry_type'                 => 'nullable|in:free,paid,exclusive',
            'entry_fee'                  => 'nullable|numeric|min:0',
            'prize'                      => 'nullable|numeric|min:10|max:100',
            'start_date'                 => 'nullable|date',
            'end_date'                   => 'nullable|date|after:start_date',
            'voting_start_date'          => 'nullable|date|after:start_date',
            'voting_end_date'            => 'nullable|date|after:voting_start_date',
            'max_entries'                => 'nullable|integer|min:1',
            'rules'                      => 'nullable|string',
            'requirements'               => 'nullable|array',
            'requirements.*.name'        => 'required|string|max:100',
            'requirements.*.type'        => 'required|in:image,video,text',
            'requirements.*.description' => 'nullable|string|max:500',
        ], [
            'category_id.required'           => 'The category is required.',
            'category_id.exists'             => 'The selected category is invalid.',
            'requirements.array'             => 'Requirements must be a valid array.',
            'requirements.*.name.required'   => 'Each requirement must have a name.',
            'requirements.*.name.max'        => 'The requirement name must not exceed 100 characters.',
            'requirements.*.type.required'   => 'Each requirement must have a type.',
            'requirements.*.type.in'         => 'The requirement type must be one of: image, video, or text.',
            'requirements.*.description.max' => 'The requirement description must not exceed 500 characters.',
        ]);

        return $validator->validate();
    }

    // upload image
    public function moveImage($folder, $filePath)
    {
        // Define the source file path
        $tempFile = public_path("temp/{$filePath}");

        // Check if the file exists in the temp directory
        if (! file_exists($tempFile)) {
            return; // Return null if the file doesn't exist
        }

        // Generate a unique file name
        $extension = pathinfo($filePath, PATHINFO_EXTENSION); // Get the file extension
        $fileName = now()->timestamp . '-' . Str::random(26) . '.' . $extension;

        // Define the target directory and file path
        $targetDir = public_path("uploads/{$folder}/");
        $targetFile = "{$targetDir}{$fileName}";

        // Ensure the target directory exists, create it if not
        if (! file_exists($targetDir)) {
            mkdir($targetDir, 0777, true); // Recursive directory creation
        }

        // Move the file to the target directory
        if (rename($tempFile, $targetFile)) {
            // Return the relative file path for storage
            return "{$folder}/{$fileName}";
        }

        // If the move operation fails, return null

    }

    // fetch contests
    public function fetchContests(Request $request)
    {
        $pp = $request->count ?? 20;
        $page = $request->input('page', 1);
        $query = Contest::whereStatus('active')->orderByDesc('id');

        // Paginate the query directly
        $result = $query->paginate($pp, ['*'], 'page', $page);
        // format data
        $objectData = $result->getCollection()->shuffle()->transform(function ($contest) {
            return $this->contestObject($contest);
        });

        return response()->json([
            'status'        => 'success',
            'message'       => 'Contests retrieved successfully.',
            'data'          => $objectData,
            'total'         => $result->total(),
            'current_page'  => $result->currentPage(),
            'current_items' => $result->count(),
            'previous_page' => $result->previousPageUrl(),
            'next_page'     => $result->nextPageUrl(),
            'last_page'     => $result->lastPage(),
        ]);
    }

    // contest object
    private function contestObject($contest)
    {
        return [
            'id'                => $contest->id,
            'title'             => $contest->title,
            'description'       => $contest->description,
            'category_id'       => $contest->category_id,
            'category'          => $contest->category->name ?? 'Others',
            'entry_type'        => $contest->entry_type,
            'entry_fee'         => $contest->entry_fee,
            'image'             => ($contest->image) ? my_asset($contest->image) : my_asset('contests/default.jpg'),
            'type'              => $contest->type,
            'amount'            => ($contest->amount),
            'slug'              => $contest->slug,
            'prize'             => $contest->prize,
            'featured'          => $contest->featured,
            'start_date'        => $contest->start_date,
            'end_date'          => $contest->end_date,
            'voting_start_date' => $contest->voting_start_date,
            'voting_end_date'   => $contest->voting_end_date,
            'status'            => $contest->status,
        ];
    }

    private function contestObjectFull($contest)
    {
        $object = [
            'id'                => $contest->id,
            'title'             => $contest->title,
            'description'       => $contest->description,
            'category_id'       => $contest->category_id,
            'category'          => $contest->category->name ?? '',
            'entry_type'        => $contest->entry_type,
            'entry_fee'         => $contest->entry_fee,
            'image'             => ($contest->image) ? my_asset($contest->image) : my_asset('contests/default.jpg'),
            'type'              => $contest->type,
            'amount'            => ($contest->amount),
            'slug'              => $contest->slug,
            'start_date'        => $contest->start_date,
            'end_date'          => $contest->end_date,
            'voting_start_date' => $contest->voting_start_date ? $contest->voting_start_date : null,
            'voting_end_date'   => $contest->voting_end_date ? $contest->voting_end_date : null,
            'status'            => $contest->status,
            'prize'             => $contest->prize,
            'rules'             => $contest->rules,
            'requirements'      => $contest->requirements,
            'featured'          => $contest->featured,
            'created_at'        => $contest->created_at,
            'updated_at'        => $contest->updated_at,
        ];
        $object['entries'] = [
            'max'   => $contest->max_entries,
            'total' => $contest->entry->count(),
        ];

        if ($contest->organizer) {
            $object['organizer'] = [
                'id'       => $contest->user_id,
                'username' => $contest->organizer->username,
                'name'     => $contest->organizer->fullname,
                'image'    => ($contest->organizer->image) ? my_asset($contest->organizer->image) : my_asset('users/default.jpg'),
            ];
        }

        return $object;
    }

    // get contest price
    public function getContestPrice($type)
    {
        // for types in free, paid and exclusive.
        $prices = [
            'free'      => sys_setting('free_voting') ?? 0,
            'paid'      => sys_setting('paid_voting') ?? 10,
            'exclusive' => sys_setting('exclusive_voting') ?? 50,
        ];

        // return price based on type selected
        return $prices[$type] ?? 0;
    }

    public function getEntryPrice($type)
    {
        $prices = [
            'free'      => sys_setting('free_entry') ?? 0,
            'paid'      => sys_setting('paid_entry') ?? 50,
            'exclusive' => sys_setting('exclusive_entry') ?? 200,
        ];

        // return price based on type selected
        return $prices[$type] ?? 0;
    }

    // close a single contest
    public function closeContest($contest)
    {
        if ($contest->voting_ended == 1) {
            return;
        }
        // Update status
        $contest->update(['status' => 'completed', 'voting_ended' => 1]);

        // Notify organizer
        sendNotification(
            'ORG_VOTING_END',
            $contest->organizer,
            [
                'username'     => $contest->organizer->username,
                'contest_name' => $contest->title,
                'total_votes'  => $contest->votes->sum('quantity'),
                'entry_count'  => $contest->entry->count(),
            ],
            [
                'user_id'    => $contest->organizer_id,
                'contest_id' => $contest->id,
                'type'       => 'ORGANIZER_VOTING_END',
            ]
        );

        // Notify all participants
        $notifiedUsers = [];

        if ($contest->participants()->count() > 0) {
            $participants = Submission::where('contest_id', $contest->id)->where('type', 'entry')
                ->where('vote_status', 'enabled')->orderByDesc('vote_count')->get();
            foreach ($participants as $participant) {
                $rank = $participant->ranking();

                // only send if user has more than 0 votes
                if ($participant->vote_count > 0) {
                    // skip if user has already been notified
                    if (in_array($participant->user_id, $notifiedUsers)) {
                        continue;
                    }
                    sendNotification(
                        'CONTEST_CLOSED',
                        $participant->user,
                        [
                            'username'      => $participant->user->username,
                            'contest_name'  => $contest->title,
                            'total_entries' => $contest->entry->count(),
                            'total_votes'   => $participant->vote_count,
                            'current_rank'  => $rank ?? 'N/A',
                        ],
                        [
                            'user_id'    => $participant->user_id,
                            'contest_id' => $contest->id,
                            'entry_id'   => $participant->id,
                            'type'       => 'CONTEST_COMPLETED',
                        ]
                    );
                    // Add user to notified list
                    $notifiedUsers[] = $participant->user_id;
                }
            }
        }

        // process contest winner:
        if ($contest->participants()->where('vote_status', 'enabled')->count() > 0) {
            $contest->processWinner();
        }
    }
}
