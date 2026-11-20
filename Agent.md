# CoachDesk --- Agent Instructions

> **Document:** Agent Operating Rules\
> **Project:** CoachDesk --- Coaching Management System\
> **Backend:** Laravel 13 + PHP + MySQL\
> **Frontend:** Vue 3 + Composition API + `<script setup>` + TypeScript
> (preferred) + Vite\
> **UI:** AdminLTE\
> **API Communication:** Laravel REST API + Axios\
> **Routing:** Vue Router\
> **State Management:** Pinia\
> **Authentication:** Laravel Sanctum\
> **Authorization:** Spatie Laravel Permission\
> **Media:** Spatie Laravel Media Library\
> **Status:** Mandatory instructions for every AI coding agent working
> on this project

------------------------------------------------------------------------

## 1. Mission

Act as a Senior PHP/Laravel Developer, Vue.js Developer, Full-Stack
Software Architect, Debugging Specialist, and Programming Mentor for
CoachDesk.

Your responsibility is to understand the existing project, follow its
documentation, preserve working functionality, and implement the user's
requested work with clean, secure, maintainable, version-compatible
code.

Do not behave as an autonomous product owner. Do not invent
requirements, expand scope, or make unresolved architecture/database
decisions without approval.

The goal is to build CoachDesk incrementally as a maintainable
coaching-centre management application.

------------------------------------------------------------------------

## 2. Mandatory Documentation-First Rule

**This rule is mandatory and must be followed before starting any
implementation, debugging, refactoring, or architecture task.**

### 2.1 Read Agent.md First

At the beginning of every new task:

1.  Read this `Agent.md` completely.
2.  Identify the project root.
3.  Discover all project-authored Markdown documents.
4.  Read all of those Markdown documents before changing code.
5.  Then inspect the relevant source code and configuration files.
6.  Only after completing these steps may implementation begin.

Do not rely on a previous conversation summary, an earlier AI response,
a file name, or a partial excerpt as a replacement for reading the
current files.

### 2.2 Required Core Documents

Read the latest project versions of all five documents below. They may
be located in the project root or under `docs/`.

  -----------------------------------------------------------------------
  Document                            Required Purpose
  ----------------------------------- -----------------------------------
  `project-overview.md`               Product scope, architecture,
                                      modules, technology, and project
                                      principles

  `PRD.md`                            Functional requirements, roles,
                                      workflows, security, and acceptance
                                      criteria

  `ERD.md`                            Entities, relationships,
                                      cardinality, and logical data model

  `database-schema.md`                Proposed columns, types, keys,
                                      indexes, constraints, delete
                                      behavior, and migration order

  `folder-structure.md`               Target Laravel and Vue code
                                      organization
  -----------------------------------------------------------------------

Use the latest files that actually exist in the repository. Do not
assume that a previously uploaded copy is still the current version.

### 2.3 Read All Other Project Markdown Files

After reading the five core documents, recursively discover and read all
other project-authored Markdown files, including when present:

-   `README.md`
-   Additional files under `docs/`
-   Module-specific documentation
-   Architecture or decision records
-   Setup and deployment instructions
-   API documentation
-   Testing instructions
-   Contribution instructions
-   Existing task notes and implementation plans
-   Markdown instructions under `.github/` or other project
    configuration folders

Include `.md` and `.mdx` files where they contain project instructions
or requirements.

Exclude dependency-generated documentation under `vendor/`,
`node_modules/`, compiled/build output, caches, and other
third-party/generated directories unless the current task specifically
requires inspecting them. Do not skip first-party project documentation
merely because it is outside `docs/`.

If there are many documents, read them in manageable sections until all
relevant project-authored Markdown content has been reviewed. Do not
claim that all documents were read if some were inaccessible or skipped.

### 2.4 Documentation Discovery Procedure

Use the tools available in the current development environment to:

1.  Confirm the project root.
2.  List project-authored Markdown files recursively.
3.  Read each file completely or in sequential chunks.
4.  Record the document names and any important constraints.
5.  Identify conflicts, outdated instructions, and unresolved decisions.
6.  Continue to source-code inspection only after the documentation
    review.

If one of the five core documents is missing, unreadable, or duplicated
with conflicting versions: - Report the exact issue. - Do not silently
select an arbitrary copy. - Continue only with work that is safe and
independent of the missing information, or ask the user for the missing
document/version.

### 2.5 Documentation Review Summary Before Work

Before implementation, provide a concise summary to the user containing:

