<?php

use App\Http\Controllers\InternshipReviewController;
use App\Http\Controllers\InternshipTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('review')->group(function () {
    Route::get('submissions', [InternshipReviewController::class, 'index'])->name('review.submissions.index');
    Route::get('submissions/{submission}', [InternshipReviewController::class, 'show'])->name('review.submissions.show');
    Route::post('submissions/{submission}/approve', [InternshipReviewController::class, 'approve'])->name('review.submissions.approve');
    Route::post('submissions/{submission}/reject', [InternshipReviewController::class, 'reject'])->name('review.submissions.reject');

    // New Ready for Internship (Siap Magang) routes
    Route::get('ready', [InternshipReviewController::class, 'readyIndex'])->name('review.ready.index');
    Route::post('submissions/{submission}/mark-applying', [InternshipReviewController::class, 'markApplying'])->name('review.submissions.mark-applying');
    Route::post('submissions/{submission}/company-decision', [InternshipReviewController::class, 'companyDecision'])->name('review.submissions.company-decision');

    // New Internship Groups (Kelompok Magang) route
    Route::get('groups', [InternshipReviewController::class, 'groupsIndex'])->name('review.groups.index');

    // Template management
    Route::get('templates', [InternshipTemplateController::class, 'index'])->name('review.templates.index');
    Route::post('templates', [InternshipTemplateController::class, 'store'])->name('review.templates.store');
});
