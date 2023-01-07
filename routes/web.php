<?php

use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\Integrations\ExistController;
use App\Http\Controllers\Integrations\TraktController;
use App\Http\Controllers\Integrations\WhatPulseController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    if (auth()->user() !== null) return redirect()->route('home');
    
    return view('landing', [
        'integrations' => collect(config('services.integrations'))
    ]);
})->name('landing');

Route::get('/dashboard', function () {
    return redirect('home');
})->middleware(['auth'])->name('dashboard');

// Integration Routes (Home Status, Add)
Route::get('/home', [IntegrationController::class, 'home'])->name('home');
Route::get('/add', [IntegrationController::class, 'add'])->name('add');
Route::get('/logs', [IntegrationController::class, 'logs'])->name('logs');

require __DIR__.'/auth.php';

// Exist Routes
Route::get('/services/exist/connect', [ExistController::class, 'connect'])->name('exist.connect');
Route::get('/services/exist/connected', [ExistController::class, 'connected'])->name('exist.connected');
Route::delete('/services/exist/disconnect', [ExistController::class, 'disconnect'])->name('exist.disconnect');
Route::get('/services/exist/manage', [ExistController::class, 'manage'])->name('exist.manage');
Route::post('/services/exist/updateAccountProfile', [ExistController::class, 'updateAccountProfile'])->name('exist.updateAccountProfile');

// WhatPulse Routes
Route::post('/services/whatpulse/connect', [WhatPulseController::class, 'connect'])->name('whatpulse.connect');
Route::delete('/services/whatpulse/disconnect', [WhatPulseController::class, 'disconnect'])->name('whatpulse.disconnect');
Route::get('/services/whatpulse/manage', [WhatPulseController::class, 'manage'])->name('whatpulse.manage');
Route::post('/services/whatpulse/setAttributes', [WhatPulseController::class, 'setAttributes'])->name('whatpulse.setAttributes');
Route::post('/services/whatpulse/zero', [WhatPulseController::class, 'zero'])->name('whatpulse.zero');

// Trakt Routes
Route::get('/services/trakt/connect', [TraktController::class, 'connect'])->name('trakt.connect');
Route::get('/services/trakt/connected', [TraktController::class, 'connected'])->name('trakt.connected');
Route::delete('/services/trakt/disconnect', [TraktController::class, 'disconnect'])->name('trakt.disconnect');
Route::get('/services/trakt/manage', [TraktController::class, 'manage'])->name('trakt.manage');
Route::post('/services/trakt/setAttributes', [TraktController::class, 'setAttributes'])->name('trakt.setAttributes');
Route::post('/services/trakt/zero', [TraktController::class, 'zero'])->name('trakt.zero');

// Static Routes
Route::get('/privacy', function() {
    return view('static.privacypolicy');
})->name('privacypolicy');

Route::get('/integrations', function() {
    return view('static.integrations');
})->name('integrations');

Route::get('/about', function () {
    return view('landing', [
        'integrations' => collect(config('services.integrations'))
    ]);
})->name('about');