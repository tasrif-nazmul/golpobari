<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminAuthenticate
{
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('admin');
        $admin = $guard->user();

        if (! $admin || ! $admin->is_active) {
            if ($admin) {
                $guard->logout();
            }

            return $this->toLogin($request);
        }

        // Idle timeout
        $timeout = (int) config('golpobari.admin_idle_timeout', 120) * 60;
        $last = (int) $request->session()->get('admin_last_activity', time());
        if ($timeout > 0 && time() - $last > $timeout) {
            $guard->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return $this->toLogin($request, 'নিরাপত্তার জন্য লগআউট করা হয়েছে। আবার লগইন করুন।');
        }
        $request->session()->put('admin_last_activity', time());

        $response = $next($request);
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Robots-Tag', 'noindex, nofollow');

        return $response;
    }

    private function toLogin(Request $request, ?string $message = null): Response
    {
        if ($request->expectsJson()) {
            abort(401);
        }

        return redirect()->guest(route('admin.login'))->with('status', $message);
    }
}
