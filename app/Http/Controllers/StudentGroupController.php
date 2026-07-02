<?php

namespace App\Http\Controllers;

use App\Models\InternshipGroup;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class StudentGroupController extends Controller
{
    /**
     * Display the dashboard for a specific internship group for the student.
     */
    public function show(InternshipGroup $group): Response
    {
        /** @var User $user */
        $user = auth()->user();

        // Ensure the student is a member of this group
        $membership = $group->memberships()->where('user_id', $user->id)->firstOrFail();

        $group->load([
            'leader',
            'memberships.user',
            'activeSubmission',
            'timelines' => fn ($query) => $query->latest('created_at'),
        ]);

        // Expose pending join requests only if the user is the leader of THIS group
        if ($group->leader_id === $user->id) {
            $group->load([
                'joinRequests' => fn ($query) => $query
                    ->where('status', 'pending')
                    // Wait! Since members can join multiple groups, we don't need to filter out
                    // whereDoesntHave('user.groupMembership') anymore!
                    // Wait, we DO want to filter out users who are ALREADY in THIS group!
                    ->whereDoesntHave('user.groupMemberships', fn ($q) => $q->where('group_id', $group->id))
                    ->with('user')
                    ->latest(),
            ]);
        } else {
            $group->setRelation('joinRequests', collect());
        }

        return Inertia::render('student/GroupDashboard', [
            'group' => array_merge($group->toArray(), [
                'banner_url' => $group->bannerUrl(),
                'og_image_url' => $group->ogImageUrl(),
                'invite_url' => route('groups.invite', $group->code),
                'membership_status' => $membership->status,
            ]),
        ]);
    }
}
