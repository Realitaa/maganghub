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
