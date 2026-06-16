<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('welcome');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('home', 'Dashboard')->name('home');
});

require __DIR__.'/settings.php';
