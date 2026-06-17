<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('home', 'Dashboard')->name('home');
});

Route::inertia('deactivated', 'auth/Deactivated')->name('deactivated');

require __DIR__.'/settings.php';
require __DIR__.'/users.php';
