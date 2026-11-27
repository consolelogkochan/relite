<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RecoveryCodeController;
use App\Http\Controllers\Auth\RegisterController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

Route::get('/', fn (): Response => Inertia::render('Welcome'))->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->middleware('throttle:6,1');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store']);

    Route::get('/password/reset', [PasswordResetController::class, 'create'])->name('password.reset');
    Route::post('/password/reset', [PasswordResetController::class, 'store']);
});

// 登録の後（ログインしている）と再設定の後（ログインしていない）の両方から来る
Route::get('/recovery-code', [RecoveryCodeController::class, 'show'])->name('recovery-code');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', fn (): Response => Inertia::render('Dashboard'))->name('dashboard');
});
