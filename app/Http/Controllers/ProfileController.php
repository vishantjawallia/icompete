<?php

namespace App\Http\Controllers;

use App\Traits\ApiResponse;
use App\Traits\UserTrait;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Factory;
use Purify;

class ProfileController extends Controller
{
    use ApiResponse, UserTrait;

    /**
     * Display the user's profile form.
     */
    public function show(Request $request)
    {
        return $this->successResponse('User updated successfully.', $this->userObject($request->user()));
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'sometimes|string|max:55',
            'last_name'  => 'sometimes|string|max:45',
            'phone'      => 'sometimes|string|max:20|',
            'image'      => 'nullable|string',
            'gender'     => 'sometimes|string|in:male,female,other',
            'bio'        => 'sometimes|string|max:1000',
        ]);
        $req = Purify::clean($validated);

        if ($request->image != null) {
            $req['image'] = moveImage('users', $request->image);
        }
        $user = Auth::user();
        $user->update($req);

        return $this->successResponse('User updated successfully.', $this->userObject($user));
    }

    /**
     * Update the user's password.
     */
    public function updatePassword(Request $request)
    {
        // Validate the input data
        $validated = $request->validate([
            'current_password' => 'required|string|min:8',
            'new_password'     => 'required|string|min:8|confirmed',
        ], [
            'new_password.confirmed' => 'The passwords does not match each other.',
        ]);

        // Check if the provided current password is correct
        if (! Hash::check($validated['current_password'], Auth::user()->password)) {
            return $this->errorResponse('Current password is incorrect.', 400);
        }

        // Update the password if the current password is correct
        $user = Auth::user();
        $user->password = Hash::make($validated['new_password']);
        $user->save();

        return $this->successResponse('Password updated successfully.');
    }

    /**
     * Get all notifications for the current user.
     */
    public function notifications()
    {
        $user = Auth::user();
        $result = $user->notifys()->latest()->paginate(50);

        $objectData = $result->getCollection()->transform(function ($notification) {
            return $this->notifyObject($notification);
        });

        return response()->json([
            'status'        => 'success',
            'message'       => 'Notifications retrieved successfully.',
            'data'          => $objectData,
            'total'         => $result->total(),
            'current_page'  => $result->currentPage(),
            'current_items' => $result->count(),
            'previous_page' => $result->previousPageUrl(),
            'next_page'     => $result->nextPageUrl(),
            'last_page'     => $result->lastPage(),
        ]);

    }

    /**
     * Get all unread notifications for the current user.
     */
    public function unreadNotifications()
    {
        $user = Auth::user();
        $result = $user->notifys()->unread()->latest()->paginate(50);

        $objectData = $result->getCollection()->transform(function ($notification) {
            return $this->notifyObject($notification);
        });

        return response()->json([
            'status'        => 'success',
            'message'       => 'Unread notifications retrieved successfully.',
            'data'          => $objectData,
            'total'         => $result->total(),
            'current_page'  => $result->currentPage(),
            'current_items' => $result->count(),
            'previous_page' => $result->previousPageUrl(),
            'next_page'     => $result->nextPageUrl(),
            'last_page'     => $result->lastPage(),
        ]);
    }

    /**
     * Mark a notification as read.
     *
     * @param  string  $id
     */
    public function readNotification($id)
    {
        $user = Auth::user();
        $notification = $user->notifys()->findOrFail($id);

        $notification->update([
            'read_at' => now(),
        ]);

        return $this->successResponse('Notification marked as read.');
    }

    /**
     * Update the user's FCM token
     *
     * @return \Illuminate\Http\Response
     */
    public function updateFcmToken(Request $request)
    {
        // send to firbase
        $valreq = $request->validate([
            'push_token' => 'required|string',
        ]);
        $user = Auth::user();
        // subscribe to topic based on their roles.
        $topic = 'icompete_' . $user->role;

        $firebase = (new Factory())->withServiceAccount(config('firebase.credentials_file'));
        $messaging = $firebase->createMessaging();

        try {
            $validated = $messaging->validateRegistrationTokens($request->push_token);

            if ($validated['valid'][0] == $request->push_token) {
                $user->update($valreq); // update user token
                $messaging->subscribeToTopic($topic, $request->push_token);

                return $this->successResponse('FCM token updated successfully.');
            }
        } catch (\Exception $e) {
            \Log::error('Error updating FCM token', [
                'type'  => 'FCM',
                'user'  => $user->id,
                'error' => $e->getMessage(),
            ]);

            return $this->errorResponse('Invalid FCM token.', 400);
        }
    }

    public function deleteAccount(Request $request)
    {
        $user = Auth::user();

        // delete all data related to a user
        $user->notifys()->delete();
        $user->loginHistory()->delete();
        // $user->coinTransaction()->delete();
        // $user->posts()->delete();
        // $user->verificationCodes()->delete();

        // $user->contests()->delete(); // if user is organizer, set contest status to closed
        // $user->entry()->delete(); // delete all entries

        $user->status = 'banned';
        $user->is_deleted = now();
        $user->save();
        // delete user auth tokens
        $request->user()->tokens()->delete();

        return $this->successResponse('User account deleted successfully.');
    }
}
