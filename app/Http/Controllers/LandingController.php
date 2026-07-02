<?php

namespace App\Http\Controllers;

use App\Services\LandingStatisticsService;
use Inertia\Inertia;
use Inertia\Response;

class LandingController extends Controller
{
    /**
     * Display the landing page with statistics.
     */
    public function index(LandingStatisticsService $statisticsService): Response
    {
        return Inertia::render('Welcome', [
            'statistics' => $statisticsService->get(),
        ]);
    }
}
