<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\OAuthController;

Route::get('/', function () {
    return ['Laravel' => app()->version()];
});

Route::get('/auth/redirect/{provider}', [OAuthController::class, 'redirect'])->name('oauth.redirect');
Route::get('/auth/callback/{provider}', [OAuthController::class, 'callback'])->name('oauth.callback');


require __DIR__.'/auth.php';
