<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $leader_id
 * @property string $code
 * @property string $status
 * @property string|null $banner_path
 * @property string|null $og_image_path
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['leader_id', 'code', 'status', 'banner_path', 'og_image_path'])]
class InternshipGroup extends Model
{
    use HasFactory;

    /**
     * Get the public URL for the group banner.
     * Falls back to the default company background image.
     */
    public function bannerUrl(): string
    {
        if ($this->banner_path && Storage::disk('public')->exists($this->banner_path)) {
            return Storage::disk('public')->url($this->banner_path);
        }

        return asset('assets/images/default-company-background.png');
    }

    /**
     * Get the public URL for the OG image (used in WhatsApp/social previews).
     * Falls back to the dedicated default OG image asset.
     */
    public function ogImageUrl(): string
    {
        if ($this->og_image_path && Storage::disk('public')->exists($this->og_image_path)) {
            return Storage::disk('public')->url($this->og_image_path);
        }

        return asset('assets/images/default-company-background-og.png');
    }

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

    /**
     * Get the active (latest) submission of this group.
     */
    public function activeSubmission(): HasOne
    {
        return $this->hasOne(InternshipSubmission::class, 'group_id')->latestOfMany();
    }

    /**
     * Get the timelines of this group.
     *
     * @return HasMany<GroupTimeline, $this>
     */
    public function timelines(): HasMany
    {
        return $this->hasMany(GroupTimeline::class, 'group_id');
    }
}
