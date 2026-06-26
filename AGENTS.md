<laravel-boost-guidelines>
=== .ai/README rules ===

# Engineering Guidelines

## Purpose

This documentation defines the engineering standards used throughout this project.

It does not teach Laravel, Vue, Inertia, or Pest.

Instead, it documents the engineering decisions that keep the project consistent over time, regardless of who implements the code.

These documents should be consulted before introducing new architecture, abstractions, or development patterns.

---

# Reading Order

Read the documents in the following order.

## 1. Development Philosophy

Defines the project's core engineering principles.

Read this first.

---

## 2. AI Workflow

Defines the standard development workflow for implementing a feature.

This document explains **how work should be approached** before writing code.

---

## 3. Backend Rules

Read when implementing:

* Laravel Controllers
* Requests
* Services
* Actions
* Policies
* Models
* Business Logic

---

## 4. Frontend Rules

Read when implementing:

* Vue Pages
* Components
* Layouts
* Composables
* Types
* UI responsibilities

---

## 5. Inertia & Wayfinder Rules

Read whenever frontend and backend communicate.

Includes:

* Forms
* HTTP requests
* Navigation
* Shared props
* Polling
* Deferred props
* Wayfinder integration

---

## 6. Folder Structure

Read before introducing:

* New folders
* New files
* New architectural layers

---

## 7. Naming Convention

Read before naming:

* Classes
* Components
* Services
* Files
* Variables
* Types

---

## 8. Testing Rules

Read before implementing or modifying tests.

Includes:

* Feature Tests
* Browser Tests
* Architecture Tests
* Unit Tests

---

# General Principles

When implementing any feature:

1. Understand the business requirement first.
2. Follow the project's documented architecture.
3. Prefer consistency over creativity.
4. Build complete vertical slices instead of isolated layers.
5. Verify behavior before considering the feature complete.

---

# Scope

These guidelines define **how this project makes engineering decisions**.

Framework documentation remains the primary reference for framework usage.

Project guidelines define how those technologies are applied within this project.

=== .ai/ai-workflow rules ===

# AI Workflow

## Purpose

This document defines the standard development workflow for AI Agents working on this project.

It does not replace the technical guidelines.

Instead, it defines **how an AI Agent should use those guidelines** while implementing features.

The goal is to produce consistent engineering decisions rather than simply generating working code.

---

# Core Principle

Develop features using a **vertical slice** approach.

Each feature should be implemented end-to-end before moving to the next feature.

Avoid implementing the entire backend, frontend, or test suite in separate phases.

Every completed feature should include:

* Backend implementation
* Frontend implementation
* Inertia integration
* Verification through tests

---

# Development Workflow

When implementing a new feature, follow this order.

## 1. Understand the Feature

Understand the business goal before writing code.

Identify:

* Business requirements
* Expected user workflow
* Required authorization
* Expected validation
* Expected outputs

Do not begin implementation before understanding the feature.

---

## 2. Read the Relevant Guidelines

Determine which project guidelines apply.

Typical references include:

* Project Philosophy
* Backend Rules
* Frontend Rules
* Inertia & Wayfinder Rules
* Folder Structure
* Naming Convention
* Testing Rules

Only consult the guidelines relevant to the current task.

---

## 3. Identify Responsibilities

Before creating files, determine where each responsibility belongs.

Examples include:

* Controller
* Request
* Policy
* Service
* Action
* Page
* Component
* Composable

Responsibilities should follow the project's architectural rules.

---

## 4. Build a Minimal Vertical Slice

Implement the smallest working version of the feature.

The objective is to establish a complete flow from backend to frontend.

Prefer a working feature over a partially completed architectural layer.

---

## 5. Refine the Implementation

Once the feature works end-to-end, improve the implementation by applying the project's architectural rules.

Examples include:

* Extract reusable Actions
* Split page-specific components
* Move business logic into Services
* Improve validation
* Improve authorization

Do not introduce abstractions prematurely.

---

## 6. Verify the Feature

Verify the completed feature using the project's testing strategy.

Typically this includes:

* Feature Tests
* Browser Tests

Unit Tests should only be introduced when project-owned algorithms require isolated verification.

---

## 7. Review Against Project Rules

Before considering the task complete, verify that the implementation still follows the project's engineering decisions.

Review:

* Architecture
* Folder structure
* Naming
* Responsibilities
* Testing

Refactor if necessary before finishing the feature.

---

# Decision-Making Rules

When multiple valid solutions exist, prefer the one already established by the project.

Consistency is more important than creativity.

Do not introduce new architectural patterns without explicit approval.

Follow the project's documented decisions before introducing personal preferences.

---

# Working with Laravel

Prefer Laravel's native solutions whenever they satisfy the project's requirements.

Only introduce third-party libraries when they provide meaningful value beyond Laravel's existing capabilities.

Avoid replacing built-in framework features with custom implementations unless explicitly justified.

---

# Working with Existing Code

Before introducing new abstractions, review the existing implementation.

Prefer extending existing patterns over creating new ones.

Do not refactor unrelated code while implementing a feature unless explicitly requested.

