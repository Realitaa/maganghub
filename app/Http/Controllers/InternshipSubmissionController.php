<?php

namespace App\Http\Controllers;

use App\Models\InternshipSubmission;
use App\Models\User;
use App\Services\DocumentGeneratorService;
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
    public function __construct(public InternshipSubmissionService $submissionService) {}

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

    /**
     * Download the generated internship application letter.
     */
    public function downloadLetter(Request $request, InternshipSubmission $submission)
    {
        Gate::authorize('downloadLetter', $submission);

        $userId = $request->integer('user_id');

        if ($userId) {
            $hasMember = $submission->submissionMemberships()->where('user_id', $userId)->exists();
            if (! $hasMember) {
                abort(403, 'Mahasiswa bukan merupakan anggota kelompok pengajuan ini.');
            }

            $user = User::findOrFail($userId);

            try {
                $generator = app(DocumentGeneratorService::class);
                $path = $generator->generateLetter($submission, $userId);
                $absolutePath = Storage::path($path);

                $safeName = str_replace([' ', '/', '\\'], '_', $user->name);
                $filename = 'surat_permohonan_magang_'.$safeName.'_'.($submission->group->code ?? $submission->id).'.docx';

                return response()->download($absolutePath, $filename)->deleteFileAfterSend(true);
            } catch (\Exception $e) {
                abort(500, 'Gagal membuat dokumen permohonan magang: '.$e->getMessage());
            }
        }

        if (! $submission->letter_path || ! Storage::exists($submission->letter_path)) {
            abort(404, 'Berkas surat permohonan magang tidak ditemukan.');
        }

        return Storage::download(
            $submission->letter_path,
            'surat_permohonan_magang_'.($submission->group->code ?? $submission->id).'.docx'
        );
    }

    /**
     * Upload the company response letter.
     */
    public function uploadResponse(Request $request, InternshipSubmission $submission): RedirectResponse
    {
        Gate::authorize('uploadResponse', $submission);

        $request->validate([
            'file' => 'required|file|mimes:pdf,docx,png,jpg,jpeg|max:2048',
        ], [
            'file.required' => 'Surat balasan wajib diunggah.',
            'file.file' => 'Berkas yang diunggah harus berupa file.',
            'file.mimes' => 'Format file surat balasan harus berupa PDF, DOCX, PNG, JPG, atau JPEG.',
            'file.max' => 'Ukuran file surat balasan tidak boleh lebih dari 2 MB.',
        ]);

        if ($submission->company_response_path) {
            Storage::delete($submission->company_response_path);
        }

        $path = $request->file('file')->store('company_responses');

        $submission->update([
            'company_response_path' => $path,
        ]);

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Surat balasan perusahaan berhasil diunggah.',
        ])->back();
    }
}
