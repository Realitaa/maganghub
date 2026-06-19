<?php

use App\Http\Controllers\InternshipGroupController;
use App\Http\Controllers\InternshipSubmissionController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('groups', [InternshipGroupController::class, 'store'])->name('groups.store');
    Route::post('groups/join', [InternshipGroupController::class, 'join'])->name('groups.join');
    Route::post('groups/leave', [InternshipGroupController::class, 'leave'])->name('groups.leave');
    Route::delete('groups/{group}', [InternshipGroupController::class, 'destroy'])->name('groups.destroy');

    Route::post('groups/submissions', [InternshipSubmissionController::class, 'store'])->name('groups.submissions.store');
    Route::post('groups/submissions/submit', [InternshipSubmissionController::class, 'submit'])->name('groups.submissions.submit');

    Route::prefix('groups/join-requests')->name('groups.join-requests.')->group(function () {
        Route::delete('{joinRequest}', [InternshipGroupController::class, 'cancelRequest'])->name('cancel');
        Route::post('{joinRequest}/approve', [InternshipGroupController::class, 'approveRequest'])->name('approve');
        Route::post('{joinRequest}/reject', [InternshipGroupController::class, 'rejectRequest'])->name('reject');
    });
});
