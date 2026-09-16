<?php

namespace App\Http\Controllers;

use App\Http\Requests\Submissions\CompanyDecisionRequest;
use App\Http\Requests\Submissions\RejectSubmissionRequest;
use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Models\User;
use App\Notifications\KickedFromGroupNotification;
use App\Services\GroupTimelineService;
use App\Services\InternshipReviewService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class InternshipReviewController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        public InternshipReviewService $reviewService,
        public GroupTimelineService $timelineService
    ) {}

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
                'group.leader:id,name,nim',
            ])
            ->latest()
            ->get()
            ->map(function ($sub) {
                return [
                    'id' => $sub->id,
                    'company_name' => $sub->company_name,
                    'leader_name' => $sub->group->leader->name ?? '-',
                    'leader_nim' => $sub->group->leader->nim ?? '-',
                    'members_count' => $sub->group->memberships_count ?? 0,
                    'submitted_at' => $sub->created_at->toISOString(),
                    'status' => $sub->status,
                ];
            });

        return Inertia::render('internships/submissions/Index', [
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
            'group.memberships',
            'submissionMemberships.user:id,name,email,nim,address,phone,semester',
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
     * Display a listing of submissions ready for internship (Persiapan Magang).
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
        $receivedResponse = $allSubmissions->filter(fn ($sub) => $sub->status === 'loa_review')->values();

        return Inertia::render('internships/preparations/Index', [
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
     * Display a listing of all internship groups in any status (Manajemen Magang).
     */
    public function groupsIndex(Request $request): Response
    {
        Gate::authorize('viewAny', InternshipSubmission::class);

        $today = Carbon::today();

        $groups = InternshipGroup::with([
            'leader:id,name,nim',
            'activeSubmission',
            'memberships.user:id,name,nim',
        ])
            ->get()
            ->map(function ($group) use ($today) {
                $sub = $group->activeSubmission;
                $computedStatus = $group->status;
                if ($sub && in_array($group->status, ['accepted', 'partially_accepted', 'internship_started', 'completed'])) {
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
        if ($statusFilter && $statusFilter !== 'all') {
            $groups = $groups->filter(function ($g) use ($statusFilter) {
                return $g->computed_status === $statusFilter || $g->status === $statusFilter;
            });
        }

        $search = $request->input('search');
        if (! empty($search)) {
            $groups = $groups->filter(function ($g) use ($search) {
                $companyName = $g->activeSubmission?->company_name ?? '';
                $leaderNim = $g->leader?->nim ?? '';

                return str_contains(strtolower($g->code), strtolower($search)) ||
                       str_contains(strtolower($g->leader?->name ?? ''), strtolower($search)) ||
                       str_contains(strtolower($leaderNim), strtolower($search)) ||
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

        return Inertia::render('internships/groups/Index', [
            'groups' => $sortedGroups,
            'filters' => [
                'search' => $search ?? '',
                'status' => $statusFilter ?? 'all',
            ],
        ]);
    }

    /**
     * Display the detailed group dashboard for admin/operator.
     */
    public function groupDetail(InternshipGroup $group): Response
    {
        Gate::authorize('viewAny', InternshipSubmission::class);

        $group->load([
            'leader:id,name,email,nim,phone,address,gender,semester,student_class_id',
            'leader.studentClass',
            'memberships.user:id,name,email,nim,phone,address,gender,semester,student_class_id',
            'memberships.user.studentClass',
            'activeSubmission',
            'timelines' => fn ($q) => $q->latest('created_at'),
        ]);

        return Inertia::render('internships/groups/Show', [
            'group' => array_merge($group->toArray(), [
                'banner_url' => $group->bannerUrl(),
                'og_image_url' => $group->ogImageUrl(),
            ]),
        ]);
    }

    /**
     * Kick a member from the internship group by admin.
     */
    public function adminKickMember(Request $request, InternshipGroup $group): RedirectResponse
    {
        Gate::authorize('viewAny', InternshipSubmission::class);

        $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'reason' => ['required', 'string', 'max:500'],
        ]);

        $userId = $request->integer('user_id');

        if ($group->leader_id === $userId) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Ketua kelompok tidak dapat dikeluarkan. Silakan angkat ketua baru terlebih dahulu.',
            ])->back();
        }

        $membership = $group->memberships()->where('user_id', $userId)->first();
        if (! $membership) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Pengguna tidak terdaftar sebagai anggota kelompok ini.',
            ])->back();
        }

        $member = User::findOrFail($userId);
        $membership->delete();

        // Send user notification with the kick reason
        $groupName = $group->activeSubmission?->company_name ?: $group->leader->name;
        $member->notify(new KickedFromGroupNotification($groupName, auth()->user()->name, $request->string('reason'), 'Admin'));

        // Record group notification / timeline
        $this->timelineService->memberKicked($group, $member->name, $request->string('reason'));

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Berhasil mengeluarkan {$member->name} dari kelompok.",
        ])->back()->with('success', "Berhasil mengeluarkan {$member->name} dari kelompok.");
    }

    /**
     * Promote an existing member to become the new group leader.
     */
    public function adminChangeLeader(Request $request, InternshipGroup $group): RedirectResponse
    {
        Gate::authorize('viewAny', InternshipSubmission::class);

        $request->validate([
            'new_leader_id' => ['required', 'exists:users,id'],
        ]);

        $newLeaderId = $request->integer('new_leader_id');

        if ($group->leader_id === $newLeaderId) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Mahasiswa tersebut sudah merupakan ketua kelompok.',
            ])->back();
        }

        $isMember = $group->memberships()->where('user_id', $newLeaderId)->exists();
        if (! $isMember) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Pengguna harus merupakan anggota kelompok ini.',
            ])->back();
        }

        $oldLeaderName = $group->leader?->name ?? 'Ketua Sebelumnya';
        $newLeader = User::findOrFail($newLeaderId);

        $group->update(['leader_id' => $newLeaderId]);

        // Record group notification / timeline
        $this->timelineService->leaderChanged($group, $newLeader->name, $oldLeaderName);

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => "Berhasil mengangkat {$newLeader->name} sebagai ketua kelompok baru.",
        ])->back()->with('success', "Berhasil mengangkat {$newLeader->name} sebagai ketua kelompok baru.");
    }

    /**
     * Replace/overwrite the internship application letter file by admin.
     */
    public function adminReplaceLetter(Request $request, InternshipGroup $group): RedirectResponse
    {
        Gate::authorize('viewAny', InternshipSubmission::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:docx,pdf', 'max:10240'],
        ]);

        $submission = $group->activeSubmission;
        if (! $submission) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Kelompok ini belum memiliki pengajuan magang.',
            ])->back();
        }

        if ($submission->letter_path && Storage::exists($submission->letter_path)) {
            Storage::delete($submission->letter_path);
        }

        $path = $request->file('file')->store('letters');
        $submission->update(['letter_path' => $path]);

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Surat permohonan magang berhasil ditimpa.',
        ])->back()->with('success', 'Surat permohonan magang berhasil ditimpa.');
    }

    /**
     * Replace/overwrite the company response letter / LoA by admin.
     */
    public function adminReplaceResponse(Request $request, InternshipGroup $group): RedirectResponse
    {
        Gate::authorize('viewAny', InternshipSubmission::class);

        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,png,jpg,jpeg,docx', 'max:10240'],
        ]);

        $submission = $group->activeSubmission;
        if (! $submission) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => 'Kelompok ini belum memiliki pengajuan magang.',
            ])->back();
        }

        if ($submission->company_response_path && Storage::exists($submission->company_response_path)) {
            Storage::delete($submission->company_response_path);
        }

        $path = $request->file('file')->store('company_responses');
        $submission->update(['company_response_path' => $path]);

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Berkas LoA / surat balasan berhasil ditimpa.',
        ])->back()->with('success', 'Berkas LoA / surat balasan berhasil ditimpa.');
    }
}
