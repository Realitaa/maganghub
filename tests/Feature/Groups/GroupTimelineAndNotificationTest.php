<?php

use App\Enums\GroupTimelineType;
use App\Models\GroupTimeline;
use App\Models\User;
use App\Notifications\JoinRequestAcceptedNotification;
use App\Notifications\JoinRequestRejectedNotification;
use App\Services\InternshipGroupService;
use App\Services\InternshipReviewService;
use App\Services\InternshipSubmissionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake();
    Storage::fake('public');
    setupGroupTimelineFakeTemplate();
    \Illuminate\Support\Carbon::setTestNow('2026-06-21');
});

afterEach(function () {
    \Illuminate\Support\Carbon::setTestNow();
});

function setupGroupTimelineFakeTemplate(): void
{
    $tempFile = tempnam(sys_get_temp_dir(), 'test_docx_');
    $zip = new ZipArchive;
    $zip->open($tempFile, ZipArchive::CREATE);
    $zip->addFromString('word/document.xml', '<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"><w:body><w:p><w:r><w:t>{{name}} {{nim}} {{semester}} {{phone}} {{email}} {{number}} {{today}} {{company_name}} {{start_date}} {{end_date}} {{calculateDuration}} {{field_of_interest}} {{division ? Division : field_of_interest}}</w:t></w:r></w:p><w:sectPr><w:pgSz w:w="12240" w:h="15840"/></w:sectPr></w:body></w:document>');
    $zip->close();
    $dummyDocxContent = file_get_contents($tempFile);
    unlink($tempFile);

    Storage::put('templates/letter_template.docx', $dummyDocxContent);
}

test('accepting a join request sends JoinRequestAcceptedNotification', function () {
    Notification::fake();

    $leader = User::factory()->create(['role' => 'student']);
    $student = User::factory()->create(['role' => 'student']);

    $groupService = app(InternshipGroupService::class);
    $group = $groupService->createGroup($leader);

    $request = $groupService->requestToJoin($student, $group->code);

    $groupService->approveJoinRequest($leader, $request);

    Notification::assertSentTo(
        $student,
        JoinRequestAcceptedNotification::class,
        function ($notification) use ($group, $leader, $student) {
            $data = $notification->toArray($student);

            return $data['group_id'] === $group->id &&
                $data['group_name'] === $leader->name &&
                $data['leader_name'] === $leader->name &&
                str_contains($data['message'], 'diterima');
        }
    );
});

test('rejecting a join request sends JoinRequestRejectedNotification', function () {
    Notification::fake();

    $leader = User::factory()->create(['role' => 'student']);
    $student = User::factory()->create(['role' => 'student']);

    $groupService = app(InternshipGroupService::class);
    $group = $groupService->createGroup($leader);

    $request = $groupService->requestToJoin($student, $group->code);

    $groupService->rejectJoinRequest($leader, $request);

    Notification::assertSentTo(
        $student,
        JoinRequestRejectedNotification::class,
        function ($notification) use ($group, $leader, $student) {
            $data = $notification->toArray($student);

            return $data['group_id'] === $group->id &&
                $data['group_name'] === $leader->name &&
                str_contains($data['message'], 'ditolak');
        }
    );
});

test('submitting a proposal records SUBMISSION_CREATED in group timeline', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $student = User::factory()->create(['role' => 'student']);

    $groupService = app(InternshipGroupService::class);
    $group = $groupService->createGroup($leader);

    // Add student to group (needs minimal 2 members)
    $request = $groupService->requestToJoin($student, $group->code);
    $groupService->approveJoinRequest($leader, $request);

    $submissionService = app(InternshipSubmissionService::class);

    $data = [
        'company_name' => 'PT Test',
        'company_address' => 'Test Address',
        'company_contact' => '08123',
        'field_of_interest' => 'Web Dev',
        'company_type' => 'Multinasional',
        'working_model' => 'Hybrid',
        'start_date' => '2026-07-01',
        'end_date' => '2026-10-01',
    ];

    $submissionService->submitProposal($leader, $data);

    $timeline = GroupTimeline::where('group_id', $group->id)->first();

    expect($timeline)->not->toBeNull()
        ->and($timeline->type)->toBe(GroupTimelineType::SubmissionCreated)
        ->and($timeline->title)->toContain('Pengajuan magang berhasil');
});

