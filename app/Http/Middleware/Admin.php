<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $guard = 'admin'): Response
    {
        if (! \Auth::guard($guard)->check()) {
            session(['old_link' => url()->current()]);

            return to_route('admin.login');
        }

        return $next($request);
    }
}
