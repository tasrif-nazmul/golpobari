<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function showLogin()
    {
        return view('admin.auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:120'],
            'password' => ['required', 'string', 'max:200'],
        ]);
        $credentials['email'] = strtolower($credentials['email']);

        if (! Auth::guard('admin')->attempt($credentials + ['is_active' => true], $request->boolean('remember'))) {
            // Same message whether the email exists or not.
            throw ValidationException::withMessages(['email' => 'These credentials do not match our records.']);
        }

        $request->session()->regenerate();
        $request->session()->put('admin_last_activity', time());

        $admin = Auth::guard('admin')->user();
        $admin->forceFill(['last_login_at' => now(), 'last_login_ip' => $request->ip()])->save();
        ActivityLogger::log('admin.login', $admin, $admin->email);

        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        ActivityLogger::log('admin.logout', auth('admin')->user());
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }
}
