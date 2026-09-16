<?php

use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Models\User;
use App\Services\LandingStatisticsService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('students are redirected from dashboard to home', function () {
    $student = User::factory()->create(['role' => 'student']);
    $this->actingAs($student);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('home'));
});

test('administrators can access the dashboard and get HTTP 200', function () {
    $admin = User::factory()->create(['role' => 'administrator']);
    $this->actingAs($admin);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('summary')
        ->has('operational')
        ->has('publicChart')
    );
});

test('operators can access the dashboard and get HTTP 200', function () {
    $operator = User::factory()->create(['role' => 'operator']);
    $this->actingAs($operator);

    $response = $this->get(route('dashboard'));
    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('summary')
        ->has('operational')
        ->has('publicChart')
    );
});

test('summary statistics are correctly calculated', function () {
    Cache::flush();

    // 5 students
    $students = User::factory()->count(5)->create(['role' => 'student']);
    // 1 administrator
    $admin = User::factory()->create(['role' => 'administrator']);

    // 3 groups
    $group1 = InternshipGroup::factory()->create(['leader_id' => $students[0]->id, 'status' => 'submitted']);
    $group2 = InternshipGroup::factory()->create(['leader_id' => $students[1]->id, 'status' => 'accepted']);
    $group3 = InternshipGroup::factory()->create(['leader_id' => $students[2]->id, 'status' => 'forming']);

    // Submissions:
    // 2 submitted, 1 accepted, 1 rejected
    // Companies: Google, Google, Microsoft, Tokopedia -> 3 unique companies
    InternshipSubmission::factory()->create([
        'group_id' => $group1->id,
        'status' => 'submitted',
        'company_name' => 'Google',
    ]);
    InternshipSubmission::factory()->create([
        'group_id' => $group2->id,
        'status' => 'accepted',
        'company_name' => 'Google', // duplicate name
    ]);
    InternshipSubmission::factory()->create([
        'group_id' => $group3->id,
        'status' => 'submitted',
        'company_name' => 'Microsoft',
    ]);
    InternshipSubmission::factory()->create([
        'group_id' => $group1->id,
        'status' => 'rejected',
        'company_name' => 'Tokopedia',
    ]);

    $this->actingAs($admin);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('summary.total_students', 5)
        ->where('summary.total_groups', 3)
        ->where('summary.pending_submissions', 2) // only status = 'submitted'
        ->where('summary.total_companies', 3)     // Google, Microsoft, Tokopedia
    );
});

test('operational overview returns correct counts and actual domain group statuses', function () {
    Cache::flush();

    $admin = User::factory()->create(['role' => 'administrator']);
    $students = User::factory()->count(4)->create(['role' => 'student']);

    $group1 = InternshipGroup::factory()->create(['leader_id' => $students[0]->id, 'status' => 'forming']);
    $group2 = InternshipGroup::factory()->create(['leader_id' => $students[1]->id, 'status' => 'submitted']);
    $group3 = InternshipGroup::factory()->create(['leader_id' => $students[2]->id, 'status' => 'loa_review']);

    // 1 pending submission (submitted)
    InternshipSubmission::factory()->create([
        'group_id' => $group2->id,
        'status' => 'submitted',
    ]);

    // 1 preparation awaiting review (loa_review)
    InternshipSubmission::factory()->create([
        'group_id' => $group3->id,
        'status' => 'loa_review',
    ]);

    $this->actingAs($admin);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('operational.pending_submissions', 1)
        ->where('operational.waiting_preparations', 1)
        ->where('operational.total_groups', 3)
        ->has('operational.group_status_distribution', 3)
        ->where('operational.group_status_distribution.0.status', 'forming')
        ->where('operational.group_status_distribution.0.name', 'Membentuk Kelompok')
        ->where('operational.group_status_distribution.1.status', 'submitted')
        ->where('operational.group_status_distribution.1.name', 'Pengajuan Dikirim')
        ->where('operational.group_status_distribution.2.status', 'loa_review')
        ->where('operational.group_status_distribution.2.name', 'Review Balasan Perusahaan')
    );
});

test('public chart has exactly four categories with proper data', function () {
    Cache::flush();

    $admin = User::factory()->create(['role' => 'administrator']);
    $students = User::factory()->count(8)->create(['role' => 'student']);

    // Group 1: accepted at Multinational
    $group1 = InternshipGroup::factory()->create(['leader_id' => $students[0]->id, 'status' => 'accepted']);
    InternshipSubmission::factory()->create([
        'group_id' => $group1->id,
        'status' => 'accepted',
        'company_name' => 'Amazon',
        'company_type' => 'Perusahaan Multinasional',
    ]);
    foreach ($students->take(2) as $s) {
        $group1->memberships()->create(['user_id' => $s->id, 'status' => 'active', 'role' => 'member']);
    }

    // Group 2: accepted at National
    $group2 = InternshipGroup::factory()->create(['leader_id' => $students[2]->id, 'status' => 'accepted']);
    InternshipSubmission::factory()->create([
        'group_id' => $group2->id,
        'status' => 'accepted',
        'company_name' => 'Telkom',
        'company_type' => 'Perusahaan Nasional',
    ]);
    $group2->memberships()->create(['user_id' => $students[2]->id, 'status' => 'active', 'role' => 'leader']);

    // Group 3: accepted at Startup Teknologi
    $group3 = InternshipGroup::factory()->create(['leader_id' => $students[3]->id, 'status' => 'accepted']);
    InternshipSubmission::factory()->create([
        'group_id' => $group3->id,
        'status' => 'accepted',
        'company_name' => 'Gojek',
        'company_type' => 'Startup Teknologi',
    ]);
    $group3->memberships()->create(['user_id' => $students[3]->id, 'status' => 'active', 'role' => 'leader']);

    // Remaining students (4) haven't done internships
    $this->actingAs($admin);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('publicChart.pie_chart')
        ->where('publicChart.pie_chart.multinational', 2)
        ->where('publicChart.pie_chart.national', 1)
        ->where('publicChart.pie_chart.startup', 1)
        ->where('publicChart.pie_chart.havenot', 4)
    );
});

