<?php

use App\Models\GroupMembership;
use App\Models\GroupTimeline;
use App\Models\InternshipGroup;
use App\Models\InternshipSubmission;
use App\Models\User;
use App\Notifications\GroupDisbandedNotification;
use App\Notifications\GroupStatusUpdatedNotification;
use App\Notifications\KickedFromGroupNotification;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
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
        $this->get(route('internships.groups.index'))->assertRedirect(route('login'));
    });

    it('prevents students from accessing the dashboard', function () {
        $student = User::factory()->create(['role' => 'student']);
        $this->actingAs($student)->get(route('internships.groups.index'))->assertForbidden();
    });

    it('allows operators to access the dashboard', function () {
        $operator = User::factory()->create(['role' => 'operator']);
        $this->actingAs($operator)->get(route('internships.groups.index'))->assertOk();
    });

    it('allows administrators to access the dashboard', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $this->actingAs($admin)->get(route('internships.groups.index'))->assertOk();
    });
});

describe('kelompok magang dashboard features', function () {
    beforeEach(function () {
        Carbon::setTestNow('2026-06-21');
    });

    afterEach(function () {
        Carbon::setTestNow();
    });

    it('displays all groups with correct computed status and sorting order', function () {
        $admin = User::factory()->create(['role' => 'administrator']);

        // Group 1: Selesai Magang (startDate & endDate in past)
        $groupSelesai = makeGroupWithSubmission('completed', 'Company Past', '2026-01-01', '2026-03-01', 'G-PAST');

        // Group 2: Segera Magang (startDate in future)
        $groupSegera = makeGroupWithSubmission('accepted', 'Company Future', '2026-07-01', '2026-09-01', 'G-FUT');

        // Group 3: Melaksanakan Magang (startDate in past, endDate in future)
        $groupMelaksanakan = makeGroupWithSubmission('internship_started', 'Company Present', '2026-06-01', '2026-08-01', 'G-PRES');

        $this->actingAs($admin)
            ->get(route('internships.groups.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('internships/groups/Index')
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
            ->get(route('internships.groups.index', ['search' => 'UniqueCompanyA']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('internships/groups/Index')
                ->has('groups', 1)
                ->where('groups.0.id', $group1->id)
            );

        // Search by group code
        $this->actingAs($admin)
            ->get(route('internships.groups.index', ['search' => 'G-CODEB']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('internships/groups/Index')
                ->has('groups', 1)
                ->where('groups.0.id', $group2->id)
            );

        // Search by leader name
        $leaderName = $group1->leader->name;
        $this->actingAs($admin)
            ->get(route('internships.groups.index', ['search' => $leaderName]))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('internships/groups/Index')
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
            ->get(route('internships.groups.index', ['status' => 'segera_magang']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('internships/groups/Index')
                ->has('groups', 1)
                ->where('groups.0.id', $groupSegera->id)
            );

        // Filter by 'melaksanakan_magang'
        $this->actingAs($admin)
            ->get(route('internships.groups.index', ['status' => 'melaksanakan_magang']))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('internships/groups/Index')
                ->has('groups', 1)
                ->where('groups.0.id', $groupMelaksanakan->id)
            );
    });
});

describe('kelompok magang detail and admin management actions', function () {
    beforeEach(function () {
        Storage::fake();
    });

    it('renders detail page for admin by code and by id', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $group = makeGroupWithSubmission('accepted', 'PT Inovasi', '2026-07-01', '2026-09-01', 'G-DETAIL');

        // Access via code
        $this->actingAs($admin)
            ->get(route('internships.groups.show', $group->code))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('internships/groups/Show')
                ->where('group.id', $group->id)
                ->where('group.code', $group->code)
            );

        // Access via numeric id
        $this->actingAs($admin)
            ->get('/internships/groups/'.$group->id)
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('internships/groups/Show')
                ->where('group.id', $group->id)
                ->where('group.code', $group->code)
            );
    });

    it('allows admin to kick a member with reason and notifies user and group timeline', function () {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'administrator']);
        $leader = User::factory()->create(['role' => 'student']);
        $member = User::factory()->create(['role' => 'student']);

        $group = InternshipGroup::factory()->create(['leader_id' => $leader->id, 'status' => 'forming']);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $member->id]);

        $this->actingAs($admin)
            ->post(route('internships.groups.kick', $group->code), [
                'user_id' => $member->id,
                'reason' => 'Mengundurkan diri karena magang mandiri.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        expect(GroupMembership::where('group_id', $group->id)->where('user_id', $member->id)->exists())->toBeFalse();

        // Check notification to kicked user
        Notification::assertSentTo($member, KickedFromGroupNotification::class, function ($n) {
            return str_contains($n->reason, 'magang mandiri');
        });

        // Check timeline event
        expect(GroupTimeline::where('group_id', $group->id)->where('type', 'MEMBER_KICKED')->exists())->toBeTrue();
    });

    it('allows admin to change group leader and records timeline event', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $leader = User::factory()->create(['role' => 'student']);
        $member = User::factory()->create(['role' => 'student']);

        $group = InternshipGroup::factory()->create(['leader_id' => $leader->id, 'status' => 'forming']);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $member->id]);

        $this->actingAs($admin)
            ->post(route('internships.groups.change-leader', $group->code), [
                'new_leader_id' => $member->id,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        expect($group->fresh()->leader_id)->toBe($member->id);
        expect(GroupTimeline::where('group_id', $group->id)->where('type', 'LEADER_CHANGED')->exists())->toBeTrue();
    });

    it('allows admin to replace application letter and response file', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $group = makeGroupWithSubmission('accepted', 'PT Maju', '2026-07-01', '2026-09-01');

        $letterFile = UploadedFile::fake()->create('surat_permohonan.pdf', 500, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('internships.groups.replace-letter', $group->code), [
                'file' => $letterFile,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $submission = $group->activeSubmission()->first();
        expect($submission->letter_path)->not->toBeNull();
        Storage::disk('local')->assertExists($submission->letter_path);

        $responseFile = UploadedFile::fake()->create('surat_balasan.pdf', 400, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('internships.groups.replace-response', $group->code), [
                'file' => $responseFile,
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $submission->refresh();
        expect($submission->company_response_path)->not->toBeNull();
        Storage::disk('local')->assertExists($submission->company_response_path);
    });

    it('prevents kicking the group leader and keeps group integrity', function () {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'administrator']);
        $leader = User::factory()->create(['role' => 'student']);
        $member = User::factory()->create(['role' => 'student']);

        $group = InternshipGroup::factory()->create(['leader_id' => $leader->id, 'status' => 'forming']);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $member->id]);

        $this->actingAs($admin)
            ->post(route('internships.groups.kick', $group->code), [
                'user_id' => $leader->id,
                'reason' => 'Mencoba mengeluarkan ketua.',
            ])
            ->assertRedirect();

        // Leader is still in group and remains leader
        expect(GroupMembership::where('group_id', $group->id)->where('user_id', $leader->id)->exists())->toBeTrue();
        expect($group->fresh()->leader_id)->toBe($leader->id);
        Notification::assertNothingSent();
    });

    it('validates file upload constraints when replacing letter and response', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $group = makeGroupWithSubmission('accepted', 'PT Maju', '2026-07-01', '2026-09-01');

        // Invalid file format (e.g. .exe)
        $invalidFile = UploadedFile::fake()->create('malicious.exe', 500, 'application/x-msdownload');

        $this->actingAs($admin)
            ->post(route('internships.groups.replace-letter', $group->code), [
                'file' => $invalidFile,
            ])
            ->assertSessionHasErrors('file');

        // Oversized file (> 10MB)
        $oversizedFile = UploadedFile::fake()->create('huge.pdf', 15000, 'application/pdf');

        $this->actingAs($admin)
            ->post(route('internships.groups.replace-response', $group->code), [
                'file' => $oversizedFile,
            ])
            ->assertSessionHasErrors('file');
    });

    it('allows admin to update internship submission data with validation', function () {
        $admin = User::factory()->create(['role' => 'administrator']);
        $group = makeGroupWithSubmission('submitted', 'PT Lama', '2026-07-01', '2026-09-01');

        // Missing required fields validation
        $this->actingAs($admin)
            ->post(route('internships.groups.update-submission', $group->code), [])
            ->assertSessionHasErrors([
                'company_name',
                'company_address',
                'company_contact',
                'field_of_interest',
                'company_type',
                'working_model',
                'start_date',
                'end_date',
            ]);

        // Valid submission update
        $this->actingAs($admin)
            ->post(route('internships.groups.update-submission', $group->code), [
                'company_name' => 'PT Solusi Terbuka Nusantara',
                'company_address' => 'Gedung Cyber 2 Tower Lt. 15, Jakarta Selatan',
                'company_contact' => 'hr@solusiterbuka.id',
                'company_leader' => 'Bapak Ir. Budi Santoso, M.Kom.',
                'division' => 'Software Engineering',
                'field_of_interest' => 'Fullstack Web Development',
                'company_type' => 'Startup Teknologi',
                'working_model' => 'Hybrid',
                'start_date' => '2026-08-01',
                'end_date' => '2026-11-01',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $submission = $group->fresh()->activeSubmission;
        expect($submission->company_name)->toBe('PT Solusi Terbuka Nusantara');
        expect($submission->company_address)->toBe('Gedung Cyber 2 Tower Lt. 15, Jakarta Selatan');
        expect($submission->company_contact)->toBe('hr@solusiterbuka.id');
        expect($submission->company_leader)->toBe('Bapak Ir. Budi Santoso, M.Kom.');
        expect($submission->division)->toBe('Software Engineering');
        expect($submission->field_of_interest)->toBe('Fullstack Web Development');
        expect($submission->company_type)->toBe('Startup Teknologi');
        expect($submission->working_model)->toBe('Hybrid');
        expect($submission->start_date->format('Y-m-d'))->toBe('2026-08-01');
        expect($submission->end_date->format('Y-m-d'))->toBe('2026-11-01');
    });

    it('allows admin to update group status, dispatches notification to members, and records timeline', function () {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'administrator']);
        $leader = User::factory()->create(['role' => 'student']);
        $member = User::factory()->create(['role' => 'student']);

        $group = InternshipGroup::factory()->create(['leader_id' => $leader->id, 'status' => 'forming']);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $member->id]);

        $submission = InternshipSubmission::factory()->create([
            'group_id' => $group->id,
            'company_name' => 'PT Inovasi Digital',
            'status' => 'draft',
        ]);

        $this->actingAs($admin)
            ->post(route('internships.groups.update-status', $group->code), [
                'status' => 'accepted',
                'reason' => 'Percepatan penerimaan magang dari pihak kampus.',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        expect($group->fresh()->status)->toBe('accepted');
        expect($submission->fresh()->status)->toBe('accepted');

        // Timeline event recorded
        $timeline = GroupTimeline::where('group_id', $group->id)->where('type', 'STATUS_UPDATED')->first();
        expect($timeline)->not->toBeNull();
        expect($timeline->metadata['status'])->toBe('accepted');
        expect($timeline->metadata['reason'])->toBe('Percepatan penerimaan magang dari pihak kampus.');

        // Notification dispatched to members
        Notification::assertSentTo($leader, GroupStatusUpdatedNotification::class, function ($n) {
            return $n->newStatus === 'accepted' && str_contains($n->reason, 'Percepatan penerimaan');
        });
        Notification::assertSentTo($member, GroupStatusUpdatedNotification::class, function ($n) {
            return $n->newStatus === 'accepted';
        });
    });

    it('allows admin to disband a group, notifies members, and removes group and related data', function () {
        Notification::fake();

        $admin = User::factory()->create(['role' => 'administrator']);
        $leader = User::factory()->create(['role' => 'student']);
        $member = User::factory()->create(['role' => 'student']);

        $group = InternshipGroup::factory()->create(['leader_id' => $leader->id, 'status' => 'submitted']);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $leader->id]);
        GroupMembership::factory()->create(['group_id' => $group->id, 'user_id' => $member->id]);

        $submission = InternshipSubmission::factory()->create([
            'group_id' => $group->id,
            'company_name' => 'PT Bubar Jaya',
        ]);

        $groupId = $group->id;

        $this->actingAs($admin)
            ->delete(route('internships.groups.disband', $group->code), [
                'reason' => 'Kelompok dibubarkan karena mahasiswa mengajukan magang mandiri.',
            ])
            ->assertRedirect(route('internships.groups.index'))
            ->assertSessionHas('success');

        // Notification sent to all members
        Notification::assertSentTo($leader, GroupDisbandedNotification::class, function ($n) {
            return str_contains($n->reason, 'magang mandiri');
        });
        Notification::assertSentTo($member, GroupDisbandedNotification::class);

        // Group, memberships, and submissions deleted
        expect(InternshipGroup::find($groupId))->toBeNull();
        expect(GroupMembership::where('group_id', $groupId)->count())->toBe(0);
        expect(InternshipSubmission::where('group_id', $groupId)->count())->toBe(0);
    });
});
