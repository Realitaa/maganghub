<?php

namespace App\Http\Controllers;

use App\Models\GroupJoinRequest;
use App\Models\InternshipGroup;
use App\Models\User;
use App\Services\InternshipGroupService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class InternshipGroupController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(public InternshipGroupService $groupService) {}

    /**
     * Create a new internship group.
     */
    public function store(): RedirectResponse
    {
        try {
            $this->groupService->createGroup(auth()->user());

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Berhasil membuat kelompok magang.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    /**
     * Send a join request to an internship group.
     */
    public function join(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string']]);

        try {
            $this->groupService->requestToJoin(auth()->user(), $request->input('code'));

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Permintaan bergabung telah dikirim. Tunggu persetujuan ketua kelompok.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    /**
     * Cancel a pending join request.
     */
    public function cancelRequest(GroupJoinRequest $joinRequest): RedirectResponse
    {
        try {
            $this->groupService->cancelJoinRequest(auth()->user(), $joinRequest);

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Permintaan bergabung dibatalkan.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    /**
     * Approve a join request.
     */
    public function approveRequest(GroupJoinRequest $joinRequest): RedirectResponse
    {
        try {
            $this->groupService->approveJoinRequest(auth()->user(), $joinRequest);

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Permintaan bergabung diterima.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    /**
     * Reject a join request.
     */
    public function rejectRequest(GroupJoinRequest $joinRequest): RedirectResponse
    {
        try {
            $this->groupService->rejectJoinRequest(auth()->user(), $joinRequest);

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Permintaan bergabung ditolak.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    /**
     * Leave the current group.
     */
    public function leave(): RedirectResponse
    {
        try {
            $this->groupService->leaveGroup(auth()->user());

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Berhasil keluar dari kelompok magang.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    /**
     * Disband (delete) the internship group.
     */
    public function destroy(InternshipGroup $group): RedirectResponse
    {
        try {
            $this->groupService->disbandGroup(auth()->user(), $group);

            return Inertia::flash('toast', [
                'type' => 'success',
                'message' => 'Berhasil membubarkan kelompok magang.',
            ])->back();
        } catch (ValidationException $e) {
            return Inertia::flash('toast', [
                'type' => 'error',
                'message' => collect($e->errors())->flatten()->first(),
            ])->back();
        }
    }

    /**
     * Update the banner image for the internship group.
     * Also accepts an optional og_image for WhatsApp/social OG preview.
     */
    public function updateBanner(Request $request, InternshipGroup $group): RedirectResponse
    {
        /** @var User $user */
        $user = auth()->user();

        if ($group->leader_id !== $user->id) {
            abort(403, 'Hanya ketua kelompok yang dapat mengubah banner.');
        }

        $request->validate([
            'image' => ['required', 'file', 'mimes:webp,png,jpg,jpeg', 'max:3072'],
            'og_image' => ['nullable', 'file', 'mimes:webp,png,jpg,jpeg', 'max:512'],
        ]);

        $oldBannerPath = $group->banner_path;
        $oldOgPath = $group->og_image_path;

        $bannerPath = $request->file('image')->store('banners', 'public');

        $updates = ['banner_path' => $bannerPath];

        if ($request->hasFile('og_image')) {
            $ogPath = $request->file('og_image')->store('og-banners', 'public');
            $updates['og_image_path'] = $ogPath;

            if ($oldOgPath && Storage::disk('public')->exists($oldOgPath)) {
                Storage::disk('public')->delete($oldOgPath);
            }
        }

        $group->update($updates);

        if ($oldBannerPath && Storage::disk('public')->exists($oldBannerPath)) {
            Storage::disk('public')->delete($oldBannerPath);
        }

        return Inertia::flash('toast', [
            'type' => 'success',
            'message' => 'Banner kelompok berhasil diperbarui.',
        ])->back();
    }

    /**
     * Get details of a group by its code.
     */
    public function showByCode(string $code): JsonResponse
    {
        $group = InternshipGroup::with([
            'leader',
            'memberships.user',
            'activeSubmission',
        ])->where('code', $code)->first();

        if (! $group) {
            return response()->json(['message' => 'Kelompok tidak ditemukan.'], 404);
        }

        return response()->json([
            'code' => $group->code,
            'leader' => [
                'name' => $group->leader->name,
                'nim' => $group->leader->nim,
                'email' => $group->leader->email,
            ],
            'banner_url' => $group->bannerUrl(),
            'members_count' => $group->memberships->count(),
            'company_name' => $group->activeSubmission?->company_name,
        ]);
    }

    /**
     * Serve the OG/invite splash page for a group by its code.
     * This is a plain Blade view (not Inertia) so that crawlers (WhatsApp, etc.)
     * can read the OG meta tags. Real users are auto-redirected to the dashboard.
     */
    public function invite(string $code): View|RedirectResponse
    {
        $group = InternshipGroup::with('leader')->where('code', $code)->first();

        if (! $group) {
            return redirect()->route('home');
        }

        return view('invite', [
            'groupCode' => $group->code,
            'leaderName' => $group->leader->name,
            'ogImageUrl' => $group->ogImageUrl(),
            'appName' => config('app.name', 'MagangHub'),
            'appUrl' => config('app.url'),
            'redirectUrl' => route('home').'?code='.$group->code,
        ]);
    }
}
