<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function edit()
    {
        return view('admin.account', ['admin' => auth('admin')->user()]);
    }

    public function update(Request $request)
    {
        $admin = auth('admin')->user();
        $d = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'current_password' => ['required', 'current_password:admin'],
            'password' => ['nullable', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);
        $admin->name = $d['name'];
        $admin->save();
        if (! empty($d['password'])) {
            // Re-hashes and saves the new password, and signs out other sessions.
            Auth::guard('admin')->logoutOtherDevices($d['password']);
        }
        ActivityLogger::log('admin.account_updated', $admin, ! empty($d['password']) ? 'Password changed' : 'Profile updated');

        return back()->with('success', 'Account updated.');
    }
}
