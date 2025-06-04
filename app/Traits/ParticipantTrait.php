<?php

namespace App\Traits;

use App\Models\Contest;
use App\Models\Submission;
use Illuminate\Validation\ValidationException;

trait ParticipantTrait
{
    use RankTrait;

    /**
     * Return a formatted object for a submission (participant)
     *
     * @param  Submission  $item
     * @return array
     */
    private function participantObject($item)
    {
        $data = [
            'id'          => $item->id,
            'contest_id'  => $item->contest_id,
            'title'       => $item->title,
            'description' => $item->description,
            'status'      => $item->vote_status,
            'media'       => $this->getMedia($item),
        ];
        $data['created_at'] = $item->created_at;
        $data['response'] = collect($item->response)->map(function ($response) {
            // Add asset_url  for image or video types
            if (in_array($response->type, ['image', 'video'])) {
                $response->asset_url = my_asset($response->value);
            } else {
                $response->asset_url = null; // No asset_url  for text type
            }

            return $response;
        });

        if ($item->contest) {
            $data['contest'] = [
                'id'         => $item->contest_id,
                'category'   => $item->contest->category->name ?? '',
                'name'       => $item->contest->title,
                'amount'     => $item->contest->amount,
                'image'      => ($item->contest->image) ? my_asset($item->contest->image) : my_asset('contests/default.jpg'),
                'type'       => $item->contest->type,
                'entry_type' => $item->contest->entry_type,
                'amount'     => ($item->contest->amount),
                'slug'       => $item->contest->slug,
                'prize'      => $item->contest->prize,
                'featured'   => $item->contest->featured,
                'entry_fee'  => $item->contest->entry_fee,
            ];
        }

        if ($item->user) {
            $data['user'] = [
                'id'       => $item->user_id,
                'username' => $item->user->username,
                'name'     => $item->user->fullname,
                'image'    => ($item->user->image) ? my_asset($item->user->image) : my_asset('users/default.jpg'),
            ];
        }
        $data['results'] = [
            'votes'     => $item->vote_count,
            'rank'      => $item->ranking(),
            'is_winner' => $item->is_winner,
        ];

        // add votes object
        return $data;
    }

    // short Object
    private function participantObjectShort($item)
    {
        $data = [
            'id'         => $item->id,
            'contest_id' => $item->contest_id,
            'title'      => $item->title,
            'status'     => $item->vote_status,
            'media'      => $this->getMedia($item),
        ];
        $data['created_at'] = $item->created_at;

        if ($item->contest) {
            $data['contest'] = [
                'id'         => $item->contest_id,
                'category'   => $item->contest->category->name ?? '',
                'name'       => $item->contest->title,
                'image'      => ($item->contest->image) ? my_asset($item->contest->image) : my_asset('contests/default.jpg'),
                'type'       => $item->contest->type,
                'entry_type' => $item->contest->entry_type,
                'amount'     => ($item->contest->amount),
                'slug'       => $item->contest->slug,
                'prize'      => $item->contest->prize,
                'featured'   => $item->contest->featured,
                'entry_fee'  => $item->contest->entry_fee,
            ];
        }
        $data['results'] = [
            'votes'     => $item->vote_count,
            'rank'      => $item->ranking(),
            'is_winner' => $item->is_winner,
        ];

        // add votes object
        return $data;
    }

    // submissions
    private function submissionObject($item)
    {
        $data = [
            'id'          => $item->id,
            'contest_id'  => $item->contest_id,
            'title'       => $item->title,
            'description' => $item->description,
            'status'      => $item->status,
            'media'       => $this->getMedia($item),
        ];
        $data['response'] = collect($item->response)->map(function ($response) {
            // Add asset_url  for image or video types
            if (in_array($response->type, ['image', 'video'])) {
                $response->asset_url = my_asset($response->value);
            } else {
                $response->asset_url = null; // No asset_url  for text type
            }

            return $response;
        });
        $data['created_at'] = $item->created_at;

        if ($item->contest) {
            $data['contest'] = [
                'id'         => $item->contest_id,
                'category'   => $item->contest->category->name ?? '',
                'name'       => $item->contest->title,
                'image'      => ($item->contest->image) ? my_asset($item->contest->image) : my_asset('contests/default.jpg'),
                'type'       => $item->contest->type,
                'entry_type' => $item->contest->entry_type,
                'amount'     => ($item->contest->amount),
                'slug'       => $item->contest->slug,
                'prize'      => $item->contest->prize,
                'featured'   => $item->contest->featured,
                'entry_fee'  => $item->contest->entry_fee,
            ];
        }

        if ($item->user) {
            $data['user'] = [
                'id'       => $item->user_id,
                'username' => $item->user->username,
                'name'     => $item->user->fullname,
                'image'    => ($item->user->image) ? my_asset($item->user->image) : my_asset('users/default.jpg'),
            ];
        }

        if ($item->status == 'rejected') {
            $data['rejection_reason'] = $item->rejection_reason;
        }

        return $data;
    }

