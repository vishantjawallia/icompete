<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\LoginHistory;
use App\Traits\ApiResponse;
use App\Traits\UserTrait;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use ApiResponse, UserTrait;

    public function store(LoginRequest $request)
    {
        $request->authenticate();
        $user = $request->user();

        // check if account is deleted
        if ($user->is_deleted != null) {
            return $this->errorResponse('Your account has been deleted.', 401);
        }
        $this->saveLoginHistory($user);
        $token = $user->createToken('User-App-Token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'User Login Successful',
            'token'   => $token,
            'user'    => $this->userObject($user) ?? $user,
        ]);

    }

    // logout
    public function destroy(Request $request)
    {
        $user = $request->user();
        $user->push_token = null;
        $user->save();
        $user->currentAccessToken()->delete();

        return response()->json([
            'status'  => 'success',
            'message' => 'User Logged Out Successfully',
        ]);
    }

    public function saveLoginHistory($user)
    {
        try {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $ipAddress = $_SERVER['REMOTE_ADDR'];

            // Get browser and OS details
            $browser = getBrowserDetails($userAgent);
            $os = getOSDetails($userAgent);

            // Get geolocation information
            $location = getGeoLocation($ipAddress);
            $city = $location['city'] ?? 'Unknown City';
            $country = $location['country'] ?? 'Unknown Country';
            // Save the login details
            $exist = LoginHistory::where('ip_address', $ipAddress)->first();
            $loginDetails = new LoginHistory();

            if ($exist) {
                $loginDetails->device = $exist->device;
                $loginDetails->city = $exist->city;
                $loginDetails->country = $exist->country;
            } else {
                $loginDetails->device = $userAgent;
                $loginDetails->city = $city;
                $loginDetails->country = $country;
            }

            $loginDetails->user_id = $user->id;
            $loginDetails->ip_address = $ipAddress;
            $loginDetails->browser = $browser;
            $loginDetails->meta = json_encode($location);
            $loginDetails->os = $os;

            $loginDetails->save();
            // send login email?
        } catch (\Exception $e) {
            \Log::error('Error saving login history: ' . $e->getMessage(), ['error' => $e]);
        }

    }
}
