<?php

namespace App\Http\Controllers\Admin\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    protected function guard()
    {
        return auth()->guard('admin');
    }

    // Login Page
    public function login()
    {
        if (Auth::guard('admin')->check()) {
            if (session('old_link') != null) {
                return redirect(session('old_link'));
            }

            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    public function submitLogin(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|exists:admins,email',
            'password' => 'required|string',
        ]);

        // return $request;
        if (Auth::guard('admin')->attempt(['email' => $request->email, 'password' => $request->password], $request->get('remember'))) {
            return $this->sendLoginResponse($request);
        }

        return response()->json(['status' => 'error', 'message' => ' Password is incorrect. Please check and try again.'], 401);

    }

    /**
     * Send the response after the admin was authenticated.
     *
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\JsonResponse
     */
    protected function sendLoginResponse(Request $request)
    {
        $request->session()->regenerate();

        if ($response = $this->authenticated($request, $this->guard('admin')->user())) {
            return $response;
        }

        return $request->wantsJson()
            ? new JsonResponse([
                'status'  => 'success',
                'message' => 'Logged in successfully',
                'url'     => $this->redirectPath(),
            ], 200)
            : redirect()->intended($this->redirectPath());
    }

    /**
     * The user has been authenticated.
     *
     * @param  mixed  $user
     * @return mixed
     */
    protected function authenticated(Request $request, $user)
    {
        return $request->wantsJson()
            ? new JsonResponse([
                'status'  => 'success',
                'message' => 'Logged in successfully',
                'url'     => $this->redirectPath(),
            ], 200)
            : redirect()->intended($this->redirectPath());

    }

    // redirect path
    public function redirectPath()
    {
        $url = route('admin.dashboard');

        if (session('old_link') != null) {
            $url = (session('old_link'));
        }

        return $url;
    }

    // Logout admin
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        if (session('old_link') != null) {
            session()->forget('old_link');
        }

        return to_route('admin.login');
    }
}
