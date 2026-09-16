<?php

namespace App\Services;

use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AdminDashboardService
{
    /**
     * Domain statuses for internship groups and their human-readable Indonesian labels.
     *
     * @var array<string, string>
     */
    public const GROUP_STATUS_LABELS = [
        'forming' => 'Membentuk Kelompok',
        'submitted' => 'Pengajuan Dikirim',
        'letter_published' => 'Surat Terbit',
        'applying' => 'Menunggu Balasan Perusahaan',
        'loa_review' => 'Review Balasan Perusahaan',
        'accepted' => 'Diterima',
        'partially_accepted' => 'Diterima Sebagian',
        'rejected' => 'Ditolak Perusahaan',
        'internship_started' => 'Sedang Magang',
        'completed' => 'Selesai Magang',
    ];

    /**
     * Palette colors for group status chart.
     *
     * @var array<string, string>
     */
    public const GROUP_STATUS_COLORS = [
        'forming' => '#94a3b8',
        'submitted' => '#f59e0b',
        'letter_published' => '#3b82f6',
        'applying' => '#6366f1',
        'loa_review' => '#a855f7',
        'accepted' => '#10b981',
        'partially_accepted' => '#14b8a6',
        'rejected' => '#f43f5e',
        'internship_started' => '#06b6d4',
        'completed' => '#22c55e',
    ];

    /**
     * Get summary statistics for the 4 summary cards.
     *
     * @return array<string, int>
     */
    public function getSummary(): array
    {
        $totalStudents = User::where('role', 'student')->count();
        $totalGroups = InternshipGroup::count();
        $pendingSubmissions = InternshipSubmission::where('status', 'submitted')->count();
        $totalCompanies = InternshipSubmission::whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->distinct('company_name')
            ->count('company_name');

        return [
            'total_students' => $totalStudents,
            'total_groups' => $totalGroups,
            'pending_submissions' => $pendingSubmissions,
            'total_companies' => $totalCompanies,
        ];
    }

    /**
     * Get operational overview data for the 2 operational cards.
     *
     * @return array<string, mixed>
     */
    public function getOperational(): array
    {
        $pendingSubmissions = InternshipSubmission::where('status', 'submitted')->count();
        $waitingPreparations = InternshipSubmission::where('status', 'loa_review')->count();

        $groupStatusCounts = InternshipGroup::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        $groupStatusDistribution = [];
        foreach (self::GROUP_STATUS_LABELS as $statusKey => $statusLabel) {
            $count = (int) ($groupStatusCounts->get($statusKey, 0));
            if ($count > 0) {
                $groupStatusDistribution[] = [
                    'status' => $statusKey,
                    'name' => $statusLabel,
                    'y' => $count,
                    'color' => self::GROUP_STATUS_COLORS[$statusKey] ?? '#64748b',
                ];
            }
        }

        return [
            'pending_submissions' => $pendingSubmissions,
            'waiting_preparations' => $waitingPreparations,
            'group_status_distribution' => $groupStatusDistribution,
            'total_groups' => InternshipGroup::count(),
        ];
    }
}
