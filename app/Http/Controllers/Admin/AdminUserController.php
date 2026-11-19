<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Support\ActivityLogger;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AdminUserController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [new Middleware('can:manage-admins')];
    }

    public function index()
    {
        return view('admin.admins.index', ['admins' => Admin::orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.admins.form', ['item' => new Admin(['role' => 'staff', 'is_active' => true])]);
    }

    public function store(Request $request)
    {
        $d = $this->validated($request);
        $a = Admin::create($d);
        ActivityLogger::log('admin.created', $a, $a->email.' ('.$a->role.')');

        return redirect()->route('admin.admins.index')->with('success', 'Admin added.');
    }

    public function edit(Admin $admin)
    {
        return view('admin.admins.form', ['item' => $admin]);
    }

    public function update(Request $request, Admin $admin)
    {
        $d = $this->validated($request, $admin);
        if ($admin->id === auth('admin')->id() && ($d['role'] !== 'owner' || ! $d['is_active'])) {
            return back()->with('error', 'You cannot demote or deactivate yourself.');
        }
        if (empty($d['password'])) {
            unset($d['password']);
        }
        $admin->update($d);
        ActivityLogger::log('admin.updated', $admin, $admin->email);

        return redirect()->route('admin.admins.index')->with('success', 'Admin saved.');
    }

    public function destroy(Admin $admin)
    {
        if ($admin->id === auth('admin')->id()) {
            return back()->with('error', 'You cannot delete yourself.');
        }
        if ($admin->isOwner() && Admin::where('role', 'owner')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'Keep at least one active owner.');
        }
        ActivityLogger::log('admin.deleted', $admin, $admin->email);
        $admin->delete();

        return back()->with('success', 'Admin removed.');
    }

    private function validated(Request $request, ?Admin $admin = null): array
    {
        $request->merge(['email' => strtolower(trim((string) $request->input('email')))]);
        $d = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'email' => ['required', 'email', 'max:120', Rule::unique('admins', 'email')->ignore($admin?->id)],
            'role' => ['required', Rule::in(['owner', 'staff'])],
            'password' => [$admin ? 'nullable' : 'required', 'confirmed', Password::min(12)->letters()->mixedCase()->numbers()],
        ]);
        $d['is_active'] = $request->boolean('is_active');

        return $d;
    }
}
