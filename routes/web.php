<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\GoogleAuthController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/login');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login');

    Route::get('/registro', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/registro', [AuthController::class, 'register'])->middleware('throttle:register');

    Route::get('/olvide-mi-contrasena', [ForgotPasswordController::class, 'showLinkRequestForm'])->name('password.request');
    Route::post('/olvide-mi-contrasena', [ForgotPasswordController::class, 'sendResetLinkEmail'])->middleware('throttle:recovery')->name('password.email');
    Route::get('/restablecer-contrasena/{token}', [ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/restablecer-contrasena', [ForgotPasswordController::class, 'resetPassword'])->middleware('throttle:recovery')->name('password.update');

    Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->middleware('throttle:oauth')->name('google.redirect');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [AuthController::class, 'dashboard'])->name('dashboard');
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::post('/auth/google/vincular', [GoogleAuthController::class, 'link'])->middleware('throttle:oauth')->name('google.link');
});

Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->middleware('throttle:oauth')->name('google.callback');
