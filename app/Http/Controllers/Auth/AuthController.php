<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\StudentRegisterRequest;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $credentials = $request->only('email', 'password');

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors(['email' => 'Invalid credentials.'])->onlyInput('email');
        }

        $user = Auth::user();

        if (! $user->is_active) {
            Auth::logout();

            return back()->withErrors(['email' => 'Your account has been deactivated.']);
        }

        $request->session()->regenerate();

        return redirect()->intended($user->dashboardRoute());
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(StudentRegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'phone' => $request->phone,
            'is_active' => true,
        ]);

        $user->assignRole('student');

        Student::create([
            'user_id' => $user->id,
            'student_id_number' => $request->student_id_number,
            'programme' => $request->programme,
            'phone' => $request->phone,
        ]);

        Auth::login($user);

        return redirect()->route('student.dashboard')->with('success', 'Welcome! Your student account is ready.');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