-   Which core documents were found and read.
-   Whether additional project Markdown files were found and reviewed.
-   The relevant requirements for the requested task.
-   Any conflict or unresolved decision that blocks implementation.

Do not repeat the entire documentation in the response. Keep the summary
focused on the current task.

------------------------------------------------------------------------

## 3. Source-of-Truth and Conflict Resolution

Use the documents according to their purpose:

  -----------------------------------------------------------------------
  Source                              Authority
  ----------------------------------- -----------------------------------
  Current explicit user instruction   Defines the requested task or
                                      approved scope change

  Actual source code and runtime      Defines what currently exists and
  configuration                       what is actually installed

  `Agent.md`                          Defines mandatory agent workflow
                                      and engineering rules

  `PRD.md`                            Defines functional requirements and
                                      acceptance criteria

  `project-overview.md`               Defines product scope and
                                      high-level architecture

  `ERD.md`                            Defines conceptual entities and
                                      relationships

  `database-schema.md`                Defines proposed table columns,
                                      types, keys, indexes, constraints,
                                      and delete behavior

  `folder-structure.md`               Defines proposed code organization

  Other project Markdown files        May define additional decisions,
                                      module details, or implementation
                                      history
  -----------------------------------------------------------------------

### Conflict Rules

1.  Do not silently ignore a relevant document.
2.  Do not silently reconcile conflicting requirements by guessing.
3.  If `ERD.md` and `database-schema.md` disagree, identify the exact
    difference and ask for a decision before creating or changing
    affected migrations.
4.  If a proposed folder structure differs from the existing Laravel
    project, inspect the existing project and preserve working
    conventions unless an approved change is necessary.
5.  If the documentation differs from actual installed package versions
    or source code, report the difference and use verified runtime facts
    for compatibility decisions.
6.  If a new user request changes an approved requirement, explain the
    impact and update the relevant documentation when the user approves
    the change.
7.  Security, privacy, financial integrity, and academic-history
    protections must never be weakened merely to make an implementation
    easier.

Treat proposed or unresolved items in the documents as **not yet
approved decisions**. Do not turn them into final implementation
requirements without confirmation.

------------------------------------------------------------------------

## 4. Product Scope

CoachDesk is a **single coaching-centre management system** for the
initial release.

### 4.1 Main Roles

-   Super Admin
-   Admin
-   Teacher
-   Accountant
-   Student

Use Spatie Laravel Permission for role and permission management. Do not
use a hard-coded `users.role` field as the main authorization system.

### 4.2 Main Modules

-   Authentication and user management
-   Student and teacher profiles
-   Categories
-   Sub-categories
-   Courses
-   Batches
-   Enrolments
-   Class sessions and attendance
-   Fee invoices and payments
-   Announcements
-   Learning materials
-   Role-specific dashboards
-   Reports and CSV export
-   Trash, Restore, and Force Delete for approved records
-   Exams and results as an advanced module after the core workflow is
    stable

### 4.3 Initial Out-of-Scope Features

Do not add these unless the user explicitly approves a scope change:

-   Multi-branch operations
-   Multi-tenant SaaS
-   Payroll
-   Full accounting software
-   Live video classes
-   Online payment gateway integration
-   Native mobile applications

Optional extensions such as notifications, bulk import, advanced exam
analytics, activity/audit history, and scheduled reminders must not
delay the core application.

------------------------------------------------------------------------

## 5. Mandatory Architecture

### 5.1 Selected Architecture

Use:

**One Laravel repository + Laravel REST API + Vue 3 SPA.**

Laravel owns: - Authentication and authorization - Database access and
relationships - Backend validation - Business rules - API response
formatting - Media handling and private file access - Search, filtering,
and pagination - Soft-delete recovery and permanent-deletion safeguards

Vue owns: - Admin Panel and student/teacher portal screens - Page
navigation - Forms and client-side interaction - Shared frontend state -
API requests and response handling - Loading, empty, success, and error
states

### 5.2 Blade Is Only the SPA Entry Point

Use a minimal Blade entry point, expected at:

`resources/views/app.blade.php`

Blade may provide: - Root HTML document - Vite asset loading - Vue mount
element - Required metadata and CSRF-related setup

Do not create Blade-rendered Admin Panel pages, student/teacher portal
pages, CRUD screens, data tables, or Vue-managed forms.

All application screens must be Vue components.

### 5.3 Vue Stack

Use the project's verified compatible versions of:

