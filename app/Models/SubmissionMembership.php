<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $submission_id
 * @property int $user_id
 * @property string $status
 * @property string|null $rejection_note
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['submission_id', 'user_id', 'status', 'rejection_note'])]
class SubmissionMembership extends Model
{
    use HasFactory;

    /**
     * Get the submission this membership snapshot belongs to.
     */
    public function submission(): BelongsTo
    {
        return $this->belongsTo(InternshipSubmission::class, 'submission_id');
    }

    /**
     * Get the user of this snapshot.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
