<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\VerificationCode;
use Auth;
use Carbon\Carbon;
use Illuminate\Http\Request;

class VerifyController extends Controller
{
    // send verification code
    public function sendVerifyCode()
    {
        // get user
        $user = Auth::user();
        $code = getNumber(6);
        $expirationTime = Carbon::now()->addMinutes(15);

        // Store verification code
        VerificationCode::updateOrCreate(
            ['user_id' => $user->id, 'type' => 'email_verification'],
            ['code' => $code, 'expires_at' => $expirationTime]
        );

        // send email
        sendNotification(
            'EMAIL_VERIFY',
            $user,
            [
                'name'              => $user->name,
                'username'          => $user->username,
                'verification_code' => ($code),
                'expiry_time'       => 15,
            ]
        );
    }

    // verify code
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric|digits:6',
        ]);
        $user = Auth::user();

        $verification = VerificationCode::where('user_id', $user->id)
            ->where('type', 'email_verification')
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

        if ($user->email_verify) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Email is already verified.',
            ], 200);
        }

        $this->markEmailAsVerified($user);
        $verification->delete();

        // Return success response after verification
        return response()->json([
            'status'  => 'success',
            'message' => 'Email verified successfully.',
        ], 200);
    }

    // resend verification code
    public function resend(Request $request)
    {
        if ($request->user()->email_verify == 1) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Email is already verified.',
            ], 200);
        }

        $lastVerificationCode = VerificationCode::where('user_id', $request->user()->id)->where('type', 'email_verification')->latest()->first();

        if ($lastVerificationCode && $lastVerificationCode->updated_at->addMinutes(5)->gt(now())) {
            $remainingTime = number_format(($lastVerificationCode->updated_at->addMinutes(5)->timestamp - now()->timestamp) / 60);

            return response()->json([
                'status'  => 'error',
                'message' => "Please wait {$remainingTime} more minute(s) before requesting a new code.",
            ], 429);
        }

        // resend verification code
        $this->sendVerifyCode();

        return response()->json([
            'status'  => 'success',
            'message' => 'Verification code resent successfully. Codes Expires in 15 minutes.',
        ], 202);
    }

    // mark email as verified
    private function markEmailAsVerified($user)
    {
        $user->update([
            'email_verify'      => 1,
            'email_verified_at' => now(),
        ]);

        session()->regenerate();

        if ($user->user_role == 'organizer') {
            sendNotification(
                'ORGANIZER_WELCOME',
                $user,
                [
                    'name'     => $user->name,
                    'username' => $user->username,
                    'email'    => $user->email,
                ]
            );
        } else {
            sendNotification(
                'USER_WELCOME',
                $user,
                [
                    'name'     => $user->name,
                    'username' => $user->username,
                    'email'    => $user->email,
                ]
            );
        }
    }
}
