<?php

namespace App\Services;

use App\Enums\GroupTimelineType;
use App\Models\GroupTimeline;
use App\Models\InternshipSubmission;
use App\Models\User;
use App\Notifications\InternshipRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class InternshipReviewService
{
    public function __construct(protected GroupTimelineService $timelineService) {}

    /**
     * Reject the internship submission with notes.
     *
     * @throws ValidationException
     */
    public function rejectSubmission(InternshipSubmission $submission, string $notes): InternshipSubmission
    {
        if (empty(trim($notes))) {
            throw ValidationException::withMessages([
                'notes' => 'Catatan penolakan wajib diisi.',
            ]);
        }

        return DB::transaction(function () use ($submission, $notes) {
            /** @var InternshipSubmission $lockedSubmission */
            $lockedSubmission = InternshipSubmission::where('id', $submission->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedSubmission || $lockedSubmission->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'error' => 'Pengajuan ini sudah tidak dapat diproses.',
                ]);
            }

            // Reject the submission
            $lockedSubmission->update([
                'status' => 'rejected',
                'rejection_note' => $notes,
            ]);

            // Revert group status to forming
            $lockedSubmission->group->update([
                'status' => 'forming',
            ]);

            // Record timeline
            $this->timelineService->submissionRejected($lockedSubmission->group, $notes);

            return $lockedSubmission;
        });
    }

    /**
     * Approve the internship submission and set status to letter_published.
     *
     * @throws ValidationException
     */
    public function approveSubmission(InternshipSubmission $submission): InternshipSubmission
    {
        return DB::transaction(function () use ($submission) {
            /** @var InternshipSubmission $lockedSubmission */
            $lockedSubmission = InternshipSubmission::where('id', $submission->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedSubmission || $lockedSubmission->status !== 'submitted') {
                throw ValidationException::withMessages([
                    'error' => 'Pengajuan ini sudah tidak dapat diproses.',
                ]);
            }

            // Approve the submission
            $lockedSubmission->update([
                'status' => 'letter_published',
            ]);

            // Update group status to letter_published
            $lockedSubmission->group->update([
                'status' => 'letter_published',
            ]);

            // Record timeline
            $this->timelineService->submissionApproved($lockedSubmission->group);

            // Generate all individual letters
            try {
                $generator = app(DocumentGeneratorService::class);
                foreach ($lockedSubmission->submissionMemberships as $membership) {
                    if (! $membership->letter_path) {
                        $path = $generator->generateLetter($lockedSubmission, $membership->user_id);
                        $membership->update([
                            'letter_path' => $path,
                        ]);
                    }
                }
            } catch (\Exception $e) {
                throw ValidationException::withMessages([
                    'error' => 'Gagal membuat dokumen permohonan magang: '.$e->getMessage(),
                ]);
            }

            return $lockedSubmission;
        });
    }

    /**
     * Mark the submission and group as actively applying to the company.
     *
     * @throws ValidationException
     */
    public function markAsApplying(InternshipSubmission $submission): InternshipSubmission
    {
        return DB::transaction(function () use ($submission) {
            /** @var InternshipSubmission $lockedSubmission */
            $lockedSubmission = InternshipSubmission::where('id', $submission->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedSubmission || $lockedSubmission->status !== 'letter_published') {
                throw ValidationException::withMessages([
                    'error' => 'Kelompok ini belum disetujui atau sudah mengajukan.',
                ]);
            }

            // Update submission status
            $lockedSubmission->update([
                'status' => 'applying',
            ]);

            // Update group status
            $lockedSubmission->group->update([
                'status' => 'applying',
            ]);

            // Record timeline
            $this->timelineService->applicationLetterPrinted($lockedSubmission->group);

            return $lockedSubmission;
        });
    }

    /**
     * Process the company placement outcome decision.
     *
     * @throws ValidationException
     */
    public function processCompanyDecision(
        InternshipSubmission $submission,
        string $decision,
        array $memberDecisions = [],
        ?int $newLeaderId = null
    ): InternshipSubmission {
        return DB::transaction(function () use ($submission, $decision, $memberDecisions, $newLeaderId) {
            /** @var InternshipSubmission $lockedSubmission */
            $lockedSubmission = InternshipSubmission::where('id', $submission->id)
                ->lockForUpdate()
                ->first();

            if (! $lockedSubmission || ! in_array($lockedSubmission->status, ['applying', 'loa_review'])) {
                throw ValidationException::withMessages([
                    'error' => 'Kelompok ini tidak sedang dalam status menunggu balasan perusahaan.',
                ]);
            }

            $group = $lockedSubmission->group;

            if ($decision === 'all_accepted') {
                // 1. All Accepted
                $lockedSubmission->update(['status' => 'accepted']);
                $group->update(['status' => 'accepted']);

                $lockedSubmission->submissionMemberships()->update(['status' => 'accepted']);
            } elseif ($decision === 'all_rejected') {
                // 2. All Rejected (Reset to forming, clear documents to save storage)
                $lockedSubmission->update(['status' => 'rejected']);
                $group->update(['status' => 'forming']);

                $lockedSubmission->submissionMemberships()->update(['status' => 'rejected']);

                // Delete member letters from storage and nullify db path
                foreach ($lockedSubmission->submissionMemberships as $subMem) {
                    if ($subMem->letter_path) {
                        Storage::delete($subMem->letter_path);
                        $subMem->update(['letter_path' => null]);
                    }
                }

                // Delete company response letter from storage and nullify db path
                if ($lockedSubmission->company_response_path) {
                    Storage::delete($lockedSubmission->company_response_path);
                    $lockedSubmission->update(['company_response_path' => null]);
                }

                // Add timeline entry
                GroupTimeline::create([
                    'group_id' => $group->id,
                    'type' => GroupTimelineType::CompanyRejected,
                    'metadata' => [
                        'company_name' => $lockedSubmission->company_name,
                    ],
                ]);
            } elseif ($decision === 'partially_accepted') {
                // 3. Partially Accepted
                $acceptedUserIds = [];
                $rejectedUserIds = [];
                $acceptedNames = [];
                $rejectedNames = [];

                foreach ($memberDecisions as $mDecision) {
                    $userId = $mDecision['user_id'] ?? null;
                    $mStatus = $mDecision['status'] ?? null;
                    $rejectionNote = $mDecision['rejection_note'] ?? null;

                    if (! $userId || ! in_array($mStatus, ['accepted', 'rejected'])) {
                        continue;
                    }

                    $subMem = $lockedSubmission->submissionMemberships()
                        ->where('user_id', $userId)
                        ->first();

                    if ($subMem) {
                        $subMem->update([
                            'status' => $mStatus,
                            'rejection_note' => $mStatus === 'rejected' ? $rejectionNote : null,
                        ]);
                    }

                    $user = User::find($userId);
                    $userName = $user ? $user->name : "User ID {$userId}";

                    if ($mStatus === 'accepted') {
                        $acceptedUserIds[] = $userId;
                        $acceptedNames[] = $userName;
                    } else {
                        $rejectedUserIds[] = $userId;
                        $rejectedNames[] = $userName;

                        // Notify rejected member
                        if ($user) {
                            $user->notify(new InternshipRejectedNotification($lockedSubmission->company_name, $rejectionNote));
                        }
                    }
                }

                // Validation: At least one member must be accepted for partially_accepted
                if (empty($acceptedUserIds)) {
                    throw ValidationException::withMessages([
                        'error' => 'Minimal harus ada satu anggota yang diterima untuk menggunakan opsi ini. Jika semua ditolak, silakan gunakan opsi Semua Ditolak.',
                    ]);
                }

                // If leader is rejected, check new leader choice
                $isLeaderRejected = in_array($group->leader_id, $rejectedUserIds);
                $newLeaderName = null;
                if ($isLeaderRejected) {
                    if (! $newLeaderId || ! in_array($newLeaderId, $acceptedUserIds)) {
                        throw ValidationException::withMessages([
                            'error' => 'Ketua kelompok ditolak, Anda wajib menunjuk Ketua baru dari anggota yang diterima.',
                        ]);
                    }
                    $group->update(['leader_id' => $newLeaderId]);
                    $newLeaderUser = User::find($newLeaderId);
                    $newLeaderName = $newLeaderUser ? $newLeaderUser->name : null;
                }

                // Update submission & group status
                $lockedSubmission->update(['status' => 'partially_accepted']);
                $group->update(['status' => 'partially_accepted']);

                // Clear rejected memberships from active group_memberships
                if (! empty($rejectedUserIds)) {
                    $group->memberships()->whereIn('user_id', $rejectedUserIds)->delete();
                }

                // Add timeline entry
                GroupTimeline::create([
                    'group_id' => $group->id,
                    'type' => GroupTimelineType::CompanyPartiallyAccepted,
                    'metadata' => [
                        'company_name' => $lockedSubmission->company_name,
                        'accepted_names' => $acceptedNames,
                        'rejected_names' => $rejectedNames,
                        'new_leader_name' => $newLeaderName,
                    ],
                ]);
            } else {
                throw ValidationException::withMessages([
                    'error' => 'Keputusan tidak valid.',
                ]);
            }

            if ($decision === 'all_accepted' || $decision === 'partially_accepted') {
                $this->timelineService->administrationCompleted($group);
            }

            return $lockedSubmission;
        });
    }
}
