# Naming Convention

## Purpose

This document defines project-specific naming conventions.

It complements Laravel's own conventions instead of replacing them.

Whenever Laravel already establishes a naming convention, follow the framework.

---

# Core Principles

## Follow Framework Conventions

Prefer Laravel, PHP, Vue, and TypeScript naming conventions.

Do not invent custom naming styles.

Consistency is more important than personal preference.

---

## Names Should Describe Responsibilities

Names should describe what something is responsible for.

Avoid names based solely on implementation details.

Prefer:

* `UserManagementService`
* `UserCreateEditDialog`
* `PasswordUpdateRequest`

Avoid:

* `Helper`
* `Manager`
* `Processor`
* `Handler`
* `Utils`

unless they accurately describe the responsibility.

---

## Prefer Explicit Names

Prefer descriptive names over abbreviated names.

Good:

```text
selectedUser
currentClassroom
availableRoles
```

Avoid:

```text
selUser
cls
tmp
obj
```

except for very small local scopes.

---

# Backend

## Models

Models use singular PascalCase.

Examples:

```text
User
Classroom
Quiz
Submission
```

---

## Controllers

Controllers describe the managed resource.

Examples:

```text
UserController
ClassroomController
QuizController
```

Avoid including implementation details in controller names.

---

## Form Requests

Form Requests describe the operation they validate.

Examples:

```text
UserStoreRequest
UserUpdateRequest
PasswordUpdateRequest
```

---

## Services

Services describe the business workflow they implement.

Examples:

```text
UserManagementService
QuizSubmissionService
InvoiceGenerationService
```

Avoid generic names.

Examples to avoid:

```text
UserService
HelperService
GeneralService
```

unless the resource genuinely has only a single business service.

---

## Actions

Actions describe a single reusable operation.

Examples:

```text
ActivateUser
DeactivateUser
ImportUsers
GenerateCertificate
```

Actions should begin with a verb.

---

## Policies

Policies follow Laravel conventions.

Examples:

```text
UserPolicy
QuizPolicy
```

---

## Events, Jobs and Listeners

Follow Laravel's default naming conventions.

Names should clearly describe the event or operation.

---

# Frontend

## Pages

Pages represent workflows.

Use PascalCase.

Examples:

```text
Index.vue
Create.vue
Edit.vue
CreateEdit.vue
Dashboard.vue
```

---

## Components

Component names should describe their responsibility.

Examples:

```text
UserTable
UserCreateEditDialog
UserDeleteDialog
PageHeader
```

Avoid names based solely on visual appearance.

Examples:

```text
BlueCard
GreenButton
LeftPanel
```

unless appearance is the actual responsibility.

---

## Layouts

Layouts describe shared page structures.

Examples:

```text
AppLayout
AuthLayout
GuestLayout
```

---

## Composables

Composable names always begin with:

```text
use
```

Examples:

```text
useAppearance
useCurrentUrl
useDateFormatter
```

Composable names should describe reusable functionality.

---

## Types

Shared types describe domain concepts.

Examples:

```text
User
Quiz
NavigationItem
FlashToast
```

Avoid naming shared types after specific components.

---

## Library Integration

Files inside `lib` should be named after the library or functionality they configure.

Examples:

```text
dayjs.ts
flashToast.ts
utils.ts
```

---

# Variables

## Boolean Variables

Prefer names that naturally read as true or false.

Examples:

```text
isActive
isPublished
hasPermission
canEdit
shouldRefresh
```

---

## Collections

Collections should use plural names.

Examples:

```text
users
roles
permissions
notifications
```

Single models should remain singular.

Examples:

```text
user
role
permission
```

---

## Event Handlers

Event handler names should describe the action.

Examples:

```text
handleSubmit
handleDelete
handleActivation
```

Avoid generic names such as:

```text
click
submit
action
```

---

## Functions

Function names should describe what they do.

Prefer verbs.

Examples:

```text
generateInvoice()
activateUser()
calculateScore()
```

---

# Routes

Resource route files should use lowercase plural names.

Examples:

```text
users.php
classrooms.php
notifications.php
```

---

# Configuration

Configuration files follow Laravel conventions.

Examples:

```text
queue.php
cache.php
permission.php
```

---

# Database

Follow Laravel's conventions.

Examples:

Tables:

```text
users
classrooms
quiz_submissions
```

Pivot Tables:

```text
classroom_user
permission_role
```

Foreign Keys:

```text
user_id
classroom_id
```

---

# General Rule

Before naming anything, ask:

1. Does Laravel already define a convention?
2. Does the name describe its responsibility?
3. Is the name explicit and easy to understand?
4. Would another developer immediately understand its purpose?

If the answer to all four questions is "yes", the name is probably appropriate.