-   Vue 3
-   Composition API and `<script setup>`
-   TypeScript where the existing setup supports it
-   Vite
-   Vue Router
-   Pinia
-   Axios
-   AdminLTE as the UI foundation

Do not assume that every proposed file or folder already exists. Follow
the actual project setup and add structure incrementally.

### 5.4 No Full Page Reload --- Mandatory

After the initial application load, normal Vue-managed workflows must
not trigger a full browser document reload.

This applies to both Admin Panel and student/teacher portal screens.

Use: - Vue Router for internal navigation. - Axios for create, read,
update, delete, form submissions, search, filtering, sorting,
pagination, and uploads. - Vue state updates after successful API
responses. - Loading, validation, success, error, empty, and
confirmation states. - Duplicate-submit prevention while requests are
pending.

A full document load is allowed only for: - Initial application load. -
Explicit browser refresh. - Navigation outside the SPA.

Do not use native full-page form submission or regular full-page links
for internal Vue-managed workflows.

### 5.5 AdminLTE and jQuery

-   AdminLTE is the visual foundation, not a replacement for Vue.
-   Verify the AdminLTE major version and its Bootstrap/JavaScript
    dependencies before integrating assets.
-   Never mix incompatible AdminLTE/Bootstrap assets.
-   Do not use jQuery to directly manipulate DOM elements owned by Vue.
-   If a required AdminLTE JavaScript plugin is used, initialize and
    clean it up safely within the relevant Vue component lifecycle.

------------------------------------------------------------------------

## 6. Version and Environment Verification

Before installation commands, configuration changes, or version-specific
implementation, inspect the actual project.

Verify as relevant:

-   `composer.json` and `composer.lock`
-   `package.json` and the package lock file
-   PHP version
-   Composer version
-   Laravel version
-   Node.js and npm versions
-   MySQL version and configuration
-   Vue and Vite versions
-   AdminLTE major version and asset dependencies
-   Laravel Sanctum compatibility
-   Spatie Laravel Permission compatibility
-   Spatie Laravel Media Library compatibility

The documented baseline is Laravel 13, Vue 3, and MySQL. The actual
project configuration takes precedence for compatibility decisions.

Do not: - Guess package versions. - Install packages without checking
compatibility. - use deprecated or outdated APIs. - Replace existing
dependencies without a clear reason. - Provide a command as verified if
it has not been checked.

Use official documentation for version-specific behavior when
verification is necessary.

Commands should be Windows PowerShell-compatible by default unless the
user specifies another environment.

------------------------------------------------------------------------

## 7. Backend Implementation Standards

### 7.1 API

-   Use versioned API endpoints under `/api/v1`.
-   Keep API controllers under `app/Http/Controllers/Api/V1/` where
    compatible with the existing project.
-   Use RESTful resource routes.
-   Use Laravel Form Requests for request validation.
-   Use API Resources for response formatting.
-   Return appropriate HTTP status codes and consistent response
    structures.
-   Support server-side pagination, search, and filtering.
-   Enforce authentication, permissions, ownership, and assigned-record
    scope on the backend.

### 7.2 Authentication

Use Laravel Sanctum's cookie-based SPA authentication approach for the
planned same-origin SPA, subject to actual deployment configuration.

Correctly handle: - CSRF cookie initialization - Stateful domains -
Session authentication - Credentialed Axios requests - Logout and
session expiry - Protected API endpoints

Do not substitute token storage in the browser unless the architecture
is explicitly reviewed and approved.

### 7.3 Authorization

Use: - Spatie Laravel Permission for roles and permissions. - Laravel
middleware for endpoint-level permission checks. - Policies for
record-level access and ownership/assignment rules.

Examples: - Teachers may access only assigned batches and their
permitted academic data. - Students may access only their own records. -
Accountants may access financial functions according to permissions. -
Super Admin and Admin access must follow the approved role matrix.

Hiding a button or route in Vue is not security. Every protected API
action must enforce authorization in Laravel.

### 7.4 Services and Code Organization

-   Keep controllers focused.
-   Keep Eloquent relationships and model-specific behavior in models.
-   Use Form Requests for backend validation.
-   Use API Resources for response formatting.
-   Use Policies for record-level authorization.
-   Introduce Services only for meaningful business workflows or
    coordinated multi-record operations.
-   Do not create a Service class for every simple CRUD action.
-   Follow the approved folder structure while respecting the existing
    project.

### 7.5 Code Quality

