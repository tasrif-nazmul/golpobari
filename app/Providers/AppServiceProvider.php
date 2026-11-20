<?php

namespace App\Providers;

use App\Models\Admin;
use App\Support\Settings;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(Settings::class);
    }

    public function boot(): void
    {
        // Only trust X-Forwarded-* from proxies you name (e.g. Cloudflare), so visitors can't fake their IP.
        if ($proxies = config('golpobari.trusted_proxies')) {
            \Illuminate\Http\Middleware\TrustProxies::at($proxies === '*' ? '*' : array_map('trim', explode(',', $proxies)));
        }
        if ($this->app->isProduction() && str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Paginator::defaultView('components.pagination');

        // Owners manage the business set-up; staff can process orders and pages.
        Gate::define('manage-settings', fn (Admin $admin) => $admin->isOwner());
        Gate::define('manage-catalog', fn (Admin $admin) => $admin->isOwner());
        Gate::define('manage-admins', fn (Admin $admin) => $admin->isOwner());

        RateLimiter::for('admin-login', fn (Request $r) => [
            Limit::perMinute(5)->by(strtolower((string) $r->input('email')).'|'.$r->ip()),
            Limit::perMinute(20)->by($r->ip()),
        ]);
        RateLimiter::for('uploads', fn (Request $r) => Limit::perMinutes(10, 80)->by($r->ip()));
        RateLimiter::for('coupon', fn (Request $r) => Limit::perMinute(15)->by($r->ip()));
        RateLimiter::for('order-submit', fn (Request $r) => [Limit::perMinute(8)->by($r->ip()), Limit::perHour(30)->by($r->ip())]);
        RateLimiter::for('preview', fn (Request $r) => Limit::perMinute(30)->by($r->ip()));
        RateLimiter::for('track', fn (Request $r) => Limit::perMinute(10)->by($r->ip()));
        RateLimiter::for('unlock', fn (Request $r) => Limit::perMinute(6)->by($r->ip().'|'.$r->route('slug')));

        Route::model('media', \App\Models\PageMedia::class);

        View::composer('layouts.admin', function ($view) {
            if (auth('admin')->check()) {
                $view->with('adminBadges', \App\Support\AdminStats::badges());
            }
        });

        View::share('settings', $this->app->make(Settings::class));
    }
}
