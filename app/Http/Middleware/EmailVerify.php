<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EmailVerify
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()->email_verify) {
            return response()->json([
                'status'     => 'error',
                'message'    => 'Your email address is not verified. Please verify your email to continue.',
                'error_type' => 'VERIFY_EMAIL',
            ], 403);
        }

        return $next($request);
    }
}