Keep changes focused on the current task.

---

# Incremental Development

Complete one feature before starting the next.

Each completed feature should leave the project in a working state.

Avoid leaving partially implemented backend, frontend, or testing layers for later completion.

Small, complete increments are preferred over large unfinished implementations.

---

# General Rule

When uncertainty exists, ask the following questions in order:

1. Has this project already solved a similar problem?
2. Does Laravel already provide a solution?
3. Does an approved third-party library already solve it?
4. Does the project's architecture already define the preferred approach?
5. Is a new abstraction truly necessary?

Only introduce new patterns after these questions have been considered.

=== .ai/backend-rules rules ===

# Backend Rules

## Skills

Using Laravel Boost feature, implement `laravel-best-practices` skills when developing backend features provided in `.agents/skills`.

## Controllers

Controllers coordinate requests and responses.

Controllers are responsible for:

* Receiving requests
* Calling authorization mechanisms
* Delegating business logic
* Returning responses

Controllers should not:

* Contain complex business logic
* Manage database transactions
* Perform large workflows
* Duplicate validation logic

Controllers may directly perform simple CRUD operations.

Simple CRUD operations do not require Services.

---

## Form Requests

Always prefer FormRequest for validation.

Avoid inline validation inside controllers unless there is a compelling reason.

---

## Authorization

Always prefer Policies and Gates.

Avoid implementing authorization rules directly inside controllers or services.

---

## Models

Models represent data and model-related behavior.

Acceptable model responsibilities:

* Relationships
* Accessors
* Mutators
* Casts
* Scopes
* Domain-specific convenience methods

Avoid turning models into workflow coordinators.

Business workflows belong in Services or Actions.

---

## Services

Services exist to implement business logic.

Create a Service when:

* Multiple models participate
* Database transactions are required
* Business workflows become complex
* Reuse is likely

Do not create Services for simple CRUD operations.

Service size is not a concern.

Responsibility and cohesion are the primary concerns.

---

## Actions

Actions represent reusable business operations.

Examples:

* PublishClassroomAction
* GradeQuizSubmissionAction
* ApproveInternshipGroupAction

Do not create Actions for simple CRUD operations.

Actions should represent meaningful domain operations.

---

## Repositories

Repositories are not used by default.

Eloquent already provides a sufficient abstraction layer.

Only introduce Repositories when:

* Multiple data sources must be abstracted
* Package development requires contracts
* A clear architectural benefit exists

---

## Database Access

Prefer Eloquent.

Prefer relationships over manual joins when practical.

Prefer explicit field selection over wildcard selection when possible.

Example:

```php
User::query()
    ->select([
        'id',
        'name',
        'email',
    ]);
```

Avoid retrieving unnecessary columns.

---

## Helpers and Utilities

Avoid generic Helpers and Utils.

Prefer:

* Laravel features
* Dedicated classes
* Services
* Actions

Helpers and Utils should be considered a last resort.

Every utility class must have a clear and specific responsibility.

=== .ai/development-philosophy rules ===

# Development Philosophy

## Purpose

This document defines the architectural and development principles of the project.

Its purpose is to ensure consistency between human developers and AI agents, reduce unnecessary complexity, and provide a clear decision-making framework when multiple valid solutions exist.

---

# Project Philosophy

## 1. Convention Over Configuration

Follow framework conventions whenever possible.

Prefer Laravel native features before introducing custom implementations.

Examples:

* Use FormRequest for validation.
* Use Policies for authorization.
* Use Eloquent relationships and scopes.
* Use Laravel Queue, Events, Notifications, Cache, and Scheduler when applicable.

Do not recreate features already provided by Laravel unless a clear limitation exists.

---

## 2. Simplicity Before Abstraction

Start with the simplest solution that solves the problem.

Do not introduce abstractions based on hypothetical future requirements.

Abstractions must be earned through real usage, complexity, or duplication.

Examples:

* A simple CRUD operation does not require a Service.
* A simple CRUD operation does not require an Action.
* A simple CRUD operation does not require a Repository.

---

## 3. Consistency Over Personal Preference

Consistency across the codebase is more important than individual architectural preferences.

When multiple valid solutions exist:

Choose the approach already established in the project.

Do not introduce new patterns, structures, or naming conventions without approval.

---

## 4. Clear Responsibilities Over Arbitrary Size Limits

The quality of code is determined by responsibility and cohesion, not by line count.

Controllers, Models, Services, and Actions may grow when necessary.

Avoid "God Classes" and "God Methods".

A class should have a clear purpose and responsibility.

---

## 5. Business Logic Belongs to the Correct Layer

Backend is responsible for:

* Business rules
* Data integrity
* Security
* Authorization
* Validation
* Transactions

Frontend is responsible for:

* Presentation
* User interaction
* State visualization
* Formatting data for display

Backend should return correct data.

Frontend should present that data appropriately.

Examples:

Backend:

```json
{
  "role": "administrator",
  "created_at": "2026-06-24T10:00:00Z"
}
```

Frontend:

```text
Administrator
24 June 2026
```

---

## 6. Battle-Tested Solutions First

