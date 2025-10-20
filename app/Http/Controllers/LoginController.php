<?php

namespace App\Http\Controllers;

use App\Models\Observer;
use App\Models\Student;
use App\Models\Teacher;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Handle the login logic for both students and teachers
    // public function login(Request $request)
    // {
    //     $request->validate([
    //         'username' => 'required',
    //         'password' => 'required',
    //         'login_as' => 'required|in:teacher,observer',
    //     ]);





    //     // Attempt to authenticate as a student
    //     $student = Student::where('username', $request->input('username'))->where('plain_password', $request->input('password'))->first();
    //     if ($student) {
    //         if (Hash::check($request->input('password'), $student->password)) {
    //             $student->increment('num_logins');
    //             Auth::guard('student')->login($student); // Log in the student
    //             return redirect()->route('student.dashboard');
    //         } else {
    //             // Password is incorrect
    //             return back()->withErrors([
    //                 'password' => 'The provided Password is incorrect.',
    //             ]);
    //         }
    //     }
    //     // Attempt to authenticate as a teacher
    //     $teacher = Teacher::where('username', $request->input('username'))->where('plain_password', $request->input('password'))->first();
    //     if ($teacher) {
    //         if (Hash::check($request->input('password'), $teacher->password)) {
    //             $teacher->increment('num_logins');
    //             Auth::guard('teacher')->login($teacher); // Log in the teacher
    //             return redirect()->route('teacher.dashboard');
    //         } else {
    //             // Password is incorrect
    //             return back()->withErrors([
    //                 'password' => 'The provided Password is incorrect.',
    //             ]);
    //         }
    //     }

    //     $user = User::where('username', $request->input('username'))->first();
    //     if ($user) {
    //         if ($user && Hash::check($request->input('password'), $user->password)) {
    //             $role = $request->input('login_as');

    //             if ($user->hasRole($role)) {
    //                 Auth::guard($role)->login($user);
    //                 return redirect()->route("$role.dashboard");
    //             } else {
    //                 return back()->withErrors([
    //                     'credentials' => "User does not have the '$role' role.",
    //                 ]);
    //             }
    //         }
    //     }
    //     $observer = Observer::where('username', $request->input('username'))->first();
    //     if ($observer) {
    //         if (Hash::check($request->input('password'), $observer->password)) {
    //             // $observer->increment('num_logins');
    //             Auth::guard('observer')->login($observer);
    //             return redirect()->route('observer.dashboard');
    //         } else {
    //             // Password is incorrect
    //             return back()->withErrors([
    //                 'password' => 'The provided Password is incorrect.',
    //             ]);
    //         }
    //     }
    //     return back()->withErrors([
    //         'username' => 'The provided Username is incorrect.',
    //     ]);


    // }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required',
            'password' => 'required',
            'login_as' => 'required|in:teacher,observer,student',
        ]);

        $role = $request->input('login_as');
        $username = $request->input('username');
        $password = $request->input('password');

        // 1. Try from users table (based on name or email)
        $user = User::where('username', $username)
            ->orWhere('email', operator: $username)
            ->first();

        if ($user && Hash::check($password, $user->password)) {
            if ($user->hasRole($role)) {
                Auth::guard($role)->login($user);
                return redirect()->route("$role.dashboard");
            } else {
                return back()->withErrors(['credentials' => "This user does not have the '$role' role."]);
            }
        }

        // 2. Try from teachers table
        if ($role === 'teacher') {
            $teacher = Teacher::where('username', $username)->orWhere('username', $username)->first();
            if ($teacher && Hash::check($password, $teacher->password)) {
                Auth::guard('teacher')->login($teacher);
                $teacher->increment('num_logins');
                return redirect()->route('teacher.dashboard');
            }
        }

        // 3. Try from observers table
        if ($role === 'observer') {
            $observer = Observer::where('username', $username)->orWhere('name', $username)->first();
            if ($observer && Hash::check($password, $observer->password)) {
                $observer->increment('num_logins');
                Auth::guard('observer')->login($observer);
                return redirect()->route('observer.dashboard');
            }
        }

        // 4. Try from students table
        if ($role === 'student') {
            $student = Student::where('username', $username)->orWhere('username', $username)->first();
            if ($student && Hash::check($password, $student->password)) {
                $student->increment('num_logins');
                Auth::guard('student')->login($student);
                return redirect()->route('student.dashboard');
            }
        }

        return back()->withErrors(['username' => 'Invalid Username or Password']);
    }



    public function logout(Request $request)
    {
        Auth::guard()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
