<?php

namespace App\Http\Controllers;

use App\Models\GroupJoinRequest;
use App\Models\InternshipGroup;
use App\Services\InternshipGroupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
}
