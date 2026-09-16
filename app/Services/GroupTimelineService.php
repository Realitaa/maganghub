<?php

namespace App\Services;

use App\Enums\GroupTimelineType;
use App\Models\GroupTimeline;
use App\Models\InternshipGroup;

class GroupTimelineService
{
    /**
     * Record submission created.
     */
    public function submissionCreated(InternshipGroup $group): GroupTimeline
    {
        return GroupTimeline::create([
            'group_id' => $group->id,
            'type' => GroupTimelineType::SubmissionCreated,
            'metadata' => null,
        ]);
    }

    /**
     * Record submission rejected with reason.
     */
    public function submissionRejected(InternshipGroup $group, string $reason): GroupTimeline
    {
        return GroupTimeline::create([
            'group_id' => $group->id,
            'type' => GroupTimelineType::SubmissionRejected,
            'metadata' => ['reason' => $reason],
        ]);
    }

    /**
     * Record submission approved.
     */
    public function submissionApproved(InternshipGroup $group): GroupTimeline
    {
        return GroupTimeline::create([
            'group_id' => $group->id,
            'type' => GroupTimelineType::SubmissionApproved,
            'metadata' => null,
        ]);
    }

    /**
     * Record application letter printed.
     */
    public function applicationLetterPrinted(InternshipGroup $group): GroupTimeline
    {
        return GroupTimeline::create([
            'group_id' => $group->id,
            'type' => GroupTimelineType::ApplicationLetterPrinted,
            'metadata' => null,
        ]);
    }

    /**
     * Record company reply uploaded.
     */
    public function companyReplyUploaded(InternshipGroup $group): GroupTimeline
    {
        return GroupTimeline::create([
            'group_id' => $group->id,
            'type' => GroupTimelineType::CompanyReplyUploaded,
            'metadata' => null,
        ]);
    }

    /**
     * Record administration completed.
     */
    public function administrationCompleted(InternshipGroup $group): GroupTimeline
    {
        return GroupTimeline::create([
            'group_id' => $group->id,
            'type' => GroupTimelineType::AdministrationCompleted,
            'metadata' => null,
        ]);
    }

    /**
     * Record leader changed.
     */
    public function leaderChanged(InternshipGroup $group, string $newLeaderName, string $oldLeaderName): GroupTimeline
    {
        return GroupTimeline::create([
            'group_id' => $group->id,
            'type' => GroupTimelineType::LeaderChanged,
            'metadata' => [
                'new_leader_name' => $newLeaderName,
                'old_leader_name' => $oldLeaderName,
            ],
        ]);
    }

    /**
     * Record member kicked by admin.
     */
    public function memberKicked(InternshipGroup $group, string $memberName, string $reason): GroupTimeline
    {
        return GroupTimeline::create([
            'group_id' => $group->id,
            'type' => GroupTimelineType::MemberKicked,
            'metadata' => [
                'member_name' => $memberName,
                'reason' => $reason,
            ],
        ]);
    }

    /**
     * Record group status updated by admin/operator.
     */
    public function statusUpdated(InternshipGroup $group, string $newStatus, string $statusLabel, string $actor, ?string $reason = null): GroupTimeline
    {
        return GroupTimeline::create([
            'group_id' => $group->id,
            'type' => GroupTimelineType::StatusUpdated,
            'metadata' => [
                'status' => $newStatus,
                'status_label' => $statusLabel,
                'actor' => $actor,
                'reason' => $reason,
            ],
        ]);
    }
}
