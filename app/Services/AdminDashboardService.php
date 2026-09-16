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
            'student_internship_status' => $this->getStudentInternshipStatus(),
            'total_groups' => InternshipGroup::count(),
        ];
    }

    /**
     * Get student internship progress status:
     * 1. Belum Magang (merah): Membentuk kelompok dan mengajukan / belum ada kelompok
     * 2. Sedang Mengajukan (kuning): Pengajuan diterima (surat terbit) hingga mendapatkan LoA
     * 3. Akan/Melaksanakan Magang (hijau): Sudah konfirmasi magang setelah LoA diterima
     *
     * @return array<int, array<string, mixed>>
     */
    public function getStudentInternshipStatus(): array
    {
        $totalStudents = User::where('role', 'student')->count();

        // 3. Akan/Melaksanakan Magang (Hijau):
        // Status kelompok: accepted, partially_accepted, internship_started, completed
        // Atau status keanggotaan: interning_elsewhere
        $akanMelaksanakan = DB::table('group_memberships')
            ->join('internship_groups', 'group_memberships.group_id', '=', 'internship_groups.id')
            ->join('users', 'group_memberships.user_id', '=', 'users.id')
            ->where('users.role', 'student')
            ->where(function ($query) {
                $query->where(function ($q) {
                    $q->where('group_memberships.status', 'active')
                        ->whereIn('internship_groups.status', ['accepted', 'partially_accepted', 'internship_started', 'completed']);
                })->orWhere('group_memberships.status', 'interning_elsewhere');
            })
            ->distinct('group_memberships.user_id')
            ->count('group_memberships.user_id');

        // 2. Sedang Mengajukan (Kuning):
        // Status kelompok: letter_published, applying, loa_review
        $sedangMengajukan = DB::table('group_memberships')
            ->join('internship_groups', 'group_memberships.group_id', '=', 'internship_groups.id')
            ->join('users', 'group_memberships.user_id', '=', 'users.id')
            ->where('users.role', 'student')
            ->where('group_memberships.status', 'active')
            ->whereIn('internship_groups.status', ['letter_published', 'applying', 'loa_review'])
            ->whereNotIn('group_memberships.user_id', function ($sub) {
                $sub->select('gm.user_id')
                    ->from('group_memberships as gm')
                    ->join('internship_groups as ig', 'gm.group_id', '=', 'ig.id')
                    ->where(function ($q) {
                        $q->where(function ($q2) {
                            $q2->where('gm.status', 'active')
                                ->whereIn('ig.status', ['accepted', 'partially_accepted', 'internship_started', 'completed']);
                        })->orWhere('gm.status', 'interning_elsewhere');
                    });
            })
            ->distinct('group_memberships.user_id')
            ->count('group_memberships.user_id');

        // 1. Belum Magang (Merah):
        // Sisa mahasiswa: membentuk kelompok, pengajuan dikirim, ditolak, atau belum punya kelompok
        $belumMagang = max(0, $totalStudents - $akanMelaksanakan - $sedangMengajukan);

        return [
            [
                'status' => 'belum_magang',
                'name' => 'Belum Magang',
                'y' => $belumMagang,
                'color' => '#ef4444', // Red 500
            ],
            [
                'status' => 'sedang_mengajukan',
                'name' => 'Sedang Mengajukan',
                'y' => $sedangMengajukan,
                'color' => '#eab308', // Yellow 500
            ],
            [
                'status' => 'akan_melaksanakan',
                'name' => 'Akan/Melaksanakan Magang',
                'y' => $akanMelaksanakan,
                'color' => '#10b981', // Emerald 500
            ],
        ];
    }
}
