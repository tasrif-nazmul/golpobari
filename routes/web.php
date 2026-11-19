<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InfoController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\TrackOrderController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/categories', [CatalogController::class, 'categories'])->name('categories.index');
Route::get('/categories/{category:slug}', [CatalogController::class, 'category'])->name('categories.show');
Route::get('/templates', [CatalogController::class, 'templates'])->name('templates.index');
Route::get('/templates/{template:slug}', [CatalogController::class, 'template'])->name('templates.show');
Route::get('/templates/{template:slug}/render', [CatalogController::class, 'render'])->name('templates.render');

Route::get('/pricing', [InfoController::class, 'pricing'])->name('pricing');
Route::get('/faq', [InfoController::class, 'faq'])->name('faq');
Route::get('/contact', [InfoController::class, 'contact'])->name('contact');
Route::get('/about', [InfoController::class, 'content'])->defaults('slug', 'about')->name('about');
Route::get('/privacy', [InfoController::class, 'content'])->defaults('slug', 'privacy')->name('privacy');
Route::get('/terms', [InfoController::class, 'content'])->defaults('slug', 'terms')->name('terms');
Route::get('/refund-policy', [InfoController::class, 'content'])->defaults('slug', 'refund-policy')->name('refund');

Route::prefix('order')->name('order.')->group(function () {
    Route::get('/', [OrderController::class, 'create'])->name('create');
    Route::post('/uploads', [OrderController::class, 'upload'])->middleware('throttle:uploads')->name('upload');
    Route::delete('/uploads/{media}', [OrderController::class, 'deleteUpload'])->whereNumber('media')->name('upload.delete');
    Route::post('/coupon', [OrderController::class, 'coupon'])->middleware('throttle:coupon')->name('coupon');
    Route::post('/slug-check', [OrderController::class, 'slugCheck'])->middleware('throttle:coupon')->name('slug');
    Route::post('/preview', [OrderController::class, 'preview'])->middleware('throttle:preview')->name('preview');
    Route::post('/', [OrderController::class, 'store'])->middleware('throttle:order-submit')->name('store');
    Route::get('/thank-you/{order:order_number}', [OrderController::class, 'confirmation'])->name('confirmation');
});

Route::get('/track-order', [TrackOrderController::class, 'form'])->name('track');
Route::post('/track-order', [TrackOrderController::class, 'lookup'])->middleware('throttle:track')->name('track.lookup');
Route::post('/track-order/payment', [TrackOrderController::class, 'resubmitPayment'])->middleware('throttle:track')->name('track.payment');

Route::get('/media/{media}/{variant}', [MediaController::class, 'show'])
    ->whereNumber('media')->whereIn('variant', ['full', 'thumb'])->name('media.show');

Route::get('/sitemap.xml', [SeoController::class, 'sitemap'])->name('sitemap');
Route::get('/robots.txt', [SeoController::class, 'robots'])->name('robots');
