<?php

use App\Http\Controllers\CustomerPageController;
use Illuminate\Support\Facades\Route;

// golpobari.com/{slug}: must stay the last route registered.
Route::get('/{slug}', [CustomerPageController::class, 'show'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->name('page.show');
Route::post('/{slug}/unlock', [CustomerPageController::class, 'unlock'])
    ->where('slug', '[a-z0-9]+(?:-[a-z0-9]+)*')->middleware('throttle:unlock')->name('page.unlock');