Prefer solutions in the following order:

1. Laravel Native Features
2. Well-maintained Community Packages
3. Custom Implementation

Avoid building custom solutions for problems that are already solved effectively by Laravel or mature libraries.

---

## 7. Modern Laravel Monolith

The default architecture is:

* Laravel
* Inertia
* Vue

The application is a modern monolith.

Do not introduce:

* Microservices
* Domain-driven architecture
* Modular architecture
* Package-based architecture

Unless explicitly requested.

---

## 8. Progressive Refactoring

Small duplication is acceptable.

Premature abstraction is not.

When duplication becomes substantial or maintenance becomes difficult, refactor appropriately.

Refactoring should be driven by evidence, not prediction.

---

## 9. AI Agent Behavior

AI agents must preserve consistency.

AI agents may suggest architectural improvements.

AI agents must not introduce new architectural patterns without approval.

AI agents should prioritize maintainability and consistency over novelty.

=== .ai/folder-structure rules ===

# Folder Structure

## Purpose

This document defines where files should be placed within the project.

It complements the Backend Rules, Frontend Rules, and Inertia & Wayfinder Rules by translating architectural responsibilities into a consistent directory structure.

Whenever Laravel introduces new conventions or directories, prefer following the official project structure before introducing custom alternatives.

---

# Core Principles

## Follow Laravel First

Follow Laravel's default directory structure whenever possible.

Do not reorganize Laravel's directories without a clear architectural reason.

Extend Laravel instead of replacing its conventions.

---

## Organize by Responsibility

Directories represent architectural responsibilities.

Examples:

* Controllers belong in `app/Http/Controllers`
* Requests belong in `app/Http/Requests`
* Pages belong in `resources/js/pages`
* Composables belong in `resources/js/composables`

Do not place files into directories simply because they appear related.

Choose the directory that matches the file's responsibility.

---

## Organize Features by Resource

Whenever a feature contains multiple related files, organize them by resource.

Example resources:

* Users
* Classrooms
* Quizzes
* Notifications

This convention should remain consistent across backend and frontend whenever practical.

---

## Introduce New Directories Only When Needed

Do not create top-level directories in advance.

Introduce a new directory only when:

* Laravel adopts the convention, or
* The project has a clear architectural need.

Avoid speculative directory structures.

---

# Backend Structure

| File Type        | Location               |
| ---------------- | ---------------------- |
| Controllers      | `app/Http/Controllers` |
| Form Requests    | `app/Http/Requests`    |
| Middleware       | `app/Http/Middleware`  |
| Models           | `app/Models`           |
| Services         | `app/Services`         |
| Actions          | `app/Actions`          |
| Policies         | `app/Policies`         |
| Observers        | `app/Observers`        |
| Enums            | `app/Enums`            |
| Queue Jobs       | `app/Jobs`             |
| Exceptions       | `app/Exceptions`       |
| Console Commands | `app/Console/Commands` |
| Providers        | `app/Providers`        |

---

## Services

If a resource contains only a single service, place it directly inside:

```text
app/Services/
```

Example:

```text
app/Services/
└── UserManagementService.php
```

If a resource grows to multiple services, group them by resource.

Example:

```text
app/Services/
└── Users/
    ├── UserManagementService.php
    ├── UserInvitationService.php
    └── UserImportService.php
```

Avoid creating resource directories containing only a single service.

---

## Actions

Group Actions by resource whenever multiple actions exist.

Example:

```text
app/Actions/
└── Users/
    ├── ActivateUser.php
    ├── DeactivateUser.php
    └── ImportUsers.php
```

---

# Frontend Structure

Wayfinder-generated files and directories are considered read-only. Any manual changes made to these files will be overwritten during regeneration and therefore have no lasting effect.

| File Type                                        | Location                             |
| ------------------------------------------------ | ------------------------------------ |
| Pages                                            | `resources/js/pages`                 |
| Shared Components                                | `resources/js/components`            |
| Feature Components                               | `resources/js/components/<resource>` |
| UI Components                                    | `resources/js/components/ui`         |
| Layouts                                          | `resources/js/layouts`               |
| Composables                                      | `resources/js/composables`           |
| Shared Types                                     | `resources/js/types`                 |
| Library Integration                              | `resources/js/lib`                   |
| Wayfinder                                        | `resources/js/wayfinder`             |
| Generated Controller Actions (part of Wayfinder) | `resources/js/actions`               |
| Generated Routes (part of Wayfinder)             | `resources/js/routes`                |

---

## Shared Components

Shared components are reusable across multiple features.

Examples include:

* Page headers
* Empty states
* Shared dialogs
* Shared navigation components

Shared components belong directly inside:

```text
resources/js/components
```

---

## Feature Components

Components that belong to a specific resource should be grouped together.

Example:

```text
resources/js/components/
└── users/
    ├── UserTable.vue
    ├── UserCreateEditDialog.vue
    ├── UserDeleteDialog.vue
    └── UserActivationDialog.vue
```

Do not place feature-specific components in the global components directory.

---

## UI Components

The `components/ui` directory represents the project's UI layer.

