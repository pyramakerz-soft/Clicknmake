<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectUserBasedOnRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user->hasRole('Observer')) {
            return redirect()->route('observer.dashboard');
        } elseif ($user->hasRole('Teacher')) {
            return redirect()->route('teacher.dashboard');
        }

        return redirect()->route('home');
    }
}
