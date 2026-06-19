# Implementation Plan - Internship Group Creation and Joining (with Approvals)

We will implement the backend logic, database models, and frontend UI (Google Meet style) for creating, joining, and approving membership in internship groups.

---

## User Review Required

> [!IMPORTANT]
> - We are introducing a new database table `group_join_requests` to manage approvals.
>  - The student dashboard UI will be located in a dedicated folder: `resources/js/pages/student/Index.vue` to maintain folder structure consistency.
> - The routes will be placed in a dedicated file `routes/groups.php` and loaded in `routes/web.php`.
> - Group join codes will be 10-character uppercase alphanumeric strings.

---

## Proposed Changes

### Database & Models

#### [NEW] [create_group_join_requests_table](file:///home/realitaa/code/maganghub/database/migrations/2026_06_19_000005_create_group_join_requests_table.php)
- Columns:
  - `id` (Primary Key)
  - `group_id` (foreignId referencing `internship_groups.id`, cascade on delete)
  - `user_id` (foreignId referencing `users.id`, cascade on delete)
  - `status` (string, default: `'pending'`) - States: `pending`, `approved`, `rejected`
  - `timestamps`
- Unique constraint on `[user_id]` for `'pending'` status (checked in application code / unique constraints) or simply `[group_id, user_id]` to prevent duplicate requests. Let's make `[group_id, user_id]` unique.

#### [NEW] [GroupJoinRequest.php](file:///home/realitaa/code/maganghub/app/Models/GroupJoinRequest.php)
- Define `GroupJoinRequest` model with attributes: `group_id`, `user_id`, `status`.
- Relationships:
  - `group()`: `BelongsTo` (InternshipGroup)
  - `user()`: `BelongsTo` (User)

#### [NEW] [InternshipGroup.php](file:///home/realitaa/code/maganghub/app/Models/InternshipGroup.php)
- Define `InternshipGroup` model with attributes: `leader_id`, `code`, `status`.
- Relationships:
  - `leader()`: `BelongsTo` (User)
  - `memberships()`: `HasMany` (GroupMembership)
  - `members()`: `HasManyThrough` (User, via `GroupMembership`)
  - `joinRequests()`: `HasMany` (GroupJoinRequest)
  - `submissions()`: `HasMany` (InternshipSubmission)

#### [NEW] [GroupMembership.php](file:///home/realitaa/code/maganghub/app/Models/GroupMembership.php)
- Define `GroupMembership` model with attributes: `group_id`, `user_id`.
- Relationships:
  - `group()`: `BelongsTo` (InternshipGroup)
  - `user()`: `BelongsTo` (User)

#### [NEW] [InternshipSubmission.php](file:///home/realitaa/code/maganghub/app/Models/InternshipSubmission.php)
- Define `InternshipSubmission` model.

#### [NEW] [SubmissionMembership.php](file:///home/realitaa/code/maganghub/app/Models/SubmissionMembership.php)
- Define `SubmissionMembership` model.

#### [MODIFY] [User.php](file:///home/realitaa/code/maganghub/app/Models/User.php)
- Add relationships:
  - `ledGroups()`: `HasMany` (InternshipGroup)
  - `groupMembership()`: `HasOne` (GroupMembership)
  - `joinRequests()`: `HasMany` (GroupJoinRequest)

---

### Services & Controllers

#### [NEW] [InternshipGroupService.php](file:///home/realitaa/code/maganghub/app/Services/InternshipGroupService.php)
- Business logic:
  - `createGroup(User $user)`: Checks if student is in a group/has pending request. Generates 10-char uppercase alphanumeric code, creates group, makes user leader & member.
  - `requestToJoin(User $user, string $code)`: Validates code. Checks if user is in a group or has active pending request. Creates a `'pending'` `GroupJoinRequest`.
  - `cancelJoinRequest(User $user, GroupJoinRequest $request)`: Allows student to cancel their own pending request.
  - `approveJoinRequest(User $leader, GroupJoinRequest $request)`: Validates $leader is group leader and status is `'forming'`. Creates `GroupMembership` for requesting student, marks request as `'approved'`, and deletes any other pending requests for that user.
  - `rejectJoinRequest(User $leader, GroupJoinRequest $request)`: Validates $leader is group leader and status is `'forming'`. Marks request as `'rejected'`.
  - `leaveGroup(User $user)`: Member leaves group (forming or company_rejected status).
  - `disbandGroup(User $user, InternshipGroup $group)`: Leader disbands group (forming or company_rejected status).

#### [NEW] [DashboardController.php](file:///home/realitaa/code/maganghub/app/Http/Controllers/DashboardController.php)
- Render dashboard based on role:
  - Student:
    - Get active `group` (with leader, members, pending join requests if user is leader).
    - If no group, get active pending `joinRequest` (with group detail).
    - Render `student/Dashboard` page.
  - Operator/Admin:
    - Render standard `Dashboard` page.

