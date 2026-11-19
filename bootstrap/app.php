<?php

use App\Http\Middleware\AdminAuthenticate;
use App\Http\Middleware\SecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware('web')
                ->prefix(config('golpobari.admin_path'))
                ->name('admin.')
                ->group(base_path('routes/admin.php'));

            // Customer pages catch the remaining single-segment URLs, so they load last.
            Route::middleware('web')->group(base_path('routes/pages.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);
        $middleware->alias(['admin' => AdminAuthenticate::class]);
        $middleware->redirectGuestsTo(fn () => route('admin.login'));
        $middleware->redirectUsersTo(fn () => route('admin.dashboard'));
        // Check admin auth before route-model binding so guests can't probe IDs.
        $middleware->prependToPriorityList(before: \Illuminate\Routing\Middleware\SubstituteBindings::class, prepend: AdminAuthenticate::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->dontFlash(['password', 'password_confirmation', 'current_password', 'access_code', 'payment_trx']);
    })->create();
