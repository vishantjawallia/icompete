<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Auth;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;

class RegisterController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request)
    {
        $request->validate([
            'first_name' => ['required', 'string', 'min:2', 'max:255', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'last_name'  => ['required', 'string', 'min:2', 'max:255', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'phone'      => ['required', 'string', 'max:20'],
            'email'      => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'username'   => ['required', 'string', 'min:5', 'alpha_dash', 'max:30', 'unique:' . User::class],
            'password'   => ['required', 'string', Rules\Password::defaults()],
            'role'       => 'required|in:contestant,voter,guest',
        ]);

        // purify request
        $req = \Purify::clean($request->all());
        $request = (object) $req;
        $user = User::create([
            'first_name' => $request->first_name,
            'last_name'  => $request->last_name,
            'username'   => strtolower($request->username),
            'role'       => $request->role,
            'phone'      => $request->phone,
            'email'      => strtolower($request->email),
            'password'   => Hash::make($request->password),
        ]);
        Auth::login($user);
        // send email verification
        $vc = new VerifyController();
        $vc->sendVerifyCode();
        // Notify Admin
        $this->completeRegistration($user);

        $token = $user->createToken('User-App-Token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'User Registered Successful. A verification Code has been sent to your email',
            'token'   => $token,
            'user'    => $user,
        ]);

    }

    /**
     * Handle an organizer registration request.
     *
     * This method validates the incoming request data for organizer registration,
     * including first name, last name, phone, email, username, password, and
     * organization name. It purifies the request data, creates a new organizer
     * user account, logs in the user, sends an email verification code, and
     * returns a JSON response with a success message and an authentication token.
     *
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @throws \Illuminate\Validation\ValidationException
     **/
    public function organizerRegistration(Request $request)
    {
        $request->validate([
            'first_name'        => ['required', 'string', 'min:2', 'max:255', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'last_name'         => ['required', 'string', 'min:2', 'max:255', 'regex:/^[A-Za-z0-9\s\-]+$/'],
            'phone'             => ['required', 'string', 'min:8', 'max:20', 'unique:' . User::class],
            'email'             => ['required', 'string', 'email', 'max:255', 'unique:' . User::class],
            'username'          => ['required', 'string', 'min:5', 'alpha_dash', 'max:30', 'unique:' . User::class],
            'password'          => ['required', 'string', Rules\Password::defaults()],
            'organization_name' => 'required|string|max:255',
        ]);
        // purify request
        $req = \Purify::clean($request->all());
        $request = (object) $req;

        $user = User::create([
            'first_name'        => $request->first_name,
            'last_name'         => $request->last_name,
            'username'          => strtolower($request->username),
            'role'              => 'organizer',
            'phone'             => $request->phone,
            'email'             => strtolower($request->email),
            'password'          => Hash::make($request->password),
            'organization_name' => $request->organization_name,
        ]);

        Auth::login($user);
        // send email verification
        $vc = new VerifyController();
        $vc->sendVerifyCode();
        // Notify Admin
        $this->completeRegistration($user);

        $token = $user->createToken('User-App-Token')->plainTextToken;

        return response()->json([
            'status'  => 'success',
            'message' => 'Registration Successful. A verification Code has been sent to your email',
            'token'   => $token,
            'user'    => $user,
        ]);
    }

    // complete registration
    public function completeRegistration($user)
    {
        // send email to admin
        notifyAdmin('ADMIN_NEW_USER',
            [
                'username'          => $user->username,
                'name'              => $user->name,
                'user_email'        => $user->email,
                'registration_date' => show_datetime($user->created_at),
                'user_type'         => $user->role,
                'ip_address'        => request()->ip(),
                'user_link'         => route('admin.users.view', $user->id),
                'link'              => route('admin.users.view', $user->id),
            ], [
                'user_id' => $user->id,
                'url'     => route('admin.users.view', $user->id),
                'type'    => 'ADMIN_NEW_USER',
            ]);
    }
}
