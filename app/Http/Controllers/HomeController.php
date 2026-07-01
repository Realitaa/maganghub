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
            $memberships = $user->groupMemberships()->with([
                'group.leader',
                'group.memberships.user',
                'group.activeSubmission',
            ])->get();

            $groups = $memberships->map(function ($membership) {
                $group = $membership->group;
                return array_merge($group->toArray(), [
                    'banner_url' => $group->bannerUrl(),
                    'og_image_url' => $group->ogImageUrl(),
                    'membership_status' => $membership->status,
                ]);
            });

            $pendingJoinRequests = $user->joinRequests()
                ->with('group.leader')
                ->where('status', 'pending')
                ->latest()
                ->get();

            return Inertia::render('student/Index', [
                'groups' => $groups,
                'pendingJoinRequests' => $pendingJoinRequests,
            ]);
        }

        return Inertia::render('Dashboard');
    }
}
