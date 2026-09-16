<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\AdminDashboardService;
use App\Services\LandingStatisticsService;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard.
     */
    public function index(
        AdminDashboardService $dashboardService,
        LandingStatisticsService $landingStatisticsService
    ): Response|RedirectResponse {
        /** @var User $user */
        $user = auth()->user();

        if ($user->role === 'student') {
            return redirect()->route('home');
        }

        return Inertia::render('Dashboard', [
            'summary' => $dashboardService->getSummary(),
            'operational' => $dashboardService->getOperational(),
            'publicChart' => $landingStatisticsService->refresh(),
        ]);
    }
}