Implementations or wrappers for UI libraries belong here.

Examples:

* shadcn-vue
* Nuxt UI
* Future UI libraries

Feature components should consume UI components from this layer instead of interacting directly with third-party UI libraries whenever practical.

---

## Library Integration

The `lib` directory contains project-level integrations and configuration for third-party libraries.

Examples:

* Day.js configuration
* Vue Sonner integration
* Tailwind Merge utilities

Do not place reusable application logic inside `lib`.

---

## Types

Shared domain types belong in:

```text
resources/js/types
```

Do not redefine shared domain types inside components.

Local implementation types may remain close to the component that owns them.

---

# Testing Structure

Follow Pest's default project structure.

Example:

```text
tests/
├── Feature/
├── Browser/
├── Unit/
├── Pest.php
└── TestCase.php
```

Feature, Browser, and Unit tests should be organized by resource whenever practical.

Example:

```text
tests/
├── Feature/
│   ├── Users/
│   ├── Classrooms/
│   └── Notifications/
│
├── Browser/
│   ├── Users/
│   ├── Classrooms/
│   └── Internship/
│
└── Unit/
    ├── Services/
    └── Helpers/
```

---

## Reusable Test Helpers

When test setup or common operations become repetitive, extract the reusable logic instead of duplicating it across test files.

Examples include:

* Authentication helpers
* User creation helpers
* Browser login helpers
* Common assertions
* Shared test setup

Prefer using Pest's extension points, such as:

* Global helper functions
* Custom expectations
* Shared traits
* Base test classes

Do not make tests depend on one another.

Each test should remain independently executable.

Only introduce additional testing directories (such as `tests/Support`) when they represent a clear architectural responsibility and are justified by the project's size.

---

# Routes

Keep route files organized by resource.

Example:

```text
routes/

web.php
users.php
classrooms.php
quizzes.php
notifications.php
```

The main route files should load resource route files.

Avoid placing every route inside a single `web.php` file as the project grows.

---

# Configuration

Follow Laravel's default configuration structure.

Package configuration files belong in:

```text
config/
```

Do not relocate configuration files.

Use Laravel's default naming conventions whenever possible.

---

# Database

Follow Laravel's default database structure.

Examples:

```text
database/

migrations/
factories/
seeders/
```

Do not reorganize migrations into feature directories.

---

# Public Assets

Follow Laravel's default public directory structure.

Do not move the application entry point or generated build assets.

---

# Storage

Follow Laravel's default storage structure.

Keep private and public files inside Laravel's storage directories.

Avoid introducing custom storage layouts unless required.

---

# General Rule

When creating a new file, determine its location by asking:

1. What responsibility does this file have?
2. Does Laravel already define a conventional location?
3. Does this belong to a specific resource?
4. Does this responsibility already have a dedicated directory?

The correct location is determined by responsibility first, Laravel conventions second, and resource organization third.

=== .ai/frontend-rules rules ===

# Frontend Rules

## Skills

Using Laravel Boost feature, implement `tailwindcss-development` skills when developing frontend template provided in `.agents/skills`. Refer to your newest knowledge about Vuejs when developing vue file.

## Purpose

This document defines frontend responsibilities, structure, and architectural decisions for Vue applications built on top of Laravel and Inertia.

The goal is to maintain consistency, improve maintainability, and provide clear guidance for both developers and AI agents.

## Respect the Project Structure

The frontend should extend the existing project structure.

Do not reorganize directories or introduce new architectural layers unless explicitly approved.

Consistency is preferred over experimentation.

---

# Core Principles

## Frontend Is Responsible for Presentation

Frontend is responsible for:

* User interface
* User interaction
* Data presentation
* User experience
* Local UI state

Frontend is not responsible for:

* Business rules
* Authorization
* Validation rules
* Data integrity
* Persistence

Backend remains the source of truth.

---

## Workflow First

Frontend structure should follow user workflows.

Pages represent workflow entry points.

Components represent responsibilities within a workflow.

The organization of the frontend should be driven by workflow boundaries, not file size.

---

## Responsibility Ownership

Every workflow must have a clear owner.

Pages own page-level workflows and shared page state.

Components own the implementation of their specific responsibilities.

Backend communication should be implemented by the component or page that owns the corresponding responsibility.

Avoid moving responsibilities between layers solely to satisfy architectural patterns.

---

## Responsibility Over File Size

File size alone is not a reason to create new components.

A component may grow large if it still represents a single responsibility.

Examples:

Acceptable:

```text
UserTable.vue
```

Containing:

* filtering
* sorting
* pagination
* bulk actions
* export actions

All of these still belong to the responsibility of displaying and managing a user table.

Do not split components solely to satisfy arbitrary line-count limits.

---

## Split by Responsibility

Create new components when responsibilities become distinct.

Examples:

```text
UserTable.vue
UserCreateEditDialog.vue
UserDeleteDialog.vue
UserActivationDialog.vue
```

Each component represents a separate responsibility or workflow action.

Avoid splitting components into excessively small visual fragments without clear value.

Examples:

```text
UserNameCell.vue
UserEmailCell.vue
UserDeleteButton.vue
```

