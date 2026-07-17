<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', [LandingController::class, 'index'])->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', 'home')->name('dashboard');
    Route::get('home', [HomeController::class, 'index'])->name('home');
});

Route::inertia('deactivated', 'auth/Deactivated')->name('deactivated');

Route::post('/forgot-password', [App\Http\Controllers\Auth\ForgotPasswordController::class, 'store'])
    ->middleware(['guest'])
    ->name('password.email');

require __DIR__.'/settings.php';
require __DIR__.'/users.php';
require __DIR__.'/groups.php';
require __DIR__.'/internship-submissions.php';
