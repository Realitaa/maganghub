<?php

use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('home', [DashboardController::class, 'index'])->name('home');
});

Route::inertia('deactivated', 'auth/Deactivated')->name('deactivated');

require __DIR__.'/settings.php';
require __DIR__.'/users.php';
require __DIR__.'/groups.php';
