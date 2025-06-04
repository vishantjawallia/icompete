<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function testHome(Request $request)
    {
        $user = \App\Models\User::first();
        $user->gender += 1;
        $user->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Request received Time',
            'time'    => now()->format('Y-m-d H:i:s:v'),
            'balance' => $user->gender,
        ], 200);
    }

    public function paymentSuccess(Request $request)
    {
        $message = $request->session()->get('message');

        return view('payment.success', ['message' => $message]);
    }

    public function paymentError(Request $request)
    {
        $message = $request->session()->get('message');

        return view('payment.error', ['message' => $message]);
    }
}
