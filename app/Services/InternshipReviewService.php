<?php

namespace App\Services;

use App\Models\InternshipSubmission;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InternshipReviewService
{
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

            // Generate document if not already generated
            if (! $lockedSubmission->letter_path) {
                try {
                    $generator = app(DocumentGeneratorService::class);
                    $path = $generator->generateLetter($lockedSubmission);
                    $lockedSubmission->update([
                        'letter_path' => $path,
                    ]);
                } catch (\Exception $e) {
                    throw ValidationException::withMessages([
                        'error' => 'Gagal membuat dokumen permohonan magang: '.$e->getMessage(),
                    ]);
                }
            }

            return $lockedSubmission;
        });
    }
}
