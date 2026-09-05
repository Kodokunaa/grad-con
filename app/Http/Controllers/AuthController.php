<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password as PasswordRule;

final class AuthController extends Controller
{
    public function loginForm(Request $request)
    {
        if (Auth::check()) {
            return redirect($this->destination(Auth::user()));
        }

        return view('auth.login', [
            'error' => $request->session()->get('errors')?->first() ?? '',
            'success' => $request->session()->get('status', ''),
            'force_login' => $request->boolean('force_login'),
        ]);
    }

    public function login(LoginRequest $request)
    {
        $user = User::where('username', $request->validated('username'))->first();
        $valid = $user && password_get_info($user->password)['algo'] !== null && Hash::check($request->validated('password'), $user->password);
        if (! $valid || ! $user->is_active || ($user->role === 'alumni' && $user->status !== 'approved')) {
            return back()->withErrors(['username' => 'Invalid credentials or account not approved.'])->onlyInput('username', 'student_id');
        }
        Auth::login($user);
        $request->session()->regenerate();
        if (Hash::needsRehash($user->password)) {
            $user->password = $request->validated('password');
            $user->save();
        }

        return redirect()->intended($this->destination($user));
    }

    private function destination(User $user): string
    {
        return url(match ($user->role) {
            'admin' => '/admin/dashboard', 'employer' => '/employer/dashboard', 'alumni_officer' => '/alumni_officer/dashboard', default => '/alumni/feed'
        });
    }

    public function registerForm(Request $request)
    {
        return view('auth.register', ['error' => $request->session()->get('errors')?->first() ?? '', 'success' => $request->session()->get('status', ''), 'courseOptions' => config('gradconn.courses'), 'batchOptions' => array_map('strval', range((int) date('Y'), 2000))]);
    }

    public function register(Request $request)
    {
        $data = $request->validate(['fullname' => 'required|string|max:150', 'student_id' => 'required|string|max:100|unique:users,username', 'email' => 'required|email|max:150|unique:users,email', 'course' => ['required', Rule::in(config('gradconn.courses'))], 'batch_year' => 'required|integer|min:2000|max:'.date('Y'), 'password' => ['required', 'string', PasswordRule::defaults(), 'same:confirm_password']]);
        $user = new User;
        $user->fill($data);
        $user->username = $data['student_id'];
        $user->role = 'alumni';
        $user->is_active = false;
        $user->status = 'pending';
        $user->save();
        Cache::forget('feed.mention-users.v1');
        Cache::forget('sidebar.pending-alumni.v1');

        return redirect('/register')->with('status', 'Registration successful. Your account is pending admin approval.');
    }

    public function forgotForm(Request $request)
    {
        return view('auth.forgot', ['error' => $request->session()->get('errors')?->first() ?? '', 'msg' => $request->session()->get('status', '')]);
    }

    public function forgot(Request $request)
    {
        $request->validate(['email' => 'required|email|max:150']);
        Password::sendResetLink($request->only('email'));

        return back()->with('status', 'If that address belongs to an account, a password reset link will be sent.');
    }

    public function resetForm(Request $request)
    {
        return view('auth.reset', ['error' => $request->session()->get('errors')?->first() ?? '', 'msg' => $request->session()->get('status', ''), 'token' => $request->query('token', '')]);
    }

    public function reset(Request $request)
    {
        $request->merge(['token' => $request->input('token', $request->query('token')), 'email' => $request->input('email', $request->query('email')), 'password_confirmation' => $request->input('confirm_password')]);
        $data = $request->validate(['token' => 'required|string', 'email' => 'required|email', 'password' => ['required', 'confirmed', PasswordRule::defaults()]]);
        $status = Password::reset($data, function (User $user, string $password) {
            $user->password = $password;
            $user->remember_token = Str::random(60);
            $user->save();
        });

        return $status === Password::PasswordReset ? redirect('/')->with('status', 'Password reset successful. Please sign in.') : back()->withErrors(['email' => __($status)]);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
