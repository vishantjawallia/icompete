<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // Ensure the user is authenticated
        if (! $user) {
            return response()->json([
                'status'  => 'error',
                'message' => 'You are not authorized to access this route.',
            ], 401);
        }

        // Check if user role matches any of the required roles
        if (! in_array($user->role, $roles)) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Access Denied. You are not authorised.',
            ], 403);
        }

        return $next($request);
    }
}