    // entry on=bject
    private function entryObject($item)
    {
        $data = [
            'id'          => $item->id,
            'contest_id'  => $item->contest_id,
            'title'       => $item->title,
            'description' => $item->description,
            'status'      => $item->status,
            'vote_status' => $item->vote_status,
            'media'       => $this->getMedia($item),
            'type'        => $item->type,
        ];
        $data['created_at'] = $item->created_at;
        $data['response'] = collect($item->response)->map(function ($response) {
            // Add asset_url  for image or video types
            if (in_array($response->type, ['image', 'video'])) {
                $response->asset_url = my_asset($response->value);
            } else {
                $response->asset_url = null; // No asset_url  for text type
            }

            return $response;
        });

        if ($item->contest) {
            $data['contest'] = [
                'id'         => $item->contest_id,
                'category'   => $item->contest->category->name ?? '',
                'name'       => $item->contest->title,
                'image'      => ($item->contest->image) ? my_asset($item->contest->image) : my_asset('contests/default.jpg'),
                'type'       => $item->contest->type,
                'entry_type' => $item->contest->entry_type,
                'amount'     => ($item->contest->amount),
                'slug'       => $item->contest->slug,
                'prize'      => $item->contest->prize,
                'featured'   => $item->contest->featured,
                'entry_fee'  => $item->contest->entry_fee,
            ];
        }

        if ($item->user) {
            $data['user'] = [
                'id'       => $item->user_id,
                'username' => $item->user->username,
                'name'     => $item->user->fullname,
                'image'    => ($item->user->image) ? my_asset($item->user->image) : my_asset('users/default.jpg'),
            ];
        }
        $data['results'] = [
            'votes'     => $item->vote_count,
            'rank'      => $item->ranking(),
            'is_winner' => $item->is_winner,
        ];

        if ($item->status == 'rejected') {
            $data['rejection_reason'] = $item->rejection_reason;
        }

        // add votes object
        return $data;
    }

    // get media
    private function getMedia($item)
    {
        // Check if response has image or video
        foreach ($item->response as $response) {
            if (in_array($response->type, ['image', 'video'])) {
                // If image/video is found, use the asset_url
                return [
                    'type'      => $response->type,
                    'asset_url' => my_asset($response->value),
                ];
            }
        }

        // If no image or video, use the user's profile image
        return [
            'type'      => 'image',
            'asset_url' => my_asset($item->user->image ?? 'users/default.jpg'),
        ];
    }

    // process submission
    protected function processSubmission(Contest $contest, $request)
    {
        $requirements = collect($contest->requirements);
        $submission = collect($request->input('response', []));
        $processedResponses = [];
        $errors = [];

        // Validate that all requirements are met
        foreach ($requirements as $requirement) {
            $response = $submission->firstWhere('name', $requirement['name']);

            if (! $response) {
                $errors[] = "Missing response for: {$requirement['name']}";

                continue;
            }

            if ($response['type'] !== $requirement['type']) {
                $errors[] = "Invalid type for {$requirement['name']}: expected {$requirement['type']}, got {$response['type']}";

                continue;
            }

            try {
                $processedValue = $this->processResponse($response, $contest->id);
                $processedResponses[] = [
                    'name'  => $requirement['name'],
                    'type'  => $requirement['type'],
                    'value' => $processedValue,
                ];
            } catch (\Exception $e) {
                $errors[] = "Error processing {$requirement['name']}: {$e->getMessage()}";
            }
        }

        if (! empty($errors)) {
            throw ValidationException::withMessages($errors);
        }

        return $processedResponses;
    }

    protected function processResponse($response, $contestId)
    {
        switch ($response['type']) {
            case 'text':
                return $this->validateText($response['value']);

            case 'image':
                return $this->moveFileFromTemp(
                    $response['value'],
                    'participants/images',
                    $contestId
                );

            case 'video':
                return $this->moveFileFromTemp(
                    $response['value'],
                    'participants/videos',
                    $contestId
                );

            default:
                throw new \Exception("Unsupported response type: {$response['type']}");
        }
    }