These should not exist unless genuine reuse or complexity justifies them.

---

## Consistency Over Preference

When multiple valid implementations exist:

Choose the approach already established in the project.

Consistency is more important than personal preference or architectural novelty.

---

# Pages

## Purpose

Pages are workflow entry points.

Pages act as:

* Workflow coordinators
* Page-level state managers
* Component orchestrators

Pages are not responsible for implementing every detail of a workflow.

---

## Page Responsibilities

Pages may:

* Receive data from the backend
* Manage page-level state
* Manage selected records
* Open and close dialogs
* Coordinate interactions between components
* Compose workflow components

Example

A user management page may consist of:

- A page header
- A table component
- A create/edit dialog
- A delete confirmation dialog
- An activation dialog

---

## Avoid Implementation-Heavy Pages

Pages should not contain the entire implementation of complex workflows.

When a responsibility becomes substantial, extract it into a dedicated component.

---

## Simple Pages May Remain Simple

Not every page requires component extraction.

Examples:

```text
Login.vue
ForgotPassword.vue
ResetPassword.vue
```

may remain self-contained if the workflow is simple.

Avoid creating unnecessary components that provide little value.

---

# Components

## Purpose

Components encapsulate a specific responsibility.

Components should own the implementation details of that responsibility.

---

## Shared Components

Shared components are reusable across multiple features.

Examples:

```text
PageHeader.vue
Breadcrumbs.vue
EmptyState.vue
```

Shared components belong in:

```text
resources/js/components
```

---

## Feature Components

Feature-specific components should be grouped together.

Shared components should remain in the global components directory.

Feature components should remain close to the feature they support.

---

## Reusability Must Be Real

Do not create reusable abstractions based on hypothetical future requirements.

Create reusable components when duplication already exists or reuse is highly likely.

---

# Layouts

## Purpose

Layouts provide shared page structure.

Examples:

* Application shell
* Dashboard layout
* Authentication layout
* Guest layout

Layouts exist to wrap multiple pages with a common structure.

---

## Layout Responsibilities

Layouts may contain:

* Navigation
* Sidebar
* Header
* Breadcrumb placement
* Shared visual structure

Layouts should not contain workflow-specific business logic.

---

# State Management

## Prefer the Smallest Possible Scope

State should live as close as possible to where it is used.

Preferred order:

1. Component state
2. Page state
3. Shared application state
4. Global frontend state

Avoid introducing global state prematurely.

---

## Global State Is Exceptional

Global state should only be introduced when multiple unrelated areas of the application require the same frontend-owned state.

Examples:

* Theme preferences
* Sidebar state
* Notification queue
* Frontend-only workflow state

Global state is not the default solution.

---

# Composables

## Purpose

Composables encapsulate reusable frontend logic.

---

## Appropriate Use Cases

Examples:

```text
useDateFormatter
useCurrencyFormatter
useAppearance
```

Composable logic should be reusable across multiple pages or components.

---

## Avoid Page-Specific Composables

Do not move page-specific logic into composables simply to reduce file size.

If logic belongs exclusively to a page or component, keep it there.

---

# Responsibility Ownership

## Page Ownership

Pages own:

- Workflow coordination
- Page-level state
- Shared page data
- Communication required by multiple workflow components

## Components Ownership

Components own:

- Their internal UI
- Their internal state
- Their backend communication
- Their loading state
- Their success and error handling

Components should not rely on the page to perform backend communication unless the communication belongs to the page itself.

## Example

`Users/Index.vue`

Responsibilities

- Receive users from backend
- Manage selected user
- Manage dialog visibility
- Coordinate page workflow

↓

`UserTable.vue`

Responsibilities

- Display table
- Emit edit/delete/activation requests

↓

`UserCreateEditDialog.vue`

Responsibilities

- Display form
- Submit form
- Handle validation errors
- Handle loading state

↓

`UserDeleteDialog.vue`

Responsibilities

- Display confirmation
- Submit deletion
- Handle request state

---

# UI Components

## UI Layer

The `components/ui` directory represents the project's UI layer.

UI components should act as wrappers or implementations of the chosen UI library.

Whether the project uses:

* shadcn-vue
* Nuxt UI
* PrimeVue
* another UI library

all UI library integrations should remain inside `components/ui`.

Feature components and pages should consume UI components from the project's UI layer instead of importing directly from third-party libraries whenever practical.

This keeps the application's UI consistent and allows the underlying UI library to be replaced or extended without affecting feature components.

## Shared UI Libraries

Prefer using existing UI components before creating custom implementations.

Examples:

* shadcn-vue components
* Existing shared components
* Existing project patterns

Custom UI components should only be created when existing solutions do not satisfy requirements.

---

# Types

## Shared Domain Types

Shared domain types should be defined in the project's `types` directory.

Examples include:

* User
* Classroom
* Quiz
* Notification
* Shared API response types
* Shared application interfaces

Avoid redefining shared domain types inside Vue components.

Import them from the shared types directory instead.

---

## Local Types

Types that only exist to support a specific page or component may remain inside that file.

