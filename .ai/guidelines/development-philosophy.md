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