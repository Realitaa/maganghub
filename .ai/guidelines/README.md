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
