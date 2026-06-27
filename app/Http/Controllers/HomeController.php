<?php

namespace App\Http\Controllers;

use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class HomeController extends Controller
{
    /**
     * Display the dashboard for the authenticated user.
     */
    public function index(): Response
    {
        /** @var User $user */
        $user = auth()->user();

        if ($user->role === 'student') {
            $membership = $user->groupMembership()->with([
                'group.leader',
                'group.memberships.user',
                'group.activeSubmission',
                'group.timelines' => fn ($query) => $query->latest('created_at'),
            ])->first();

            $group = null;
            $pendingJoinRequests = null;

            if ($membership) {
                $group = $membership->group;

                // Only expose pending join requests to the leader
                if ($group->leader_id === $user->id) {
                    $group->load([
                        'joinRequests' => fn ($query) => $query
                            ->where('status', 'pending')
                            ->whereDoesntHave('user.groupMembership')
                            ->with('user')
                            ->latest(),
                    ]);
                } else {
                    $group->setRelation('joinRequests', collect());
                }
            } else {
                $pendingJoinRequests = $user->joinRequests()
                    ->with('group.leader')
                    ->where('status', 'pending')
                    ->latest()
                    ->get();
            }

            return Inertia::render('student/Index', [
                'group' => $group ? array_merge($group->toArray(), [
                    'banner_url' => $group->bannerUrl(),
                    'og_image_url' => $group->ogImageUrl(),
                    'invite_url' => route('groups.invite', $group->code),
                ]) : null,
                'pendingJoinRequests' => $pendingJoinRequests ?? collect(),
            ]);
        }

        return Inertia::render('Dashboard');
    }
}
