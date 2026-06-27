<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::redirect('dashboard', 'home')->name('dashboard');
    Route::get('home', [HomeController::class, 'index'])->name('home');
});

Route::inertia('deactivated', 'auth/Deactivated')->name('deactivated');

require __DIR__.'/settings.php';
require __DIR__.'/users.php';
require __DIR__.'/groups.php';
require __DIR__.'/internship-submissions.php';
