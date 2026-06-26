<?php

namespace App\Http\Controllers;

use App\Http\Requests\Submissions\CompanyDecisionRequest;
use App\Http\Requests\Submissions\RejectSubmissionRequest;
use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Services\InternshipReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
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

        return Inertia::render('review/submissions/Index', [
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
            'group.leader:id,name,email,nim',
            'submissionMemberships.user:id,name,email,nim',
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
    public function reject(RejectSubmissionRequest $request, InternshipSubmission $submission): RedirectResponse
    {
        Gate::authorize('reject', $submission);

        try {
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

    /**
     * Display a listing of submissions ready for internship.
     */
    public function readyIndex(Request $request): Response
    {
        Gate::authorize('viewAny', InternshipSubmission::class);

        $query = InternshipSubmission::with([
            'group' => function ($q) {
                $q->select('id', 'leader_id', 'code')->withCount('memberships');
            },
            'group.leader:id,name,nim',
            'submissionMemberships.user:id,name,email,nim,phone,address,gender,semester',
        ]);

        $allSubmissions = $query->latest('updated_at')->get();

        $readyToPrint = $allSubmissions->filter(fn ($sub) => $sub->status === 'letter_published')->values();
        $waitingResponse = $allSubmissions->filter(fn ($sub) => $sub->status === 'applying')->values();
        $receivedResponse = $allSubmissions->filter(fn ($sub) => $sub->status === 'applying' && ! empty($sub->company_response_path))->values();

        return Inertia::render('review/Ready', [
            'readyToPrint' => $readyToPrint,
            'waitingResponse' => $waitingResponse,
            'receivedResponse' => $receivedResponse,
        ]);
    }

    /**
     * Mark the submission as actively applying to the company.
     */
    public function markApplying(InternshipSubmission $submission): RedirectResponse
    {
        Gate::authorize('approve', $submission);

        try {
            $this->reviewService->markAsApplying($submission);

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Status kelompok berhasil diubah menjadi sedang mengajukan.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    /**
     * Process the company placement outcome decision.
     */
    public function companyDecision(CompanyDecisionRequest $request, InternshipSubmission $submission): RedirectResponse
    {
        Gate::authorize('approve', $submission);

        try {
            $this->reviewService->processCompanyDecision(
                $submission,
                $request->input('decision'),
                $request->input('member_decisions', []),
                $request->input('new_leader_id')
            );

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Keputusan penempatan perusahaan berhasil diproses.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    /**
     * Display a listing of groups that have completed their administration.
     */
    public function groupsIndex(Request $request): Response
    {
        Gate::authorize('viewAny', InternshipSubmission::class);

        $today = Carbon::today();

        $groups = InternshipGroup::whereIn('status', ['accepted', 'partially_accepted', 'internship_started', 'completed'])
            ->with([
                'leader:id,name,nim',
                'activeSubmission',
                'memberships.user:id,name,nim',
            ])
            ->get()
            ->map(function ($group) use ($today) {
                $sub = $group->activeSubmission;
                $computedStatus = 'selesai_magang';
                if ($sub) {
                    if ($today->lt($sub->start_date)) {
                        $computedStatus = 'segera_magang';
                    } elseif ($today->gte($sub->start_date) && $today->lte($sub->end_date)) {
                        $computedStatus = 'melaksanakan_magang';
                    } else {
                        $computedStatus = 'selesai_magang';
                    }
                }
                $group->computed_status = $computedStatus;

                return $group;
            });

        // Apply filters
        $statusFilter = $request->input('status');
        if ($statusFilter && in_array($statusFilter, ['segera_magang', 'melaksanakan_magang', 'selesai_magang'])) {
            $groups = $groups->filter(fn ($g) => $g->computed_status === $statusFilter);
        }

        $search = $request->input('search');
        if (! empty($search)) {
            $groups = $groups->filter(function ($g) use ($search) {
                $companyName = $g->activeSubmission?->company_name ?? '';

                return str_contains(strtolower($g->code), strtolower($search)) ||
                       str_contains(strtolower($g->leader?->name ?? ''), strtolower($search)) ||
                       str_contains(strtolower($companyName), strtolower($search));
            });
        }

        // Sort: Segera Magang (1), Melaksanakan Magang (2), Selesai Magang (3)
        $sortWeights = [
            'segera_magang' => 1,
            'melaksanakan_magang' => 2,
            'selesai_magang' => 3,
        ];

        $sortedGroups = $groups->sortBy(fn ($g) => $sortWeights[$g->computed_status] ?? 99)->values();

        return Inertia::render('review/groups/Index', [
            'groups' => $sortedGroups,
            'filters' => [
                'search' => $search ?? '',
                'status' => $statusFilter ?? 'all',
            ],
        ]);
    }
}
