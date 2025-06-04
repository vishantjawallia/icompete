<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\AdminVerification;
use Carbon\Carbon;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class PasswordController extends Controller
{
    // Reset Password
    public function resetView()
    {
        if (\Auth::guard('admin')->check()) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.reset-password');
    }

    // forgot password
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admins,email',
        ],
            [
                'email.required' => 'The email field is required.',
                'email.email'    => 'Please provide a valid email address.',
                'email.exists'   => 'This email address is not valid.',
            ]);

        $admin = Admin::whereEmail($request->email)->first();
        $this->sendResetCode($admin);
        // add email to session
        session()->put('reset_email', $request->email);

        // send email with reset code
        return response()->json([
            'status'  => 'success',
            'url'     => route('admin.password.change'),
            'message' => 'Reset password code sent to your email.',
        ]);
    }

    // change password view
    public function changePassword(Request $request)
    {
        $email = session()->get('reset_email');

        return view('admin.auth.password', compact('email'));
    }

    // send reset code
    public function sendResetCode($admin)
    {
        // get admin
        $code = getNumber(6);
        $expirationTime = Carbon::now()->addMinutes(10);

        AdminVerification::updateOrCreate(
            ['admin_id' => $admin->id, 'type' => 'password_reset'],
            ['code' => $code, 'expires_at' => $expirationTime]
        );
        // send email
        sendNotification(
            'RESET_PASSWORD',
            $admin,
            [
                'name'        => $admin->name,
                'username'    => $admin->name,
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
            'email' => 'required|email|exists:admins,email',
        ], [
            'email.required' => 'The email field is required.',
            'email.email'    => 'Please provide a valid email address.',
            'email.exists'   => 'This email address is not registered on our system.',
        ]);
        // Get the admin by email
        $admin = Admin::whereEmail($request->email)->first();

        if (! $admin) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Admin not found.',
            ], 404);  // 404 Not Found if the admin does not exist
        }
        $waitTime = 2;
        $lastAdminVerification = AdminVerification::where('admin_id', $admin->id)->where('type', 'password_reset')->latest()->first();

        if ($lastAdminVerification && $lastAdminVerification->updated_at->addMinutes($waitTime)->gt(now())) {
            $remainingTime = number_format(($lastAdminVerification->updated_at->addMinutes($waitTime)->timestamp - now()->timestamp) / 60) + 1;

            return response()->json([
                'status'  => 'error',
                'message' => "Please wait {$remainingTime} more minutes before requesting a new code.",
            ], 429);
        }
        // Resend the reset code
        $this->sendResetCode($admin);

        return response()->json([
            'status'  => 'success',
            'message' => 'Password reset code resent successfully.',
        ], 200);  // 200 OK for successful request
    }

    // confirm code
    public function confirmCode(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:admins,email',
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

        $admin = Admin::whereEmail($request->email)->first();
        $verification = AdminVerification::where('admin_id', $admin->id)
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
            'email'    => 'required|email|exists:admins,email',
            'code'     => 'required|numeric|digits:6',
            'password' => ['required', 'confirmed', Rules\Password::min(8)->mixedCase()->numbers()],
        ],
            [
                'email.required'     => 'The email field is required.',
                'email.email'        => 'Please provide a valid email address.',
                'email.exists'       => 'This email address is not valid.',
                'code.required'      => 'The code field is required.',
                'code.numeric'       => 'The code must be a number.',
                'code.digits'        => 'The code must be 6 digits.',
                'password.required'  => 'The password field is required.',
                'password.min'       => 'The password must be at least 6 characters.',
                'password.confirmed' => 'The password confirmation does not match.',
            ]);

        $admin = Admin::whereEmail($request->email)->first();
        $verification = AdminVerification::where('admin_id', $admin->id)
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

        $admin->update([
            'password' => Hash::make($request->password),
        ]);
        $verification->delete();
        // login user and redirect to dashboard
        \Auth::guard('admin')->login($admin);

        return response()->json([
            'status'  => 'success',
            'url'     => route('admin.dashboard'),
            'message' => 'Password reset successfully.',
        ], 200);
    }
}
