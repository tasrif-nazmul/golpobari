<?php

use App\Http\Controllers\Admin;
use Illuminate\Support\Facades\Route;

Route::middleware('guest:admin')->group(function () {
    Route::get('/login', [Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [Admin\AuthController::class, 'login'])->middleware('throttle:admin-login')->name('login.attempt');
});

Route::middleware('admin')->group(function () {
    Route::post('/logout', [Admin\AuthController::class, 'logout'])->name('logout');
    Route::get('/', Admin\DashboardController::class)->name('dashboard');

    // Orders & payments
    Route::get('/orders', [Admin\OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [Admin\OrderController::class, 'show'])->name('orders.show');
    Route::put('/orders/{order}', [Admin\OrderController::class, 'update'])->name('orders.update');
    Route::put('/orders/{order}/content', [Admin\OrderController::class, 'updateContent'])->name('orders.content');
    Route::post('/orders/{order}/status', [Admin\OrderController::class, 'status'])->name('orders.status');
    Route::post('/orders/{order}/notes', [Admin\OrderController::class, 'notes'])->name('orders.notes');
    Route::post('/orders/{order}/media', [Admin\OrderController::class, 'addMedia'])->name('orders.media.store');
    Route::get('/orders/{order}/media/{media}/download', [Admin\OrderController::class, 'downloadMedia'])->name('orders.media.download');
    Route::delete('/orders/{order}/media/{media}', [Admin\OrderController::class, 'deleteMedia'])->name('orders.media.destroy');
    Route::get('/orders/{order}/media.zip', [Admin\OrderController::class, 'downloadAll'])->name('orders.media.zip');
    Route::post('/orders/{order}/page', [Admin\PageController::class, 'storeFromOrder'])->name('orders.page');

    Route::get('/payments', [Admin\PaymentController::class, 'index'])->name('payments.index');
    Route::post('/payments/{payment}/verify', [Admin\PaymentController::class, 'verify'])->name('payments.verify');
    Route::post('/payments/{payment}/reject', [Admin\PaymentController::class, 'reject'])->name('payments.reject');
    Route::post('/payments/{payment}/refund', [Admin\PaymentController::class, 'refund'])->name('payments.refund');

    // Customer pages
    Route::get('/pages', [Admin\PageController::class, 'index'])->name('pages.index');
    Route::get('/pages/{page}/edit', [Admin\PageController::class, 'edit'])->name('pages.edit');
    Route::put('/pages/{page}', [Admin\PageController::class, 'update'])->name('pages.update');
    Route::get('/pages/{page}/preview', [Admin\PageController::class, 'preview'])->name('pages.preview');
    Route::post('/pages/{page}/action', [Admin\PageController::class, 'action'])->name('pages.action');
    Route::post('/pages/{page}/renew', [Admin\PageController::class, 'renew'])->name('pages.renew');
    Route::post('/pages/{page}/duplicate', [Admin\PageController::class, 'duplicate'])->name('pages.duplicate');
    Route::post('/pages/{page}/media', [Admin\PageController::class, 'addMedia'])->name('pages.media.store');
    Route::put('/pages/{page}/media', [Admin\PageController::class, 'updateMedia'])->name('pages.media.update');
    Route::delete('/pages/{page}', [Admin\PageController::class, 'destroy'])->name('pages.destroy');

    // Catalogue & content (owner only, enforced in controllers)
    Route::resource('categories', Admin\CategoryController::class)->except('show');
    Route::resource('templates', Admin\TemplateController::class)->except('show');
    Route::resource('packages', Admin\PackageController::class)->except('show');
    Route::resource('coupons', Admin\CouponController::class);
    Route::resource('testimonials', Admin\TestimonialController::class)->except('show');
    Route::resource('faqs', Admin\FaqController::class)->except('show');
    Route::get('/content-pages', [Admin\ContentPageController::class, 'index'])->name('content-pages.index');
    Route::get('/content-pages/{contentPage}/edit', [Admin\ContentPageController::class, 'edit'])->name('content-pages.edit');
    Route::put('/content-pages/{contentPage}', [Admin\ContentPageController::class, 'update'])->name('content-pages.update');
    Route::post('/reorder/{type}', Admin\ReorderController::class)->name('reorder');

    Route::get('/settings/{group?}', [Admin\SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings/{group}', [Admin\SettingController::class, 'update'])->name('settings.update');

    Route::get('/activity', [Admin\ActivityLogController::class, 'index'])->name('activity.index');

    Route::resource('admins', Admin\AdminUserController::class)->except('show');
    Route::get('/account', [Admin\AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [Admin\AccountController::class, 'update'])->name('account.update');
});
