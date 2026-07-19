<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $group_id
 * @property string $company_name
 * @property string $company_address
 * @property string $company_contact
 * @property string $division
 * @property string $field_of_interest
 * @property string|null $company_type
 * @property string|null $working_model
 * @property Carbon $start_date
 * @property Carbon $end_date
 * @property string|null $letter_path
 * @property string $status
 * @property string|null $rejection_note
 * @property string|null $company_response_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['group_id', 'company_name', 'company_address', 'company_contact', 'division', 'field_of_interest', 'company_type', 'working_model', 'start_date', 'end_date', 'letter_path', 'status', 'rejection_note', 'company_response_path'])]
class InternshipSubmission extends Model
{
    use HasFactory;

    /**
     * Get the attribute casts.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
        ];
    }

    /**
     * Get the group this submission belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(InternshipGroup::class, 'group_id');
    }

    /**
     * Get the snapshot memberships of this submission.
     */
    public function submissionMemberships(): HasMany
    {
        return $this->hasMany(SubmissionMembership::class, 'submission_id');
    }
}
