<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $leader_id
 * @property string $code
 * @property string $status
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['leader_id', 'code', 'status'])]
class InternshipGroup extends Model
{
    use HasFactory;

    /**
     * Get the leader of the group.
     */
    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    /**
     * Get the memberships of this group.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(GroupMembership::class, 'group_id');
    }

    /**
     * Get the members of this group (through memberships).
     */
    public function members(): HasManyThrough
    {
        return $this->hasManyThrough(User::class, GroupMembership::class, 'group_id', 'id', 'id', 'user_id');
    }

    /**
     * Get the join requests for this group.
     */
    public function joinRequests(): HasMany
    {
        return $this->hasMany(GroupJoinRequest::class, 'group_id');
    }

    /**
     * Get the submissions of this group.
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(InternshipSubmission::class, 'group_id');
    }
}
