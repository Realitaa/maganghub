<?php

use App\Http\Controllers\InternshipReviewController;
use App\Http\Controllers\InternshipTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('operator')->group(function () {
    Route::get('submissions', [InternshipReviewController::class, 'index'])->name('operator.submissions.index');
    Route::get('submissions/{submission}', [InternshipReviewController::class, 'show'])->name('operator.submissions.show');
    Route::post('submissions/{submission}/approve', [InternshipReviewController::class, 'approve'])->name('operator.submissions.approve');
    Route::post('submissions/{submission}/reject', [InternshipReviewController::class, 'reject'])->name('operator.submissions.reject');

    // Template management
    Route::get('templates', [InternshipTemplateController::class, 'index'])->name('operator.templates.index');
    Route::post('templates', [InternshipTemplateController::class, 'store'])->name('operator.templates.store');
});
