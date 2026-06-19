# Walkthrough - Internship Group Feature

## Backend Changes

### New Migration
- [create_group_join_requests_table](file:///home/realitaa/code/maganghub/database/migrations/2026_06_18_221302_create_group_join_requests_table.php): Tracks join requests with `group_id`, `user_id`, `status` (pending/approved/rejected/cancelled), and a unique constraint on `[group_id, user_id]`.

### New Models
- [InternshipGroup](file:///home/realitaa/code/maganghub/app/Models/InternshipGroup.php): `leader_id`, `code` (10-char uppercase), `status`.
- [GroupMembership](file:///home/realitaa/code/maganghub/app/Models/GroupMembership.php): Tracks active members.
- [GroupJoinRequest](file:///home/realitaa/code/maganghub/app/Models/GroupJoinRequest.php): Tracks pending/approved/rejected join requests.
- [InternshipSubmission](file:///home/realitaa/code/maganghub/app/Models/InternshipSubmission.php): Stub for future submission workflow.
- [SubmissionMembership](file:///home/realitaa/code/maganghub/app/Models/SubmissionMembership.php): Snapshot of members at time of submission.

### Updated Model
- [User](file:///home/realitaa/code/maganghub/app/Models/User.php): Added `ledGroups()`, `groupMembership()`, `joinRequests()` relationships.

### New Service
- [InternshipGroupService](file:///home/realitaa/code/maganghub/app/Services/InternshipGroupService.php): All group business logic:
  - `createGroup` – creates group + makes user leader + first member
  - `requestToJoin` – sends pending join request
  - `cancelJoinRequest` – student cancels own request
  - `approveJoinRequest` – leader approves → creates membership → cancels all other pending requests from that student (race condition handled)
  - `rejectJoinRequest` – marks request as rejected
  - `leaveGroup` – non-leader leaves (forming/company_rejected only)
  - `disbandGroup` – leader disbands (forming/company_rejected only)

### New Controllers
- [DashboardController](file:///home/realitaa/code/maganghub/app/Http/Controllers/DashboardController.php): Routes students to `student/Index` with their group or pending requests; other roles get `Dashboard`.
- [InternshipGroupController](file:///home/realitaa/code/maganghub/app/Http/Controllers/InternshipGroupController.php): All group endpoints with `Inertia::flash` toast notifications.

### New Routes
- [routes/groups.php](file:///home/realitaa/code/maganghub/routes/groups.php): Dedicated group routes under auth middleware.
- [routes/web.php](file:///home/realitaa/code/maganghub/routes/web.php): Updated to use `DashboardController` and load `groups.php`.

## Frontend Changes

### New Page
- [student/Index.vue](file:///home/realitaa/code/maganghub/resources/js/pages/student/Index.vue): Student dashboard with 3 distinct states:
  1. **No group, no requests**: Google Meet-style UI with "Buat Kelompok" button and join code input.
  2. **Pending requests**: Shows all pending requests with cancel buttons.
  3. **In group**: Group code display + copy/share, member list with leader badge, pending join requests (leader only) with accept/reject, and disband/leave actions.
  - Deep link support: `?code=XXXXXXXXXX` triggers a join confirmation dialog automatically.

## Test Results
- **29/29** new tests pass in [InternshipGroupTest.php](file:///home/realitaa/code/maganghub/tests/Feature/InternshipGroupTest.php)
- **87/87** total tests pass (79 active + 8 skipped)
- Pint formatting: ✅ clean