Examples include:

* Extended interfaces
* Temporary UI models
* Component-specific unions
* Local helper types

Keep local types close to the implementation they support.

Do not promote them into the shared types directory unless they become reusable.

---

## Keep Components Focused

Vue components should primarily describe behavior and presentation.

Avoid filling `<script setup>` with large domain type definitions.

When a type represents a reusable domain concept, move it into the shared `types` directory.

This improves readability and keeps components focused on their responsibility.

---

# Library Integration

The lib directory contains project-level integrations and configurations for third-party libraries.

Examples:

- Day.js configuration
- Vue Sonner integration
- Tailwind Merge utilities

Avoid placing application-specific reusable logic inside lib.

Application logic belongs in composables or components.

---

# Final Principle

Frontend architecture should prioritize:

* Responsibility
* Maintainability
* Workflow clarity
* Consistency

over:

* Excessive abstraction
* Premature reusability
* Arbitrary file size limits
* Architectural trends

=== .ai/inertia-wayfinder-rules rules ===

# Inertia & Wayfinder Rules

## Purpose

This document defines how frontend components communicate with the backend.

The ownership of that communication is defined in Frontend Rules.

This document only defines which Inertia and Wayfinder APIs should be used once ownership has been determined.

It does **not** replace the official Inertia or Wayfinder documentation. Instead, it establishes the project's architectural decisions whenever multiple valid approaches exist.

Whenever an implementation detail is not covered by this document, follow the latest official Inertia documentation and Laravel Boost Skills.

---

## Skills

Using Laravel Boost feature, implement `inertia-vue-development` and `wayfinder-development` skills when developing frontend scripts provided in `.agents/skills`.

---

# Rule 0 — Inertia and Wayfinder Are One Architecture

This project treats Inertia and Wayfinder as a unified application architecture.

Whenever frontend code communicates with Laravel controllers or routes:

* Prefer Inertia primitives.
* Prefer Wayfinder-generated controller actions and routes.
* Avoid introducing alternative routing or HTTP abstractions.

---

# Official Documentation First

Before implementing any Inertia or Wayfinder feature:

1. Consult the latest Laravel Boost Skill.
2. Follow the official implementation.
3. Only deviate when this document explicitly defines a project-specific convention.

Never invent a custom implementation when the framework already provides one.

---

# Forms

## Default

Use the Inertia `<Form>` component.

Example use cases:

* Create forms
* Update forms
* Authentication forms
* Search forms
* Simple CRUD forms

The `<Form>` component should be the default choice.

---

## Alternative

Use `useForm()` only when programmatic control is required.

Examples:

* Dynamic default values
* Dependent fields
* Programmatically modifying form values
* Complex client-side form interactions

Do not use `useForm()` simply because it is available.

---

# Page Refresh

After a successful mutation:

- Prefer controller redirects.
- Prefer Inertia page rerendering.
- Prefer receiving fresh props from the backend.

Avoid manually synchronizing application data after successful mutations.

Use onSuccess() only for UI concerns such as:

- Closing dialogs
- Resetting forms
- Focusing inputs
- Showing local UI feedback

Do not use onSuccess() to manually refresh application state that will already be updated by Inertia.

# Navigation

## User Navigation

Use the Inertia `<Link>` component.

Do not use plain `<a>` tags for internal navigation.

---

## Programmatic Navigation

Prefer dedicated HTTP router helpers over generic navigation methods whenever both provide equivalent functionality.

Example:

```ts
router.get()
router.post()
router.put()
router.patch()
router.delete()
```

Avoid:

```ts
router.visit(url, {
    method: 'post',
})
```

unless `router.visit()` provides functionality unavailable through the shorthand methods.

Reason:

* More readable
* More explicit
* Consistent throughout the project

---

# HTTP Requests

## Default

Use Inertia navigation whenever the request represents a page visit.

---

## Inertia HTTP Primitives

Use the official Inertia HTTP primitives.

If they cannot satisfy a legitimate technical requirement, use the browser Fetch API.

Do not introduce additional HTTP libraries.

---

## Avoid

Do not introduce Axios.

Axios duplicates functionality already provided by Inertia and increases unnecessary project complexity.

---

# Wayfinder

Always prefer Wayfinder-generated actions over manually constructed routes or URLs.

Avoid:

* Hardcoded URLs
* String-based routes
* Manual route construction

Preferred:

```ts
router.get(index())
router.post(store())
```

```vue
<Form v-bind="store.form()">
```

Wayfinder should be the single source of truth for frontend routing.

---

# Shared Props

Use Shared Props only for data that:

* Is reused across multiple pages
* Is reused across multiple components
* Represents application-wide state

Examples:

* Authenticated user
* Application name
* Sidebar state
* Shared navigation data

---

## Security

Never expose sensitive information through Shared Props.

Remember that Shared Props are delivered to the client and can be inspected through browser developer tools.

---

# Deferred Props

Use Deferred Props only when:

* A query is significantly expensive
* Data benefits from caching
* Rendering the page immediately provides a noticeably better user experience

Deferred Props should not become the default loading strategy.

---

