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
}
