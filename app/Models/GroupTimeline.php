<?php

namespace App\Models;

use App\Enums\GroupTimelineType;
use App\Presenters\GroupTimelinePresenter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $group_id
 * @property GroupTimelineType $type
 * @property array|null $metadata
 * @property Carbon|null $created_at
 * @property-read InternshipGroup $group
 * @property-read string $title
 */
class GroupTimeline extends Model
{
    /**
     * Disable the updated_at timestamp.
     */
    const UPDATED_AT = null;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'group_id',
        'type',
        'metadata',
    ];

    /**
     * The attributes that should be appended to JSON representation.
     *
     * @var array<int, string>
     */
    protected $appends = ['title'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => GroupTimelineType::class,
            'metadata' => 'array',
        ];
    }

    /**
     * Get the group that owns the timeline record.
     *
     * @return BelongsTo<InternshipGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(InternshipGroup::class, 'group_id');
    }

    /**
     * Get the title/message of the timeline record.
     */
    public function getTitleAttribute(): string
    {
        return GroupTimelinePresenter::title($this);
    }
}
