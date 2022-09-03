<?php

use App\Http\Controllers\IntegrationController;
use App\Http\Controllers\Integrations\ExistController;
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
    
    return view('landing');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Integration Routes (Home Status, Add)
Route::get('/home', [IntegrationController::class, 'home'])->name('home');
Route::get('/add', [IntegrationController::class, 'add'])->name('add');

require __DIR__.'/auth.php';

// test route
Route::get('/services/whatpulse/test', [WhatPulseController::class, 'test']);

// Exist Routes
Route::get('/services/exist/connect', [ExistController:: class, 'connect'])->name('exist.connect');
Route::get('/services/exist/connected', [ExistController::class, 'connected'])->name('exist.connected');
Route::delete('/services/exist/disconnect', [ExistController::class, 'disconnect'])->name('exist.disconnect');
Route::get('/services/exist/manage', [ExistController::class, 'manage'])->name('exist.manage');
Route::post('/services/exist/updateAccountProfile', [ExistController::class, 'updateAccountProfile'])->name('exist.updateAccountProfile');