# Polling

When periodic updates are required, use the polling features provided by Inertia.

Avoid implementing manual polling using timers when the framework already provides the capability.

---

# Infinite Scroll

Default to traditional pagination.

Use Infinite Scroll only when explicitly required by the project.

Administrative systems generally benefit more from pagination than infinite scrolling.

---

# Optimistic UI

Do not implement optimistic updates by default.

Use optimistic UI only when explicitly requested or when there is a clear user experience benefit that has been approved.

The backend remains the source of truth.

---

# Final Principle

Whenever multiple official Inertia or Wayfinder APIs provide equivalent functionality:

Choose the simplest, most explicit, and most consistent option adopted by this project.

Consistency is more valuable than personal preference.

---

# Communication Placement

The placement of Inertia communication follows responsibility ownership.

If a request belongs exclusively to a component's responsibility, implement the communication inside that component.

Examples:

- Create/Edit dialog
- Delete dialog
- Avatar uploader

If multiple workflow components require the same request or data, implement the communication at the page level.

Examples:

- Dashboard statistics
- Shared search
- Shared filters
- Shared page data

Do not move backend communication to the page merely to make components "dumb".

=== .ai/naming-convention rules ===

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

=== .ai/testing-rules rules ===

# Testing Rules

## Purpose

This document defines the project's testing philosophy and strategy.

It complements Laravel Boost's Pest skills by defining what should be tested, the responsibility of each test type, and how tests should verify application behavior.

This document focuses on testing strategy, not Pest syntax or framework APIs.

---

# Testing Philosophy

## Test Behavior, Not Implementation

Tests should verify application behavior and feature contracts instead of implementation details.

A test should remain valid even if the internal implementation changes, provided the externally observable behavior remains the same.

Do not write tests that depend on:

* Controller implementation
* Service implementation
* Action implementation
* Internal method calls

Instead, verify the observable result.

---

## Test Project-Owned Logic

Laravel and third-party libraries are already extensively tested by their maintainers.

Prioritize testing code that belongs to the project.

Examples include:

* Business rules
* Business workflows
* Authorization rules
* Validation rules
* Custom algorithms
* Project-specific behaviors

Avoid writing tests solely to verify framework functionality.

---

# Test Priority

Unless otherwise requested, prioritize tests in the following order:

1. Feature Tests
2. Browser Tests
3. Unit Tests

Feature and Browser Tests are considered equally important for completed features.

Unit Tests should only be introduced when custom project logic requires isolated verification.

---

# Feature Tests

## Responsibility

Feature Tests verify the HTTP contract of the Laravel application.

They should verify the complete behavior exposed through HTTP without relying on internal implementation details.

Feature Tests are backend-aware.

They are **not** frontend-framework-aware.

---

## Verify Contracts

Determine the expected behavior before choosing assertions.

Do not begin by selecting assertions.

Instead, ask:

> "What contract does this endpoint provide?"

Assertions should verify that contract.

---

## Typical Contracts

Depending on the endpoint, consider verifying:

* HTTP status
* Authorization
* Validation
* Database changes
* Storage changes
* Events
* Notifications
* Mail
* Queue
* Redirects
* Session data
* Flash messages

When an endpoint returns an Inertia response, also verify:

* Component
* Props
* Shared Props
* Deferred Props (when applicable)

When an endpoint redirects to an Inertia page, verify the redirect itself and, when appropriate, follow the redirect to verify the final Inertia response.

Only verify behaviors that belong to the endpoint's contract.

---

## Think in Behaviors

Avoid thinking:

> Which assertion should I use?

Instead think:

> What behavior should this endpoint guarantee?

Assertions exist only to verify those behaviors.

---

# Browser Tests

## Responsibility

Browser Tests verify the user experience.

They should ensure users can successfully complete workflows using the application's interface.

Browser Tests verify interactions that Feature Tests cannot.

Examples include:

* User interaction
* Form behavior
* Dialogs
* Navigation
* JavaScript behavior
* UI feedback
* Browser rendering

---

## Workflow Verification

Browser Tests should verify complete page workflows.

Examples include:

* Creating resources
* Updating resources
* Deleting resources
* Filtering
* Searching
* Pagination
* Multi-step interactions

Focus on the user's ability to complete work successfully.

---

## End-to-End Workflows

Completed business processes should include at least one dedicated end-to-end Browser Test whenever practical.

These tests verify complete workflows from the user's perspective.

Example:

* Student submits internship application
* Supervisor approves application
* Coordinator assigns advisor
* Internship becomes active

These tests validate the complete business process rather than individual pages.

---

# Unit Tests

## Responsibility

Unit Tests verify isolated project-owned logic.

Examples include:

* Custom algorithms
* Parsers
* Calculators
* Formatters
* Pure functions
* Complex business calculations

Do not write Unit Tests simply because a Service, Action, or Model exists.

---

## Prefer Feature Tests

When business behavior can be verified through HTTP, prefer Feature Tests.

Only isolate logic into Unit Tests when independent verification provides meaningful value.

---

# Architecture Tests

## Responsibility