test('opening dashboard re-generates and updates public chart cache for 6 hours', function () {
    Cache::flush();
    $admin = User::factory()->create(['role' => 'administrator']);

    expect(Cache::has(LandingStatisticsService::CACHE_KEY))->toBeFalse();

    $this->actingAs($admin);
    $response = $this->get(route('dashboard'));

    $response->assertOk();

    // Cache must now be populated
    expect(Cache::has(LandingStatisticsService::CACHE_KEY))->toBeTrue();
    $cached = Cache::get(LandingStatisticsService::CACHE_KEY);

    expect($cached)->toHaveKeys(['total_students', 'total_groups', 'total_companies', 'pie_chart', 'updated_at']);
    expect($cached['pie_chart'])->toHaveKeys(['multinational', 'national', 'startup', 'havenot']);

    // Verify updated_at is a valid recent timestamp
    $updatedAt = Carbon::parse($cached['updated_at']);
    expect($updatedAt->diffInSeconds(now()))->toBeLessThan(10);
});

test('landing page reads statistics from the 6-hour cache', function () {
    Cache::flush();

    $cachedData = [
        'total_students' => 99,
        'total_groups' => 20,
        'total_companies' => 15,
        'pie_chart' => [
            'multinational' => 10,
            'national' => 20,
            'startup' => 15,
            'havenot' => 54,
        ],
        'updated_at' => now()->toIso8601String(),
    ];

    Cache::put(LandingStatisticsService::CACHE_KEY, $cachedData, now()->addHours(6));

    $response = $this->get('/');

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Welcome')
        ->where('statistics.total_students', 99)
        ->where('statistics.total_groups', 20)
        ->where('statistics.total_companies', 15)
        ->where('statistics.pie_chart.multinational', 10)
        ->where('statistics.pie_chart.national', 20)
        ->where('statistics.pie_chart.startup', 15)
        ->where('statistics.pie_chart.havenot', 54)
    );
});

test('student internship status categorizes students into 3 progress buckets', function () {
    Cache::flush();

    $admin = User::factory()->create(['role' => 'administrator']);
    $students = User::factory()->count(10)->create(['role' => 'student']);

    // Group 1: accepted -> 3 students (Akan/Melaksanakan Magang)
    $group1 = InternshipGroup::factory()->create(['leader_id' => $students[0]->id, 'status' => 'accepted']);
    foreach ($students->take(3) as $s) {
        $group1->memberships()->create(['user_id' => $s->id, 'status' => 'active', 'role' => 'member']);
    }

    // Group 2: applying -> 2 students (Sedang Mengajukan)
    $group2 = InternshipGroup::factory()->create(['leader_id' => $students[3]->id, 'status' => 'applying']);
    foreach ($students->skip(3)->take(2) as $s) {
        $group2->memberships()->create(['user_id' => $s->id, 'status' => 'active', 'role' => 'member']);
    }

    // Group 3: forming -> 2 students (Belum Magang)
    $group3 = InternshipGroup::factory()->create(['leader_id' => $students[5]->id, 'status' => 'forming']);
    foreach ($students->skip(5)->take(2) as $s) {
        $group3->memberships()->create(['user_id' => $s->id, 'status' => 'active', 'role' => 'member']);
    }

    // Remaining 3 students have no group (Belum Magang)
    // Total Belum Magang = 2 + 3 = 5

    $this->actingAs($admin);
    $response = $this->get(route('dashboard'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->has('operational.student_internship_status', 3)
        ->where('operational.student_internship_status.0.status', 'belum_magang')
        ->where('operational.student_internship_status.0.name', 'Belum Magang')
        ->where('operational.student_internship_status.0.y', 5)
        ->where('operational.student_internship_status.0.color', '#ef4444')
        ->where('operational.student_internship_status.1.status', 'sedang_mengajukan')
        ->where('operational.student_internship_status.1.name', 'Sedang Mengajukan')
        ->where('operational.student_internship_status.1.y', 2)
        ->where('operational.student_internship_status.1.color', '#eab308')
        ->where('operational.student_internship_status.2.status', 'akan_melaksanakan')
        ->where('operational.student_internship_status.2.name', 'Akan/Melaksanakan Magang')
        ->where('operational.student_internship_status.2.y', 3)
        ->where('operational.student_internship_status.2.color', '#10b981')
    );
});
