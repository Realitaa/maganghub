<?php

use App\Http\Controllers\InternshipReviewController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('submissions', [InternshipReviewController::class, 'index'])->name('operator.submissions.index');
    Route::get('submissions/{submission}', [InternshipReviewController::class, 'show'])->name('operator.submissions.show');
    Route::post('submissions/{submission}/approve', [InternshipReviewController::class, 'approve'])->name('operator.submissions.approve');
    Route::post('submissions/{submission}/reject', [InternshipReviewController::class, 'reject'])->name('operator.submissions.reject');
});
