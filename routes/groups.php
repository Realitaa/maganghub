<?php

use App\Http\Controllers\InternshipGroupController;
use App\Http\Controllers\InternshipSubmissionController;
use Illuminate\Support\Facades\Route;

// Public invite / OG splash page — no auth required so social crawlers can read OG tags
Route::get('join/{code}', [InternshipGroupController::class, 'invite'])->name('groups.invite');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('groups', [InternshipGroupController::class, 'store'])->name('groups.store');
    Route::post('groups/join', [InternshipGroupController::class, 'join'])->name('groups.join');
    Route::get('groups/by-code/{code}', [InternshipGroupController::class, 'showByCode'])->name('groups.by-code');
    Route::post('groups/leave', [InternshipGroupController::class, 'leave'])->name('groups.leave');
    Route::post('groups/{group}/banner', [InternshipGroupController::class, 'updateBanner'])->name('groups.banner.update');
    Route::post('groups/{group}/kick', [InternshipGroupController::class, 'kick'])->name('groups.kick');
    Route::delete('groups/{group}', [InternshipGroupController::class, 'destroy'])->name('groups.destroy');

    Route::post('groups/submissions', [InternshipSubmissionController::class, 'store'])->name('groups.submissions.store');
    Route::post('groups/submissions/submit', [InternshipSubmissionController::class, 'submit'])->name('groups.submissions.submit');
    Route::get('groups/submissions/{submission}/download-letter', [InternshipSubmissionController::class, 'downloadLetter'])->name('groups.submissions.download-letter');
    Route::post('groups/submissions/{submission}/upload-response', [InternshipSubmissionController::class, 'uploadResponse'])->name('groups.submissions.upload-response');

    Route::prefix('groups/join-requests')->name('groups.join-requests.')->group(function () {
        Route::delete('{joinRequest}', [InternshipGroupController::class, 'cancelRequest'])->name('cancel');
        Route::post('{joinRequest}/approve', [InternshipGroupController::class, 'approveRequest'])->name('approve');
        Route::post('{joinRequest}/reject', [InternshipGroupController::class, 'rejectRequest'])->name('reject');
    });
});