    // validate text
    protected function validateText($value)
    {
        if (empty($value) || ! is_string($value)) {
            throw new \Exception('Invalid text value');
        }

        return strip_tags($value);
    }

    protected function moveFileFromTemp($filename, $destinationPath, $contestId)
    {
        // Check if file exists and determine its path
        if (str_starts_with($filename, 'participants/')) {
            // File is already in participants directory
            $tempPath = $filename;
            $oldFile = public_path("uploads/{$filename}");
        } else {
            // File is in temp directory
            $tempPath = 'temp/' . $filename;
            $oldFile = public_path("temp/{$filename}");

            if (! file_exists($oldFile)) {
                throw new \Exception("File does not exist: {$filename}");
            }
        }

        // $tempPath = 'temp/' . $filename;
        // $tempFile = public_path("temp/{$filename}");
        // if (!file_exists($tempFile)) {
        //     throw new \Exception("Uploaded File does not exist");
        // }

        try {
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            // Generate new filename with contest ID prefix
            $fileName = now()->timestamp . '-' . \Str::random(26) . '.' . $extension;
            $newFilename = $contestId . '_' . $filename;

            // Move file to new location
            $targetDir = public_path("uploads/{$destinationPath}/");
            $targetFile = "{$targetDir}{$fileName}";

            if (! file_exists($targetDir)) {
                mkdir($targetDir, 0777, true); // Recursive directory creation
            }

            if (copy($oldFile, $targetFile)) {
                // Return the relative file path for storage
                return "{$destinationPath}/{$fileName}";
            }

            // if (rename($tempFile, $targetFile)) {
            //     // Return the relative file path for storage
            //     return "{$destinationPath}/{$fileName}";
            // }

            throw new \Exception('Failed to move file');
        } catch (\Exception $e) {
            \Log::error('File move failed', [
                'temp_path'   => $tempPath,
                'destination' => $destinationPath,
                'error'       => $e->getMessage(),
            ]);

            throw new \Exception('Error processing file: ' . $e->getMessage());
        }
    }

    // entry update
    protected function processUpdates($existingResponses, $updates, $contestId)
    {
        $updatedResponses = [];

        foreach ($existingResponses as $existing) {
            // Find if there's an update for this response
            $update = collect($updates)->firstWhere('name', $existing->name);

            try {
                if (! $update) {
                    // If no update provided, keep existing response as is
                    $updatedResponses[] = [
                        'name'  => $existing->name,
                        'type'  => $existing->type,
                        'value' => $existing->value,
                    ];
                } else {
                    // Process the update
                    $updatedResponses[] = $this->processResponseUpdate(
                        $existing,
                        $update,
                        $contestId
                    );
                }
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    $existing->name => $e->getMessage(),
                ]);
            }
        }

        return $updatedResponses;
    }

    protected function processResponseUpdate($existing, $update, $contestId)
    {
        // If no value provided in update, return existing
        if (! isset($update['value']) || $update['value'] === $existing->value) {
            return $existing;
        }
        switch ($existing->type) {
            case 'text':
                return [
                    'name'  => $existing->name,
                    'type'  => 'text',
                    'value' => $this->validateText($update['value']),
                ];

            case 'image':
            case 'video':

                // Move new file to permanent storage
                $newPath = $this->moveFileFromTemp(
                    $update['value'],
                    'participants/' . $existing->type . 's',
                    $contestId
                );

                // Delete old file if it exists (and isn't in use by other submissions)
                if (isset($existing->value)) {
                    $this->deleteOldFile($existing->value);
                }

                return [
                    'name'  => $existing->name,
                    'type'  => $existing->type,
                    'value' => $newPath,
                ];

            default:
                throw new \Exception("Unsupported response type: {$existing->type}");
        }
    }

    protected function deleteOldFile($path)
    {
        // You might want to check if file is used by other submissions before deleting
        $targetDir = public_path("uploads/{$path}/");

        if (file_exists($targetDir)) {
            unlink($targetDir); // Recursive directory creation
        }
    }

    // contest stats
    protected function contestStats($item)
    {
        $data = [
            'id'          => $item->id,
            'title'       => $item->title,
            'total_votes' => $item->votes->sum('quantity'),
        ];

        return $data;
    }
}
