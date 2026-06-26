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