test('approving/rejecting submission records SUBMISSION_APPROVED and SUBMISSION_REJECTED in group timeline', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $student = User::factory()->create(['role' => 'student']);

    $groupService = app(InternshipGroupService::class);
    $group = $groupService->createGroup($leader);

    $request = $groupService->requestToJoin($student, $group->code);
    $groupService->approveJoinRequest($leader, $request);

    $submissionService = app(InternshipSubmissionService::class);

    $data = [
        'company_name' => 'PT Test',
        'company_address' => 'Test Address',
        'company_contact' => '08123',
        'field_of_interest' => 'Web Dev',
        'company_type' => 'Multinasional',
        'working_model' => 'Hybrid',
        'start_date' => '2026-07-01',
        'end_date' => '2026-10-01',
    ];

    $submission = $submissionService->submitProposal($leader, $data);

    $reviewService = app(InternshipReviewService::class);

    // Reject first
    $reviewService->rejectSubmission($submission, 'Wrong document format');

    $timeline = GroupTimeline::where('group_id', $group->id)
        ->where('type', GroupTimelineType::SubmissionRejected)
        ->first();

    expect($timeline)->not->toBeNull()
        ->and($timeline->title)->toContain('ditolak oleh operator')
        ->and($timeline->title)->toContain('Wrong document format');

    // Submit again (creates a new submission since previous was rejected)
    $newSubmission = $submissionService->submitProposal($leader, $data);

    // Approve
    $reviewService->approveSubmission($newSubmission);

    $timelineApprove = GroupTimeline::where('group_id', $group->id)
        ->where('type', GroupTimelineType::SubmissionApproved)
        ->first();

    expect($timelineApprove)->not->toBeNull()
        ->and($timelineApprove->title)->toContain('diterima oleh operator');
});

test('marking as applying records APPLICATION_LETTER_PRINTED in group timeline', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $student = User::factory()->create(['role' => 'student']);

    $groupService = app(InternshipGroupService::class);
    $group = $groupService->createGroup($leader);

    $request = $groupService->requestToJoin($student, $group->code);
    $groupService->approveJoinRequest($leader, $request);

    $submissionService = app(InternshipSubmissionService::class);

    $data = [
        'company_name' => 'PT Test',
        'company_address' => 'Test Address',
        'company_contact' => '08123',
        'field_of_interest' => 'Web Dev',
        'company_type' => 'Multinasional',
        'working_model' => 'Hybrid',
        'start_date' => '2026-07-01',
        'end_date' => '2026-10-01',
    ];

    $submission = $submissionService->submitProposal($leader, $data);

    $reviewService = app(InternshipReviewService::class);
    $reviewService->approveSubmission($submission);

    $reviewService->markAsApplying($submission);

    $timeline = GroupTimeline::where('group_id', $group->id)
        ->where('type', GroupTimelineType::ApplicationLetterPrinted)
        ->first();

    expect($timeline)->not->toBeNull()
        ->and($timeline->title)->toContain('Surat pengajuan magangmu sudah dicetak');
});

test('uploading reply records COMPANY_REPLY_UPLOADED in group timeline', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $student = User::factory()->create(['role' => 'student']);

    $groupService = app(InternshipGroupService::class);
    $group = $groupService->createGroup($leader);

    $request = $groupService->requestToJoin($student, $group->code);
    $groupService->approveJoinRequest($leader, $request);

    $submissionService = app(InternshipSubmissionService::class);

    $data = [
        'company_name' => 'PT Test',
        'company_address' => 'Test Address',
        'company_contact' => '08123',
        'field_of_interest' => 'Web Dev',
        'company_type' => 'Multinasional',
        'working_model' => 'Hybrid',
        'start_date' => '2026-07-01',
        'end_date' => '2026-10-01',
    ];

    $submission = $submissionService->submitProposal($leader, $data);

    $reviewService = app(InternshipReviewService::class);
    $reviewService->approveSubmission($submission);
    $reviewService->markAsApplying($submission);

    // Call uploadResponse on controller
    $file = UploadedFile::fake()->create('response.pdf', 100, 'application/pdf');

    $response = $this->actingAs($leader)
        ->post(route('groups.submissions.upload-response', $submission), [
            'file' => $file,
        ]);

    $response->assertRedirect();

    $timeline = GroupTimeline::where('group_id', $group->id)
        ->where('type', GroupTimelineType::CompanyReplyUploaded)
        ->first();

    expect($timeline)->not->toBeNull()
        ->and($timeline->title)->toContain('surat balasan perusahaan');
});

test('processing placement decision records ADMINISTRATION_COMPLETED in group timeline', function () {
    $leader = User::factory()->create(['role' => 'student']);
    $student = User::factory()->create(['role' => 'student']);

    $groupService = app(InternshipGroupService::class);
    $group = $groupService->createGroup($leader);

    $request = $groupService->requestToJoin($student, $group->code);
    $groupService->approveJoinRequest($leader, $request);

    $submissionService = app(InternshipSubmissionService::class);

    $data = [
        'company_name' => 'PT Test',
        'company_address' => 'Test Address',
        'company_contact' => '08123',
        'field_of_interest' => 'Web Dev',
        'company_type' => 'Multinasional',
        'working_model' => 'Hybrid',
        'start_date' => '2026-07-01',
        'end_date' => '2026-10-01',
    ];

    $submission = $submissionService->submitProposal($leader, $data);

    $reviewService = app(InternshipReviewService::class);
    $reviewService->approveSubmission($submission);
    $reviewService->markAsApplying($submission);

    // Decision: all_accepted
    $reviewService->processCompanyDecision($submission, 'all_accepted');

    $timeline = GroupTimeline::where('group_id', $group->id)
        ->where('type', GroupTimelineType::AdministrationCompleted)
        ->first();

    expect($timeline)->not->toBeNull()
        ->and($timeline->title)->toContain('Administrasi magang kelompokmu sudah selesai');
});
