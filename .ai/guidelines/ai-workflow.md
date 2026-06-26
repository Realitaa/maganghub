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
