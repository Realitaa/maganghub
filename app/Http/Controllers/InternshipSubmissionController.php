<?php

namespace App\Http\Controllers;

use App\Http\Requests\Submissions\UploadResponseLetterRequest;
use App\Models\InternshipSubmission;
use App\Models\User;
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

    public function downloadLetter(Request $request, InternshipSubmission $submission)
    {
        Gate::authorize('downloadLetter', $submission);

        $userId = $request->integer('user_id');

        if ($userId) {
            $membership = $submission->submissionMemberships()
                ->where('user_id', $userId)
                ->first();

            if (! $membership) {
                abort(403, 'Mahasiswa bukan merupakan anggota kelompok pengajuan ini.');
            }

            if (! $membership->letter_path || ! Storage::exists($membership->letter_path)) {
                abort(404, 'Berkas surat permohonan magang mahasiswa tidak ditemukan.');
            }

            $user = User::findOrFail($userId);
            $absolutePath = Storage::path($membership->letter_path);

            $safeName = str_replace([' ', '/', '\\'], '_', $user->name);
            $filename = 'surat_permohonan_magang_'.$safeName.'_'.($submission->group->code ?? $submission->id).'.docx';

            return response()->download($absolutePath, $filename);
        }

        // Bulk download all letters in a ZIP
        $memberships = $submission->submissionMemberships()->with('user')->get();
        if ($memberships->isEmpty()) {
            abort(404, 'Tidak ada anggota kelompok.');
        }

        $zip = new \ZipArchive;
        $zipPath = tempnam(sys_get_temp_dir(), 'zip_');
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Gagal membuat file ZIP.');
        }

        $hasFiles = false;
        foreach ($memberships as $membership) {
            if ($membership->letter_path && Storage::exists($membership->letter_path)) {
                $user = $membership->user;
                $absolutePath = Storage::path($membership->letter_path);
                $safeName = str_replace([' ', '/', '\\'], '_', $user->name);
                $filename = 'surat_permohonan_magang_'.$safeName.'_'.($submission->group->code ?? $submission->id).'.docx';
                $zip->addFile($absolutePath, $filename);
                $hasFiles = true;
            }
        }

        $zip->close();

        if (! $hasFiles) {
            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }
            abort(404, 'Tidak ada berkas surat permohonan magang yang ditemukan untuk kelompok ini.');
        }

        $zipName = 'surat_permohonan_magang_'.($submission->group->code ?? $submission->id).'.zip';

        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
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
