<?php

namespace App\Services;

use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class LandingStatisticsService
{
    public const CACHE_KEY = 'landing.statistics';

    public const CACHE_TTL_HOURS = 6;

    /**
     * Get the landing page statistics.
     * Caches the result for 6 hours.
     *
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addHours(self::CACHE_TTL_HOURS), function () {
            return $this->generate();
        });
    }

    /**
     * Re-generate statistics, update cache with fresh data, and return it.
     *
     * @return array<string, mixed>
     */
    public function refresh(): array
    {
        $data = $this->generate();

        Cache::put(self::CACHE_KEY, $data, now()->addHours(self::CACHE_TTL_HOURS));

        return $data;
    }

    /**
     * Generate the statistics by running aggregation queries.
     *
     * @return array<string, mixed>
     */
    public function generate(): array
    {
        $totalStudents = User::where('role', 'student')->count();
        $totalGroups = InternshipGroup::count();

        $totalCompanies = InternshipSubmission::whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->distinct('company_name')
            ->count('company_name');

        // Calculate students accepted by company type
        $studentCountsByCompanyType = DB::table('group_memberships')
            ->join('internship_groups', 'group_memberships.group_id', '=', 'internship_groups.id')
            ->join('internship_submissions', 'internship_submissions.group_id', '=', 'internship_groups.id')
            ->where('group_memberships.status', 'active')
            ->whereIn('internship_submissions.status', ['accepted', 'partially_accepted', 'internship_started', 'completed'])
            ->groupBy('internship_submissions.company_type')
            ->select('internship_submissions.company_type', DB::raw('COUNT(DISTINCT group_memberships.user_id) as total'))
            ->pluck('total', 'company_type');

        $multinational = (int) $studentCountsByCompanyType->get('Perusahaan Multinasional', 0)
            + (int) $studentCountsByCompanyType->get('Multinasional', 0);
        $national = (int) $studentCountsByCompanyType->get('Perusahaan Nasional', 0)
            + (int) $studentCountsByCompanyType->get('Nasional', 0);
        $startup = (int) $studentCountsByCompanyType->get('Startup Teknologi', 0);

        $totalAccepted = $multinational + $national + $startup;

        // Prevent negative havenot just in case of data inconsistencies
        $havenot = max(0, $totalStudents - $totalAccepted);

        return [
            'total_students' => $totalStudents,
            'total_groups' => $totalGroups,
            'total_companies' => $totalCompanies,
            'pie_chart' => [
                'multinational' => $multinational,
                'national' => $national,
                'startup' => $startup,
                'havenot' => $havenot,
            ],
            'updated_at' => now()->toIso8601String(),
        ];
    }
}
