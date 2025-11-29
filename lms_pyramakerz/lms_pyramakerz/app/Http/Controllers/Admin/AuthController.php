<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;


class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('admin.auth.login');
    }
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $email = Str::lower($request->input('email'));
        $ip = $request->ip();
        $key = 'login-attempts:' . $email . '|' . $ip;

        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            session()->flash('blocked_until', now()->addSeconds($seconds)->timestamp);
            return back()->withErrors([
                'email' => "Too many login attempts. Please try again in {$seconds} seconds.",
            ]);
        }

        $admin = Admin::where('email', $email)->first();
        if ($admin && Hash::check($request->input('password'), $admin->password)) {
            RateLimiter::clear($key);
            Auth::guard('admin')->login($admin);
            return redirect()->route('admin.dashboard');
        }

        $user = User::where('email', $email)->first();
        if ($user && Hash::check($request->input('password'), $user->password)) {
            RateLimiter::clear($key);
            Auth::guard('web')->login($user);
            return redirect()->route('admin.dashboard');
        }


        RateLimiter::hit($key, 180);

        return back()->withErrors(['email' => 'The provided credentials are incorrect.']);
    }


    public function logout()
    {
        Auth::guard('admin')->logout();
        return redirect()->route('admin.login')->with('success', 'Logged out successfully.');
    }
}
