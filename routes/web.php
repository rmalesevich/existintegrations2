<?php

use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\Integrations\ExistController;
use App\Http\Controllers\Integrations\TraktController;
use App\Http\Controllers\Integrations\WhatPulseController;
use App\Http\Controllers\Integrations\YnabController;
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
    
    return view('static.landing', [
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
Route::get('/integrations/whatpulse', function() {
    return view('static.integrationswhatpulse');
})->name('integrations.whatpulse');

// Trakt Routes
Route::get('/services/trakt/connect', [TraktController::class, 'connect'])->name('trakt.connect');
Route::get('/services/trakt/connected', [TraktController::class, 'connected'])->name('trakt.connected');
Route::delete('/services/trakt/disconnect', [TraktController::class, 'disconnect'])->name('trakt.disconnect');
Route::get('/services/trakt/manage', [TraktController::class, 'manage'])->name('trakt.manage');
Route::post('/services/trakt/setAttributes', [TraktController::class, 'setAttributes'])->name('trakt.setAttributes');
Route::post('/services/trakt/zero', [TraktController::class, 'zero'])->name('trakt.zero');
Route::get('/integrations/trakt', function() {
    return view('static.integrationstrakt');
})->name('integrations.trakt');

// YNAB Routes
Route::get('/services/ynab/connect', [YnabController::class, 'connect'])->name('ynab.connect');
Route::get('/services/ynab/connected', [YnabController::class, 'connected'])->name('ynab.connected');
Route::delete('/services/ynab/disconnect', [YnabController::class, 'disconnect'])->name('ynab.disconnect');
Route::get('/services/ynab/manage', [YnabController::class, 'manage'])->name('ynab.manage');
Route::post('/services/ynab/setAttributes', [YnabController::class, 'setAttributes'])->name('ynab.setAttributes');
Route::post('/services/ynab/zero', [YnabController::class, 'zero'])->name('ynab.zero');
Route::get('/integrations/ynab', function() {
    return view('static.integrationsynab');
})->name('integrations.ynab');

// Generic Static Routes
Route::get('/privacy', function() {
    return view('static.privacypolicy');
})->name('privacypolicy');

Route::get('/integrations', function() {
    return view('static.integrations');
})->name('integrations');

Route::get('/about', function () {
    return view('static.landing', [
        'integrations' => collect(config('services.integrations'))
    ]);
})->name('about');