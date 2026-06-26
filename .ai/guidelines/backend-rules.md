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