-   Clean, concise, readable code.
-   Laravel conventions and SOLID/DRY where useful.
-   Separation of concerns.
-   Reusable components and logic where justified.
-   No unnecessary packages or abstractions.
-   No speculative features.
-   No redundant comments, validation, try/catch blocks, or logging
    without a concrete need.
-   Required backend validation, authorization, and security checks are
    mandatory and must not be omitted.

------------------------------------------------------------------------

## 8. Database and Eloquent Rules

### 8.1 Required Domain Tables

Follow the current `ERD.md` and `database-schema.md` for the proposed
domain model:

-   `users`
-   `student_profiles`
-   `teacher_profiles`
-   `categories`
-   `sub_categories`
-   `courses`
-   `batches`
-   `enrollments`
-   `class_sessions`
-   `attendance_records`
-   `fee_invoices`
-   `payments`
-   `announcements`
-   `announcement_targets` (proposed; confirm audience design)
-   `learning_materials`
-   `exams` (advanced planned module)
-   `exam_results` (advanced planned module)

Do not add or remove domain tables without reviewing the documents and
the requested feature.

### 8.2 Schema Rules

-   MySQL is the planned database.
-   Use Laravel migrations and Eloquent relationships.
-   Use primary keys and foreign keys as documented.
-   Use `DECIMAL` for money; never use floating-point database columns
    for monetary values.
-   Follow the proposed `VARCHAR(30)` status convention unless the
    approved schema or actual project dictates otherwise.
-   Add indexes for common queries and filters.
-   Preserve relational integrity.
-   Use database transactions for multi-step enrolment and financial
    workflows where required.
-   Avoid N+1 queries through appropriate eager loading.
-   Do not store duplicated calculated totals without an approved
    reason.
-   Do not add a fixed `role` column to `users`.
-   Do not add image foreign keys when Spatie Media Library polymorphic
    ownership is used.

### 8.3 Spatie Package Tables

Spatie package tables are package-managed.

For Spatie Laravel Permission: - Use the official migration
generated/published by the compatible installed package. - Do not
manually recreate `roles`, `permissions`, `model_has_roles`,
`model_has_permissions`, or `role_has_permissions`. - Verify the guard
and morph-key configuration.

For Spatie Laravel Media Library: - Use the package's migration for
`media`. - Treat `model_type` and `model_id` as a polymorphic
relationship. - Use the planned named collections. - Do not create
direct media foreign keys on each domain table.

### 8.4 Migration Order

Follow the dependency order in `database-schema.md`, currently proposed
as:

1.  Users
2.  Categories
3.  Sub-categories
4.  Courses
5.  Student profiles
6.  Teacher profiles
7.  Batches
8.  Enrolments
9.  Class sessions
10. Attendance records
11. Fee invoices
12. Payments
13. Announcements
14. Announcement targets
15. Learning materials
16. Exams
17. Exam results
18. Package-generated Spatie migrations according to actual package
    dependencies

Verify actual package migration dependencies before finalizing the
order.

### 8.5 Unresolved Database Decisions

Do not guess or silently finalize these decisions:

-   Whether every student and teacher must have a login account.
-   Whether a batch can have multiple teachers or one primary teacher.
-   Whether re-enrolment reactivates an existing enrolment or creates a
    new history row.
-   Whether enrolments use SoftDeletes or withdrawal status only.
-   Whether sub-category slugs are globally unique or unique within a
    category.
-   Exact invoice/installment and payment rules.
-   Announcement audience target types.
-   Learning-material visibility scope.
-   Whether user accounts need SoftDeletes or deactivation is
    sufficient.
-   Which announcements and learning materials use SoftDeletes.
-   Exam retakes/attempts and grading rules.
-   Exact media conversions and sizes.

When a task depends on one of these decisions, ask the user before
implementing the affected schema or business logic.

------------------------------------------------------------------------

## 9. Soft Delete, Trash, Restore, and Force Delete

This workflow is required only for approved eligible models.

### 9.1 Eligible Models

The current documents plan Soft Delete, Restore, and Force Delete for: -
Categories - Sub-categories - Courses - Batches - Student profiles -
Teacher profiles - Users, only if user SoftDeletes is approved - Other
approved master/content records

Announcements and learning materials are optional and require
confirmation.

Enrolment SoftDeletes remain undecided.

### 9.2 Protected Historical Records

Do not add generic permanent deletion for: - Attendance records - Fee
invoices - Payments - Published exam results - Other protected financial
or academic history

Use cancellation, voiding, correction, or archival workflows only when
approved.

### 9.3 Required Backend Behavior

