<?php

use App\Http\Controllers\InternshipReviewController;
use App\Http\Controllers\InternshipTemplateController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'verified'])->prefix('internships')->group(function () {
    // 1. Pengajuan Magang
    Route::get('submissions', [InternshipReviewController::class, 'index'])->name('internships.submissions.index');
    Route::get('submissions/{submission}', [InternshipReviewController::class, 'show'])->name('internships.submissions.show');
    Route::post('submissions/{submission}/approve', [InternshipReviewController::class, 'approve'])->name('internships.submissions.approve');
    Route::post('submissions/{submission}/reject', [InternshipReviewController::class, 'reject'])->name('internships.submissions.reject');

    // 2. Persiapan Magang
    Route::get('preparations', [InternshipReviewController::class, 'readyIndex'])->name('internships.preparations.index');
    Route::post('submissions/{submission}/mark-applying', [InternshipReviewController::class, 'markApplying'])->name('internships.submissions.mark-applying');
    Route::post('submissions/{submission}/company-decision', [InternshipReviewController::class, 'companyDecision'])->name('internships.submissions.company-decision');

    // 3. Manajemen Magang & Detail Kelompok
    Route::get('groups', [InternshipReviewController::class, 'groupsIndex'])->name('internships.groups.index');
    Route::get('groups/{group:code}', [InternshipReviewController::class, 'groupDetail'])->name('internships.groups.show');
    Route::post('groups/{group:code}/kick', [InternshipReviewController::class, 'adminKickMember'])->name('internships.groups.kick');
    Route::post('groups/{group:code}/change-leader', [InternshipReviewController::class, 'adminChangeLeader'])->name('internships.groups.change-leader');
    Route::post('groups/{group:code}/submissions/replace-letter', [InternshipReviewController::class, 'adminReplaceLetter'])->name('internships.groups.replace-letter');
    Route::post('groups/{group:code}/submissions/replace-response', [InternshipReviewController::class, 'adminReplaceResponse'])->name('internships.groups.replace-response');
    Route::post('groups/{group:code}/submissions/update', [InternshipReviewController::class, 'adminUpdateSubmission'])->name('internships.groups.update-submission');
    Route::post('groups/{group:code}/status', [InternshipReviewController::class, 'adminUpdateStatus'])->name('internships.groups.update-status');
    Route::delete('groups/{group:code}/disband', [InternshipReviewController::class, 'adminDisbandGroup'])->name('internships.groups.disband');

    // 4. Kelola Template
    Route::get('templates', [InternshipTemplateController::class, 'index'])->name('internships.templates.index');
    Route::post('templates', [InternshipTemplateController::class, 'store'])->name('internships.templates.store');
    Route::get('templates/raw', [InternshipTemplateController::class, 'rawTemplate'])->name('internships.templates.raw');
    Route::match(['get', 'post'], 'templates/preview', [InternshipTemplateController::class, 'processPreview'])->name('internships.templates.preview');
});
