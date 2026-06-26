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
