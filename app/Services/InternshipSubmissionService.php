<?php

namespace App\Services;

use App\Models\InternshipSubmission;
use App\Models\SubmissionMembership;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class InternshipSubmissionService
{
    public function __construct(protected GroupTimelineService $timelineService) {}

    /**
     * Save the internship submission data as a draft.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function saveDraft(User $user, array $data): InternshipSubmission
    {
        $group = $user->ledGroups()->first();

        if (! $group) {
            throw ValidationException::withMessages([
                'error' => 'Hanya ketua kelompok yang dapat menyimpan draf pengajuan.',
            ]);
        }

        $allowedStatuses = ['forming', 'company_rejected'];
        if (! in_array($group->status, $allowedStatuses)) {
            throw ValidationException::withMessages([
                'error' => 'Kamu tidak dapat mengubah pengajuan pada status kelompok saat ini.',
            ]);
        }

        // Validate basic draft data
        $validated = Validator::make($data, [
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_address' => ['nullable', 'string'],
            'company_contact' => ['nullable', 'string', 'max:255'],
            'company_leader' => ['nullable', 'string', 'max:255'],
            'division' => ['nullable', 'string', 'max:255'],
            'field_of_interest' => ['nullable', 'string', 'max:255'],
            'company_type' => ['nullable', 'string', 'in:Multinasional,Nasional,Startup Teknologi'],
            'working_model' => ['nullable', 'string', 'in:WFO,WFA,Hybrid'],
            'start_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'date_format:Y-m-d', 'after:today', 'after:start_date'],
        ])->validate();

        // Find existing draft submission or create a new one
        $submission = InternshipSubmission::where('group_id', $group->id)
            ->latest()
            ->first();

        if ($submission && $submission->status === 'draft') {
            $submission->update(array_merge($validated, [
                'status' => 'draft',
            ]));
        } else {
            $submission = InternshipSubmission::create(array_merge($validated, [
                'group_id' => $group->id,
                'status' => 'draft',
            ]));
        }

        return $submission;
    }

    /**
     * Submit the internship proposal to admin for review.
     *
     * @param  array<string, mixed>  $data
     *
     * @throws ValidationException
     */
    public function submitProposal(User $user, array $data): InternshipSubmission
    {
        $group = $user->ledGroups()->with('memberships')->first();

        if (! $group) {
            throw ValidationException::withMessages([
                'error' => 'Hanya ketua kelompok yang dapat mengajukan magang.',
            ]);
        }

        $allowedStatuses = ['forming', 'company_rejected'];
        if (! in_array($group->status, $allowedStatuses)) {
            throw ValidationException::withMessages([
                'error' => 'Kamu tidak dapat mengajukan magang pada status kelompok saat ini.',
            ]);
        }

        // Validate all required fields for submission
        $validated = Validator::make($data, [
            'company_name' => ['required', 'string', 'max:255'],
            'company_address' => ['required', 'string'],
            'company_contact' => ['required', 'string', 'max:255'],
            'company_leader' => ['nullable', 'string', 'max:255'],
            'division' => ['nullable', 'string', 'max:255'],
            'field_of_interest' => ['required', 'string', 'max:255'],
            'company_type' => ['required', 'string', 'in:Multinasional,Nasional,Startup Teknologi'],
            'working_model' => ['required', 'string', 'in:WFO,WFA,Hybrid'],
            'start_date' => ['required', 'date', 'date_format:Y-m-d', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'date_format:Y-m-d', 'after:today', 'after:start_date'],
        ], [
            'company_name.required' => 'Nama perusahaan wajib diisi.',
            'company_address.required' => 'Alamat perusahaan wajib diisi.',
            'company_contact.required' => 'Kontak perusahaan wajib diisi.',
            'field_of_interest.required' => 'Bidang yang diminati wajib diisi.',
            'company_type.required' => 'Tipe perusahaan wajib diisi.',
            'company_type.in' => 'Tipe perusahaan harus salah satu dari: Multinasional, Nasional, atau Startup Teknologi.',
            'working_model.required' => 'Model pengerjaan magang wajib diisi.',
            'working_model.in' => 'Model pengerjaan magang harus salah satu dari: WFO, WFA, atau Hybrid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh tanggal yang sudah lalu.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after' => 'Tanggal selesai harus setelah tanggal mulai dan setelah hari ini.',
        ])->validate();

        // Get active members of the group
        $members = $group->memberships;
        if ($members->count() < 2) {
            throw ValidationException::withMessages([
                'error' => 'Kelompok harus memiliki minimal dua anggota sebelum mengajukan magang.',
            ]);
        }

        // Save or update submission
        $submission = InternshipSubmission::where('group_id', $group->id)
            ->latest()
            ->first();

        if ($submission && $submission->status === 'draft') {
            $submission->update(array_merge($validated, [
                'status' => 'submitted',
            ]));
        } else {
            $submission = InternshipSubmission::create(array_merge($validated, [
                'group_id' => $group->id,
                'status' => 'submitted',
            ]));
        }

        // Update group status
        $group->update([
            'status' => 'submitted',
        ]);

        // Record timeline
        $this->timelineService->submissionCreated($group);

        // Create snapshot membership records
        // Clean up any existing submission memberships for this submission to avoid duplicate key issues if re-submitting
        SubmissionMembership::where('submission_id', $submission->id)->delete();

        foreach ($members as $member) {
            SubmissionMembership::create([
                'submission_id' => $submission->id,
                'user_id' => $member->user_id,
                'status' => 'pending',
            ]);
        }

        return $submission;
    }
}