-   Active listings use normal Eloquent queries.
-   Trash listings use `onlyTrashed()`.
-   Restore uses `restore()`.
-   Force Delete uses `forceDelete()` and targets trashed records only.
-   Restore routes must explicitly resolve soft-deleted records.
-   Force Delete routes must not permanently delete active records.
-   Restore and Force Delete use separate permissions.
-   Check dependencies and retention rules before permanent deletion.
-   Never cascade-force-delete financial or academic history because a
    parent record is deleted.

### 9.4 API Pattern

For approved resources, follow the documented pattern:

``` text
GET    /api/v1/{resource}/trash
PATCH  /api/v1/{resource}/{record}/restore
DELETE /api/v1/{resource}/{record}/force
```

All Trash, Restore, and Force Delete interactions must use Axios and
update Vue state without a full page reload. Restore and Force Delete
require clear user confirmation.

------------------------------------------------------------------------

## 10. Media and File Security

Use Spatie Laravel Media Library for model-associated files.

Planned collections: - `avatar` - `category-image` -
`subcategory-image` - `course-cover` - `announcement-attachments` -
`learning-materials` - `student-documents`

Rules: - Validate file type and size on the backend. - Use public
storage only for assets intended to be public. - Store student documents
and private files on a private disk. - Serve private files through
authorized backend endpoints. - Never expose private student documents
through unrestricted public URLs. - Handle media replacement/removal
consistently with the owning model. - Do not add unnecessary media
columns to domain tables.

------------------------------------------------------------------------

## 11. Frontend Implementation Standards

### 11.1 Feature-Based Structure

Follow `folder-structure.md` and the actual existing project.

The proposed frontend root is `resources/js/`, with: - `app.ts` -
`bootstrap.ts` - `api/` - `router/` - `stores/` - `layouts/` -
`components/` - `modules/` - `composables/` - `types/` - optional
`styles/`

Keep feature-specific pages, components, API functions, and types inside
their module when appropriate.

### 11.2 Responsibilities

-   `app.ts`: create Vue app, register Router and Pinia, load
    styles/assets, mount Vue.
-   `api/http.ts`: shared Axios instance and common HTTP configuration.
-   `router/`: route definitions and navigation guards.
-   `stores/`: shared application state only.
-   `layouts/`: Admin, Portal, and Auth Vue layouts.
-   `components/`: genuinely shared UI components.
-   `modules/`: feature-specific screens, components, API functions, and
    types.
-   `composables/`: reusable Composition API logic.
-   `types/`: shared TypeScript types.

Do not create every proposed directory at project initialization. Add
folders when the corresponding feature is implemented.

### 11.3 UI States

For asynchronous operations, handle the relevant: - Loading state -
Empty state - Validation errors - Permission errors - Server errors -
Success feedback - Confirmation state - Duplicate-submit prevention

Do not allow an Axios request to silently fail while leaving the UI in a
misleading state.

------------------------------------------------------------------------

## 12. Development Workflow --- Follow for Every Task

### Step 1: Documentation Review

Follow Section 2. Read `Agent.md` and all project-authored Markdown
documents before work.

### Step 2: Understand the Request

-   Restate the requested outcome internally.
-   Identify affected modules and user roles.
-   Identify applicable PRD requirement IDs.
-   Identify database relationships and UI/API impact.
-   Identify acceptance criteria.

### Step 3: Inspect the Existing Project

Before changing code: - Inspect the relevant folder structure. - Read
the actual controllers, models, routes, requests, resources, migrations,
Vue components, stores, and API client. - Inspect `composer.json`,
`package.json`, and lock files when versions matter. - Check Git status
and existing user changes when Git is available. - Do not overwrite
unrelated or uncommitted work.

### Step 4: Analyze and Plan

Explain briefly: - Current behavior. - Expected behavior. - Relevant
application flow. - Files likely to change. - Database impact. -
Authorization and security impact. - Risks or unresolved decisions.

Do not claim a root cause without evidence.

### Step 5: Implement Incrementally

-   Make the smallest complete change that satisfies the request.
-   Preserve the existing architecture and working functionality.
-   Implement one module or coherent feature at a time.
-   Avoid unrelated refactoring.
-   Do not install a package unless necessary and compatible.
-   Do not create files that are not needed for the current task.

### Step 6: Verify

Run relevant tests, checks, and build commands when possible.

Verify: - Backend behavior. - API response and validation. -
Authentication and permissions. - Database relationships and
integrity. - Frontend interaction and state updates. -
No-full-page-reload behavior. - Responsive behavior when UI changes. -
Existing functionality was not unintentionally broken.

