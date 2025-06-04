<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VerificationCode;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class PasswordController extends Controller
{
    // forgot password
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ],
            [
                'email.required' => 'The email field is required.',
                'email.email'    => 'Please provide a valid email address.',
                'email.exists'   => 'This email address is not valid.',
            ]);

        $user = User::whereEmail($request->email)->first();
        $this->sendResetCode($user);

        // send email with reset code
        return response()->json([
            'status'  => 'success',
            'message' => 'Reset password code sent to your email.',
        ]);
    }

    // send reset code
    public function sendResetCode($user)
    {
        // get user
        $code = getNumber(6);
        $expirationTime = Carbon::now()->addMinutes(10);

        VerificationCode::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'password_reset'],
            ['code' => $code, 'expires_at' => $expirationTime]
        );
        // send email
        sendNotification(
            'RESET_PASSWORD',
            $user,
            [
                'name'        => $user->name,
                'username'    => $user->username,
                'reset_code'  => ($code),
                'expiry_time' => show_time($expirationTime),
                'expiry_mins' => 10,
            ]
        );
    }

    // resend code
    public function resendCode(Request $request)
    {
        // Validate the email input
        $request->validate([
            'email' => 'required|email|exists:users,email',
        ], [
            'email.required' => 'The email field is required.',
            'email.email'    => 'Please provide a valid email address.',
            'email.exists'   => 'This email address is not registered on our system.',
        ]);
        // Get the user by email
        $user = User::whereEmail($request->email)->first();

        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'User not found.',
            ], 404);  // 404 Not Found if the user does not exist
        }
        $waitTime = 2;
        $lastVerificationCode = VerificationCode::where('user_id', $user->id)->where('type', 'password_reset')->latest()->first();

        if ($lastVerificationCode && $lastVerificationCode->updated_at->addMinutes($waitTime)->gt(now())) {
            $remainingTime = number_format(($lastVerificationCode->updated_at->addMinutes($waitTime)->timestamp - now()->timestamp) / 60);

            return response()->json([
                'status'  => 'error',
                'message' => "Please wait {$remainingTime} more minute(s) before requesting a new code.",
            ], 429);
        }
        // Resend the reset code
        $this->sendResetCode($user);

        return response()->json([
            'status'  => 'success',
            'message' => 'Password reset code resent successfully.',
        ], 200);  // 200 OK for successful request
    }

    // confirm code
    public function confirmCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email',
            'code'  => 'required|numeric|digits:6',
        ],
            [
                'email.required' => 'The email field is required.',
                'email.email'    => 'Please provide a valid email address.',
                'email.exists'   => 'This email address is not registered on our system.',
                'code.required'  => 'The code field is required.',
                'code.numeric'   => 'The code must be a number.',
                'code.digits'    => 'The code must be 6 digits.',
            ]);

        $user = User::whereEmail($request->email)->first();
        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'password_reset')
            ->where('code', $request->code)
            ->first();

        if (! $verification) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid Verification code! Please try again.',
            ], 422);
        }

        if (Carbon::now()->gt($verification->expires_at)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Verification code has expired. Please resend the code.',
            ], 422);
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Verification code confirmed successfully.',
        ], 200);
    }

    // reset password
    public function resetPassword(Request $request)
    {
        $request->validate([
            'email'    => 'required|email|exists:users,email',
            'code'     => 'required|numeric|digits:6',
            'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()->symbols()->uncompromised()],
        ],
            [
                'email.required'     => 'The email field is required.',
                'email.email'        => 'Please provide a valid email address.',
                'email.exists'       => 'This email address is not registered on our system.',
                'code.required'      => 'The code field is required.',
                'code.numeric'       => 'The code must be a number.',
                'code.digits'        => 'The code must be 6 digits.',
                'password.required'  => 'The password field is required.',
                'password.min'       => 'The password must be at least 6 characters.',
                'password.confirmed' => 'The password confirmation does not match.',
            ]);

        $user = User::whereEmail($request->email)->first();
        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'password_reset')
            ->where('code', $request->code)
            ->first();

        if (! $verification) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Invalid Verification code! Please try again.',
            ], 422);
        }

        if (Carbon::now()->gt($verification->expires_at)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Verification code has expired. Please resend the code.',
            ], 422);
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);
        $verification->delete();

        // send email
        sendNotification(
            'PASSWORD_CHANGED',
            $user,
            [
                'name'     => $user->name,
                'username' => $user->username,
            ]
        );

        return response()->json([
            'status'  => 'success',
            'message' => 'Password reset successfully.',
        ], 200);
    }
}
