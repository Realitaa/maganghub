<?php

use App\Models\GroupMembership;
use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Models\User;
use Illuminate\Support\Carbon;
use Inertia\Testing\AssertableInertia as Assert;

function makeGroupWithSubmission(string $status, string $company, string $startDate, string $endDate, ?string $code = null): InternshipGroup
{
    $leader = User::factory()->create(['role' => 'student']);

    $group = InternshipGroup::factory()->create([
        'leader_id' => $leader->id,
        'status' => $status,
        'code' => $code ?? 'G-'.fake()->unique()->numerify('###'),
    ]);

    GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);

    $submission = InternshipSubmission::factory()->create([
        'group_id' => $group->id,
        'company_name' => $company,
        'start_date' => $startDate,
        'end_date' => $endDate,
        'status' => $status,
    ]);

    return $group;
}

describe('kelompok magang dashboard authorization', function () {
    it('redirects guest to login', function () {
        $this->get(route('review.groups.index'))->assertRedirect(route('login'));
    });

    it('prevents students from accessing the dashboard', function () {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->get(route('review.groups.index'))->assertForbidden();
    });

    it('allows operators to access the dashboard', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        $this->actingAs($operator)->get(route('review.groups.index'))->assertOk();
    });

    it('allows administrators to access the dashboard', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($admin)->get(route('review.groups.index'))->assertOk();
    });
});

describe('kelompok magang dashboard features', function () {
    beforeEach(function () {
        Carbon::setTestNow('2026-06-21');
    });

    afterEach(function () {
        Carbon::setTestNow();
    });

    it('displays groups with correct computed status and sorting order', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        // Group 1: Selesai Magang (startDate & endDate in past)
        $groupSelesai = makeGroupWithSubmission('completed', 'Company Past', '2026-01-01', '2026-03-01', 'G-PAST');

        // Group 2: Segera Magang (startDate in future)
        $groupSegera = makeGroupWithSubmission('accepted', 'Company Future', '2026-07-01', '2026-09-01', 'G-FUT');

        // Group 3: Melaksanakan Magang (startDate in past, endDate in future)
        $groupMelaksanakan = makeGroupWithSubmission('internship_started', 'Company Present', '2026-06-01', '2026-08-01', 'G-PRES');

        $this->actingAs($admin)
            ->get(route('review.groups.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('review/groups/Index')
                ->has('groups', 3)
                // Sorting order: Segera Magang (1), Melaksanakan Magang (2), Selesai Magang (3)
                ->where('groups.0.id', $groupSegera->id)
                ->where('groups.0.computed_status', 'segera_magang')
                ->where('groups.1.id', $groupMelaksanakan->id)
                ->where('groups.1.computed_status', 'melaksanakan_magang')
                ->where('groups.2.id', $groupSelesai->id)
                ->where('groups.2.computed_status', 'selesai_magang')
            );
    });

    it('filters groups by search query (code, leader name, company name)', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        $group1 = makeGroupWithSubmission('accepted', 'UniqueCompanyA', '2026-07-01', '2026-09-01', 'G-CODEA');
        $group2 = makeGroupWithSubmission('accepted', 'OtherCompany', '2026-07-01', '2026-09-01', 'G-CODEB');

        // Search by company
        $this->actingAs($admin)
            ->get(route('review.groups.index', ['search' => 'UniqueCompanyA']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('review/groups/Index')
                ->has('groups', 1)
                ->where('groups.0.id', $group1->id)
            );

        // Search by group code
        $this->actingAs($admin)
            ->get(route('review.groups.index', ['search' => 'G-CODEB']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('review/groups/Index')
                ->has('groups', 1)
                ->where('groups.0.id', $group2->id)
            );

        // Search by leader name
        $leaderName = $group1->leader->name;
        $this->actingAs($admin)
            ->get(route('review.groups.index', ['search' => $leaderName]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('review/groups/Index')
                ->has('groups', 1)
                ->where('groups.0.id', $group1->id)
            );
    });

    it('filters groups by computed status', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        $groupSegera = makeGroupWithSubmission('accepted', 'Company Future', '2026-07-01', '2026-09-01', 'G-FUT');
        $groupMelaksanakan = makeGroupWithSubmission('internship_started', 'Company Present', '2026-06-01', '2026-08-01', 'G-PRES');

        // Filter by 'segera_magang'
        $this->actingAs($admin)
            ->get(route('review.groups.index', ['status' => 'segera_magang']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('review/groups/Index')
                ->has('groups', 1)
                ->where('groups.0.id', $groupSegera->id)
            );

        // Filter by 'melaksanakan_magang'
        $this->actingAs($admin)
            ->get(route('review.groups.index', ['status' => 'melaksanakan_magang']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('review/groups/Index')
                ->has('groups', 1)
                ->where('groups.0.id', $groupMelaksanakan->id)
            );
    });
});