Architecture Tests verify that the project continues to follow its architectural decisions.

Unlike Feature Tests, they do not verify application behavior.

Unlike Unit Tests, they do not verify algorithms.

Their purpose is to enforce long-lived architectural conventions.

---

## What to Verify

Architecture Tests are appropriate for verifying decisions such as:

* Naming conventions
* Directory structure
* Layer boundaries
* High-level architectural rules

Examples include:

* Controllers use the `Controller` suffix.
* Form Requests use the `Request` suffix.
* Services use the `Service` suffix.
* Project-specific architectural layers remain in their expected directories.
* Prohibited architectural patterns are not introduced.

---

## What Not to Verify

Do not use Architecture Tests to enforce subjective coding styles or implementation details.

Avoid testing:

* Method length
* Class size
* Internal implementation
* Coding preferences
* Temporary project decisions

Architecture Tests should protect long-term architectural decisions rather than restrict future refactoring.

---

# Edge Cases

Every feature should consider testing more than the happy path.

Depending on the feature, consider testing:

* Authorization failures
* Validation failures
* Invalid input
* Boundary values
* Empty input
* Duplicate data
* Missing resources
* Invalid state transitions
* Business rule violations

Edge cases should reflect real business scenarios.

---

# Test Independence

Each test must be independently executable.

Tests must never depend on the execution order of other tests.

Avoid sharing state between tests.

The only exception is dedicated End-to-End Browser Tests, which intentionally verify a complete business workflow from start to finish within a single test.

---

# Reusable Test Logic

Avoid duplicating repetitive testing code.

When setup or common operations become repetitive, extract reusable test helpers.

Examples include:

* Authentication helpers
* User creation helpers
* Browser login helpers
* Shared assertions
* Common setup routines

Prefer Pest's extension points, including:

* Global helper functions
* Custom expectations
* Shared traits
* Base test classes

Reuse setup logic.

Never reuse entire test cases.

---

# Test Readability

Tests should clearly communicate intent.

Prefer the Arrange–Act–Assert structure whenever practical.

Each test should verify a single behavior or workflow.

Avoid combining unrelated behaviors into a single test.

---

# General Rule

Before writing any test, ask:

1. What contract does this feature provide?
2. Which test type is responsible for verifying that contract?
3. What observable behaviors prove the contract has been fulfilled?
4. Which assertions best verify those behaviors?

Tests should prove that the application behaves correctly, not that it is implemented in a particular way.

=== foundation rules ===

# Laravel Boost Guidelines

The Laravel Boost guidelines are specifically curated by Laravel maintainers for this application. These guidelines should be followed closely to ensure the best experience when building Laravel applications.

## Foundational Context

This application is a Laravel application and its main Laravel ecosystems package & versions are below. You are an expert with them all. Ensure you abide by these specific packages & versions.

- php - 8.5
- inertiajs/inertia-laravel (INERTIA_LARAVEL) - v3
- laravel/fortify (FORTIFY) - v1
- laravel/framework (LARAVEL) - v13
- laravel/prompts (PROMPTS) - v0
- laravel/wayfinder (WAYFINDER) - v0
- larastan/larastan (LARASTAN) - v3
- laravel/boost (BOOST) - v2
- laravel/mcp (MCP) - v0
- laravel/pail (PAIL) - v1
- laravel/pint (PINT) - v1
- laravel/sail (SAIL) - v1
- laravel/telescope (TELESCOPE) - v5
- pestphp/pest (PEST) - v4
- phpunit/phpunit (PHPUNIT) - v12
- @inertiajs/vue3 (INERTIA_VUE) - v3
- tailwindcss (TAILWINDCSS) - v4
- vue (VUE) - v3
- @laravel/vite-plugin-wayfinder (WAYFINDER_VITE) - v0
- eslint (ESLINT) - v9
- prettier (PRETTIER) - v3

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If the user doesn't see a frontend change reflected in the UI, it could mean they need to run `pnpm run build`, `pnpm run dev`, or `composer run dev`. Ask them.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

## Replies

- Be concise in your explanations - focus on what's important rather than explaining obvious details.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Always use `search-docs` before making code changes. Do not skip this step. It returns version-specific docs based on installed packages automatically.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Follow existing application Enum naming conventions.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Every change must be programmatically tested. Write a new test or update an existing test, then run the affected tests to make sure they pass.
- Run the minimum number of tests needed to ensure code quality and speed. Use `php artisan test --compact` with a specific filename or filter.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

## Vite Error

- If you receive an "Illuminate\Foundation\ViteException: Unable to locate file in Vite manifest" error, you can run `pnpm run build` or ask the user to run `pnpm run dev` or `composer run dev`.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

## Pest

- This project uses Pest for testing. Create tests: `php artisan make:test --pest {name}`.
- The `{name}` argument should not include the test suite directory. Use `php artisan make:test --pest SomeFeatureTest` instead of `php artisan make:test --pest Feature/SomeFeatureTest`.
- Run tests: `php artisan test --compact` or filter: `php artisan test --compact --filter=testName`.
- Do NOT delete tests without approval.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

</laravel-boost-guidelines>
