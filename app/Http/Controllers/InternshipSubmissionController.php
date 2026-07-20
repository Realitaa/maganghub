<?php

namespace App\Http\Controllers;

use App\Http\Requests\Submissions\UploadResponseLetterRequest;
use App\Models\InternshipSubmission;
use App\Services\GroupTimelineService;
use App\Services\InternshipSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class InternshipSubmissionController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        public InternshipSubmissionService $submissionService,
        public GroupTimelineService $timelineService
    ) {}

    /**
     * Save the internship submission as a draft.
     */
    public function store(Request $request): RedirectResponse
    {
        try {
            $this->submissionService->saveDraft(auth()->user(), $request->all());

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Draf pengajuan magang berhasil disimpan.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    /**
     * Submit the internship proposal to admin.
     */
    public function submit(Request $request): RedirectResponse
    {
        try {
            $this->submissionService->submitProposal(auth()->user(), $request->all());

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pengajuan magang berhasil dikirim untuk ditinjau.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    public function downloadLetter(InternshipSubmission $submission)
    {
        Gate::authorize('downloadLetter', $submission);

        if (! $submission->letter_path || ! Storage::exists($submission->letter_path)) {
            abort(404, 'Berkas surat permohonan magang tidak ditemukan.');
        }

        $absolutePath = Storage::path($submission->letter_path);

        $safeCompanyName = str_replace([' ', '/', '\\'], '_', $submission->company_name);
        $filename = 'surat_permohonan_magang_'.$safeCompanyName.'_'.($submission->group->code ?? $submission->id).'.docx';

        return response()->download($absolutePath, $filename);
    }

    /**
     * Upload the company response letter.
     */
    public function uploadResponse(UploadResponseLetterRequest $request, InternshipSubmission $submission): RedirectResponse
    {
        Gate::authorize('uploadResponse', $submission);

        if ($submission->company_response_path) {
            Storage::delete($submission->company_response_path);
        }

        $path = $request->file('file')->store('company_responses');

        $submission->update([
            'company_response_path' => $path,
            'status' => 'loa_review',
        ]);

        $submission->group->update([
            'status' => 'loa_review',
        ]);

        $this->timelineService->companyReplyUploaded($submission->group);

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Surat balasan perusahaan berhasil diunggah.',
        ])->back();
    }
}