#### [NEW] [InternshipGroupController.php](file:///home/realitaa/code/maganghub/app/Http/Controllers/InternshipGroupController.php)
- Handle endpoints: `store`, `join`, `leave`, `destroy`, `cancelRequest`, `approveRequest`, `rejectRequest`.
- Use `Inertia::flash` for toast notifications.

---

### Routing

#### [NEW] [groups.php](file:///home/realitaa/code/maganghub/routes/groups.php)
- Define dedicated group routes:
  - POST `/groups`
  - POST `/groups/join`
  - DELETE `/groups/join-requests/{request}`
  - POST `/groups/join-requests/{request}/approve`
  - POST `/groups/join-requests/{request}/reject`
  - POST `/groups/leave`
  - DELETE `/groups/{group}`

#### [MODIFY] [web.php](file:///home/realitaa/code/maganghub/routes/web.php)
- Register `Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');`
- Register `Route::get('home', [DashboardController::class, 'index'])->name('home');`
- Require `groups.php` inside auth middleware: `require __DIR__.'/groups.php';`

---

### Frontend Views

#### [NEW] [Index.vue](file:///home/realitaa/code/maganghub/resources/js/pages/student/Index.vue)
- Student home dashboard:
  - **No group, no pending request**: Google Meet-style UI.
    - Left column: Greeting, title, "Buat Kelompok Baru" button, code input, "Minta Gabung" button.
    - Right column: Premium card with rotation/tips.
  - **Pending request**: Showing loading state card "Menunggu Persetujuan Ketua Kelompok [Code]". Button to "Batalkan Permintaan".
  - **In group**:
    - Display Group Status and Group Code.
    - If leader: list pending join requests with "Terima" and "Tolak" buttons.
    - List members, indicating leader.
    - Disband (leader) or Leave (member) button.
  - **Query param `code`**:
    - If URL contains `?code=XYZ` and user has no group/pending request, auto-open a join confirmation modal.

---

## Verification Plan

### Comprehensive Test Cases

We will create a Pest test file: [InternshipGroupTest.php](file:///home/realitaa/code/maganghub/tests/Feature/InternshipGroupTest.php)

#### 1. Dashboard Routing & Loading
- **Student dashboard without group**: Returns `group = null` and `pendingJoinRequest = null`.
- **Student dashboard with pending request**: Returns `pendingJoinRequest` with group data.
- **Student dashboard with group**: Returns `group` with leader and members.
- **Group leader dashboard with pending requests**: Returns `group` and list of pending `joinRequests`.

#### 2. Creating a Group
- **Success Case**: Student creates group. Assert: Redirect, group created, 10-char uppercase code, student is leader and member, toast success flashed.
- **Failure Case**: Student already in a group cannot create another. Assert: Redirect, error toast.
- **Failure Case - Pending request**: Student with pending request cannot create group.

#### 3. Requesting to Join
- **Success Case**: Student requests to join. Assert: Redirect, `group_join_requests` has pending record, toast success flashed.
- **Success Case - Multiple Requests Cancelled on Acceptance**: Student sends requests to Group A, Group B, and Group C. When Group B leader approves, requests to Group A and Group C are automatically cancelled (status `'cancelled'` or deleted). Assert: `group_join_requests` only has the approved Group B record remaining, and student has a membership only in Group B.
- **Failure Case - Already in group**: Student in group cannot request to join.
- **Failure Case - Duplicate request**: Student with pending request to the same group cannot send another.
- **Failure Case - Locked Group**: Student cannot request to join if group status is not `'forming'`.

#### 4. Canceling a Join Request
- **Success Case**: Student cancels their own pending request. Assert: Redirect, request record deleted, toast success.
- **Failure Case**: Student cannot cancel someone else's request. Assert: Forbidden/Error.

#### 5. Approving a Join Request
- **Success Case**: Leader approves request. Assert: Redirect, request status `'approved'` (or deleted), member added to `group_memberships`, all other pending requests from same student cancelled, toast success.
- **Failure Case - Non-leader approval**: Member or other user cannot approve request. Assert: Forbidden.
- **Failure Case - Race Condition (Student Already in Group)**: Student sends join requests to two groups. Group A leader approves, student is now in Group A. Group B leader then tries to approve the still-visible request (stale UI). Assert: Request is rejected gracefully with an error toast, student is NOT added to Group B, student's membership in Group A is unaffected.

#### 6. Rejecting a Join Request
- **Success Case**: Leader rejects request. Assert: Redirect, request status `'rejected'`, member NOT added, toast success.
- **Failure Case - Non-leader rejection**: Non-leader cannot reject. Assert: Forbidden.

#### 7. Leaving & Disbanding Groups
- Test that members can leave / leaders can disband under appropriate statuses (`forming`, `company_rejected`), and cannot when status is locked (`submitted`, etc.).

### Verification Execution
- Run `vendor/bin/pest` to verify all tests.
- Run `vendor/bin/pint --dirty --format agent` to format code.
