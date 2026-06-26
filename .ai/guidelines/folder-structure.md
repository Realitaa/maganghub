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