Do not claim tests passed unless they were actually run and passed.

### Step 7: Report

At the end, provide: - What changed. - Files created/updated. -
Important implementation decisions. - Commands/tests executed and their
actual results. - Any remaining issue or unverified behavior. - The next
useful step, if applicable.

------------------------------------------------------------------------

## 13. Debugging Workflow

When the user provides an error, screenshot, or bug report:

1.  **Error Meaning:** Explain what the error means.
2.  **Current Behavior:** Identify what is happening.
3.  **Expected Behavior:** Identify what should happen.
4.  **Root Cause:** State only what is supported by inspected code,
    logs, or reproducible evidence. Label hypotheses as hypotheses.
5.  **Application Flow:** Identify the Laravel/Vue/API/database layer
    involved.
6.  **Files to Check:** Inspect the relevant files before recommending
    edits.
7.  **Solution:** Explain the minimum required correction.
8.  **Updated Code:** Provide complete corrected sections/files when
    safe and useful.
9.  **Verification:** Give precise steps to confirm the fix.

Do not: - Guess the root cause from an error title alone. - Modify
unrelated files. - Replace a complete file without reading its current
content. - Hide errors with broad exception handling. - Add unnecessary
logging or defensive code without a concrete need.

------------------------------------------------------------------------

## 14. Testing and Quality Gates

Test the relevant areas for each feature.

### Backend

-   Authentication and logout
-   API validation
-   Authorization and Policies
-   Record ownership and assigned-batch scope
-   CRUD behavior
-   Database constraints and relationships
-   Duplicate active enrolment prevention
-   Duplicate attendance prevention
-   Invoice/payment consistency
-   Transactions for multi-step workflows
-   Media upload and private document access
-   Trash/Restore/Force Delete restrictions

### Frontend

-   Vue Router internal navigation
-   Axios API communication
-   Form validation feedback
-   Loading, empty, success, and error states
-   Search, filters, sorting, and pagination
-   Upload preview and upload feedback
-   Permission-aware UI
-   No full page reload for Vue-managed workflows
-   State updates after create, update, delete, restore, and upload

### Project Health

-   Application remains runnable after each milestone.
-   No secrets are committed.
-   No unnecessary dependencies are introduced.
-   No protected academic or financial history is lost.
-   No unrelated feature is broken.

------------------------------------------------------------------------

## 15. Documentation Maintenance

When an approved implementation decision changes the product,
architecture, database, or folder organization:

1.  Identify which Markdown documents are affected.
2.  Explain the proposed documentation change.
3.  Update the relevant documents consistently when authorized.
4.  Keep `ERD.md` and `database-schema.md` aligned.
5.  Keep `folder-structure.md` aligned with the actual approved
    organization.
6.  Keep `PRD.md` aligned with approved functional behavior.
7.  Do not silently rewrite requirements to match an implementation
    shortcut.
8.  Preserve unresolved decisions as unresolved until the user confirms
    them.

If the implementation reveals that a source document is incorrect or
incomplete, report the evidence and propose a correction instead of
silently changing the design.

------------------------------------------------------------------------

## 16. Communication Rules

-   Explain in simple Bangla.
-   Keep technical terms and code identifiers in English.
-   Be concise but include important reasoning.
-   Do not overwhelm the user with unrelated topics.
-   Ask only the most important clarification when a decision blocks
    safe implementation.
-   Do not assume the user wants a full project rewrite when they ask
    for one feature.
-   Do not claim a file was created, code was changed, or a test passed
    unless it actually happened.
-   When giving commands, use Windows PowerShell syntax by default.
-   Warn before destructive database, filesystem, or Git commands.
-   Never expose `.env` secrets, credentials, tokens, or private student
    data in responses.

------------------------------------------------------------------------

## 17. Definition of Done

A task is complete only when:

-   The relevant project Markdown documents were reviewed.
-   Existing implementation was inspected.
-   The requested behavior is implemented within the approved scope.
-   Backend validation and authorization are enforced where required.
-   Database integrity and protected historical data are preserved.
-   Vue-managed workflows do not cause full page reloads.
-   Relevant tests/checks were run or remaining verification limits were
    clearly stated.
-   The project remains runnable to the extent verified.
-   Documentation was updated when an approved design decision changed.

**Final rule:** Read the project documentation first, inspect the actual
code second, plan before editing, implement only the approved scope, and
verify before reporting completion.
