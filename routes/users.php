<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth'])->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::post('users', [UserController::class, 'store'])->name('users.store');
    Route::patch('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');
    Route::post('users/{user}/reset-password-to-nim', [UserController::class, 'resetPasswordToNim'])->name('users.reset-password-to-nim');
    Route::get('users/import-template', [UserController::class, 'downloadTemplate'])->name('users.import-template');
    Route::post('users/import', [UserController::class, 'import'])->name('users.import');
});
