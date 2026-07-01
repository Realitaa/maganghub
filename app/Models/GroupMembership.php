<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $group_id
 * @property int $user_id
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['group_id', 'user_id', 'status'])]
class GroupMembership extends Model
{
    use HasFactory;

    /**
     * Get the group this membership belongs to.
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(InternshipGroup::class, 'group_id');
    }

    /**
     * Get the user this membership belongs to.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
