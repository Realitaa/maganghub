<?php

namespace App\Http\Controllers;

use App\Services\InternshipSubmissionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
