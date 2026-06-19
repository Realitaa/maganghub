<?php

namespace App\Http\Controllers;

use App\Models\InternshipSubmission;
use App\Services\InternshipReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InternshipReviewController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(public InternshipReviewService $reviewService) {}

    /**
     * Display a listing of active internship submissions.
     */
    public function index(): Response
    {
        Gate::authorize('viewAny', InternshipSubmission::class);

        $submissions = InternshipSubmission::where('status', 'submitted')
            ->with([
                'group' => function ($query) {
                    $query->select('id', 'leader_id')->withCount('memberships');
                },
                'group.leader:id,name',
            ])
            ->latest()
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'company_name' => $sub->company_name,
                    'leader_name' => $sub->group->leader->name ?? '-',
                    'members_count' => $sub->group->memberships_count ?? 0,
                    'submitted_at' => $sub->created_at->toISOString(),
                    'status' => $sub->status,
                ];
            });

        return Inertia::render('submissions/Index', [
            'submissions' => $submissions,
        ]);
    }

    /**
     * Display the specified submission detail.
     */
    public function show(InternshipSubmission $submission): JsonResponse
    {
        Gate::authorize('view', $submission);

        $submission->load([
            'group.leader:id,name,email,nim,major',
            'submissionMemberships.user:id,name,email,nim,major',
        ]);

        return response()->json([
            'data' => $submission,
        ]);
    }

    /**
     * Approve the specified submission.
     */
    public function approve(InternshipSubmission $submission): RedirectResponse
    {
        Gate::authorize('approve', $submission);

        try {
            $this->reviewService->approveSubmission($submission);

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pengajuan magang berhasil disetujui.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    /**
     * Reject the specified submission with notes.
     */
    public function reject(Request $request, InternshipSubmission $submission): RedirectResponse
    {
        Gate::authorize('reject', $submission);

        try {
            $request->validate([
                'notes' => ['required', 'string'],
            ], [
                'notes.required' => 'Catatan penolakan wajib diisi.',
            ]);

            $this->reviewService->rejectSubmission($submission, $request->input('notes'));

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Pengajuan magang berhasil ditolak.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }
}
