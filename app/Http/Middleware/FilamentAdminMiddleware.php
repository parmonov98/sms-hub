<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class FilamentAdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip admin check for login page
        if ($request->routeIs('filament.admin.auth.login') || $request->routeIs('filament.admin.auth.logout')) {
            return $next($request);
        }

        if (!Auth::check()) {
            return redirect()->route('filament.admin.auth.login');
        }

        if (!Auth::user()->isAdmin()) {
            Auth::logout();
            return redirect()->route('filament.admin.auth.login')
                ->with('error', 'You must be an administrator to access this area.');
        }

        return $next($request);
    }
}
