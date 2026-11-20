# CoachDesk --- Folder Structure

> **Document Type:** Application Folder and Code Organization
> Specification\
> **Product:** CoachDesk --- Coaching Management System\
> **Backend:** Laravel 13\
> **Frontend:** Vue 3 + Composition API + `<script setup>` + TypeScript
> (preferred)\
> **Build Tool:** Vite\
> **UI Foundation:** AdminLTE\
> **API:** Laravel REST API + Axios\
> **Routing:** Vue Router\
> **State Management:** Pinia\
> **Authorization:** Spatie Laravel Permission\
> **Media:** Spatie Laravel Media Library\
> **Source Documents:** `project-overview.md`, `PRD.md`, and `ERD.md`\
> **Status:** Proposed structure --- create folders as each module is
> implemented

------------------------------------------------------------------------

## 1. Purpose

This document defines the proposed Laravel + Vue folder structure for
CoachDesk.

The project will use **one Laravel repository** containing: - Laravel
backend code and REST API. - Vue 3 SPA frontend. - A minimal Blade SPA
entry point. - AdminLTE dashboard assets and styling. - Database
migrations, seeders, factories, and tests. - Project documentation.

The structure is intended to keep backend and frontend responsibilities
separate, support feature-based development, and avoid creating
unnecessary folders before they are needed.

------------------------------------------------------------------------

## 2. Architecture Rules

1.  Laravel is responsible for authentication, authorization,
    validation, business rules, database access, and media access.
2.  Vue 3 is responsible for Admin Panel and student/teacher portal
    screens.
3.  Blade is used only as the minimal SPA entry point in the initial
    architecture.
4.  Vue Router handles internal navigation.
5.  Axios handles all Vue-managed CRUD, forms, search, filtering,
    pagination, and uploads.
6.  Pinia manages shared frontend state.
7.  AdminLTE provides the Admin Panel visual foundation; it does not
    replace Vue components.
8.  Avoid jQuery DOM manipulation on elements managed by Vue.
9.  Laravel middleware and Policies enforce backend authorization;
    frontend guards are not a security boundary.
10. Implement one module at a time. Do not create every proposed file at
    project initialization.

------------------------------------------------------------------------

## 3. Proposed Root Structure

``` text
coachdesk/
│
├── app/
│   ├── Enums/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── Auth/
│   │   │           ├── Dashboard/
│   │   │           ├── UserController.php
│   │   │           ├── CategoryController.php
│   │   │           ├── SubCategoryController.php
│   │   │           ├── CourseController.php
│   │   │           ├── BatchController.php
│   │   │           ├── StudentController.php
│   │   │           ├── TeacherController.php
│   │   │           ├── EnrollmentController.php
│   │   │           ├── ClassSessionController.php
│   │   │           ├── AttendanceController.php
│   │   │           ├── FeeInvoiceController.php
│   │   │           ├── PaymentController.php
│   │   │           ├── AnnouncementController.php
│   │   │           ├── LearningMaterialController.php
│   │   │           ├── ExamController.php
│   │   │           └── ExamResultController.php
│   │   │
│   │   ├── Requests/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── Auth/
│   │   │           ├── Category/
│   │   │           ├── SubCategory/
│   │   │           ├── Course/
│   │   │           ├── Batch/
│   │   │           ├── Student/
│   │   │           ├── Teacher/
│   │   │           ├── Enrollment/
│   │   │           ├── Attendance/
│   │   │           └── Finance/
│   │   │
│   │   ├── Resources/
│   │   │   └── Api/
│   │   │       └── V1/
│   │   │           ├── UserResource.php
│   │   │           ├── CategoryResource.php
│   │   │           ├── SubCategoryResource.php
│   │   │           ├── CourseResource.php
│   │   │           ├── BatchResource.php
│   │   │           ├── StudentResource.php
│   │   │           ├── TeacherResource.php
│   │   │           ├── EnrollmentResource.php
│   │   │           ├── AttendanceResource.php
│   │   │           ├── FeeInvoiceResource.php
│   │   │           └── PaymentResource.php
│   │   │
│   │   └── Middleware/
│   │
│   ├── Models/
│   │   ├── User.php
│   │   ├── StudentProfile.php
│   │   ├── TeacherProfile.php
│   │   ├── Category.php
│   │   ├── SubCategory.php
│   │   ├── Course.php
│   │   ├── Batch.php
│   │   ├── Enrollment.php
│   │   ├── ClassSession.php
│   │   ├── AttendanceRecord.php
│   │   ├── FeeInvoice.php
│   │   ├── Payment.php
│   │   ├── Announcement.php
│   │   ├── AnnouncementTarget.php
│   │   ├── LearningMaterial.php
│   │   ├── Exam.php
│   │   └── ExamResult.php
│   │
│   ├── Policies/
│   │   ├── CategoryPolicy.php
│   │   ├── CoursePolicy.php
│   │   ├── BatchPolicy.php
│   │   ├── StudentProfilePolicy.php
│   │   ├── TeacherProfilePolicy.php
│   │   ├── EnrollmentPolicy.php
│   │   └── ...
│   │
│   ├── Services/
│   │   ├── EnrollmentService.php
│   │   └── PaymentService.php
│   │
│   └── Providers/
│
├── bootstrap/
│   └── app.php
│
├── config/
│   ├── app.php
│   ├── auth.php
│   ├── database.php
│   ├── filesystems.php
│   ├── permission.php
│   └── media-library.php
│
├── database/
│   ├── factories/
│   │   ├── UserFactory.php
│   │   ├── StudentProfileFactory.php
│   │   ├── TeacherProfileFactory.php
│   │   └── ...
│   │
│   ├── migrations/
│   │   ├── create_users_table.php
│   │   ├── create_categories_table.php
│   │   ├── create_sub_categories_table.php
│   │   ├── create_courses_table.php
│   │   ├── create_student_profiles_table.php
│   │   ├── create_teacher_profiles_table.php
│   │   ├── create_batches_table.php
│   │   ├── create_enrollments_table.php
│   │   ├── create_class_sessions_table.php
│   │   ├── create_attendance_records_table.php
│   │   ├── create_fee_invoices_table.php
│   │   ├── create_payments_table.php
│   │   ├── create_announcements_table.php
│   │   ├── create_announcement_targets_table.php
│   │   ├── create_learning_materials_table.php
│   │   ├── create_exams_table.php
│   │   └── create_exam_results_table.php
│   │
│   └── seeders/
│       ├── DatabaseSeeder.php
│       ├── RolesAndPermissionsSeeder.php
│       ├── SuperAdminSeeder.php
│       └── ...
│
├── docs/
│   ├── project-overview.md
│   ├── PRD.md
│   ├── ERD.md
│   ├── database-schema.md
│   └── folder-structure.md
│
├── public/
│   ├── build/
│   └── ...
│
├── resources/
│   ├── css/
│   │   ├── app.css
│   │   └── adminlte-overrides.css
│   │
│   ├── js/
│   │   ├── app.ts
│   │   ├── bootstrap.ts
│   │   │
│   │   ├── api/
│   │   │   ├── http.ts
│   │   │   └── ...
│   │   │
│   │   ├── router/
│   │   │   ├── index.ts
│   │   │   ├── routes.ts
│   │   │   └── guards.ts
│   │   │
│   │   ├── stores/
│   │   │   ├── auth.ts
│   │   │   ├── notification.ts
│   │   │   └── ...
│   │   │
│   │   ├── layouts/
│   │   │   ├── AdminLayout.vue
│   │   │   ├── PortalLayout.vue
│   │   │   └── AuthLayout.vue
│   │   │
│   │   ├── components/
│   │   │   ├── common/
│   │   │   ├── forms/
│   │   │   ├── tables/
│   │   │   ├── feedback/
│   │   │   └── navigation/
│   │   │
│   │   ├── modules/
│   │   │   ├── dashboard/
│   │   │   ├── auth/
│   │   │   ├── users/
│   │   │   ├── categories/
│   │   │   ├── sub-categories/
│   │   │   ├── courses/
│   │   │   ├── batches/
│   │   │   ├── students/
│   │   │   ├── teachers/
│   │   │   ├── enrollments/
│   │   │   ├── attendance/
│   │   │   ├── finance/
│   │   │   ├── announcements/
│   │   │   ├── learning-materials/
│   │   │   ├── exams/
│   │   │   └── settings/
│   │   │
│   │   ├── composables/
│   │   │   ├── usePagination.ts
│   │   │   ├── usePermissions.ts
│   │   │   └── ...
│   │   │
│   │   ├── types/
│   │   │   ├── api.ts
│   │   │   ├── user.ts
│   │   │   └── ...
│   │   │
│   │   └── styles/
│   │       ├── adminlte.ts
│   │       └── ...
│   │
│   └── views/
│       └── app.blade.php
│
├── routes/
│   ├── api.php
│   ├── web.php
│   └── console.php
│
├── storage/
│   ├── app/
│   │   ├── private/
│   │   └── public/
│   ├── framework/
│   └── logs/
│
├── tests/
│   ├── Feature/
│   │   └── Api/
│   │       └── V1/
│   │           ├── Auth/
│   │           ├── Category/
│   │           ├── Course/
│   │           ├── Batch/
│   │           ├── Enrollment/
│   │           ├── Attendance/
│   │           └── Finance/
│   │
│   └── Unit/
│
├── .env
├── .env.example
├── .gitignore
├── artisan
├── composer.json
├── package.json
├── phpunit.xml
├── tsconfig.json
├── vite.config.ts
└── README.md
```

This is a target structure. Laravel's standard files and any
package-generated files should be preserved. Create feature folders only
when their module is implemented.

------------------------------------------------------------------------

## 4. Backend Folder Responsibilities

### 4.1 `app/Models/`

Contains Eloquent models and database relationships.

Examples: - `Category.php`: `subCategories()`, media collection. -
`SubCategory.php`: `category()`, `courses()`. - `Course.php`:
`subCategory()`, `batches()`. - `Batch.php`: `course()`, `teacher()`,
`enrollments()`, `classSessions()`. - `Enrollment.php`: `student()`,
`batch()`, `attendanceRecords()`, `feeInvoices()`. - `FeeInvoice.php`:
`enrollment()`, `payments()`.

Models should contain relationships, casts, scopes, and model-specific
behavior. Do not place all application workflows inside models.

### 4.2 `app/Http/Controllers/Api/V1/`

Contains API controllers grouped by API version.

Responsibilities: - Receive API requests. - Call validation through Form
Requests. - Authorize the requested action. - Coordinate the relevant
model or business workflow. - Return API Resources and appropriate
status codes.

Controllers should remain focused. Avoid duplicating business rules
across multiple controllers.

### 4.3 `app/Http/Requests/Api/V1/`

Contains Form Request classes for backend validation.

Examples: - `StoreCategoryRequest.php` - `UpdateCategoryRequest.php` -
`StoreCourseRequest.php` - `StoreEnrollmentRequest.php` -
`StorePaymentRequest.php`

Use separate requests when create and update rules differ meaningfully.

### 4.4 `app/Http/Resources/Api/V1/`

Contains API response transformers.

Examples: - `CategoryResource.php` - `CourseResource.php` -
`BatchResource.php` - `StudentResource.php` - `FeeInvoiceResource.php`

Resources should return consistent, intentional response fields. Do not
expose private student data or internal model fields unnecessarily.

### 4.5 `app/Policies/`

Contains authorization policies for resource-level access.

Examples: - `CategoryPolicy.php` - `StudentProfilePolicy.php` -
`BatchPolicy.php` - `EnrollmentPolicy.php`

Use Spatie permissions for role/permission checks and Policies for
record-level rules such as: - Teacher can access only assigned
batches. - Student can access only their own profile and records. -
Accountant can access financial modules according to permission.

### 4.6 `app/Services/`

Use services only for workflows that contain meaningful business logic
or coordinate multiple records.

Initial candidates: - `EnrollmentService.php`: capacity checks,
duplicate enrolment checks, enrolment workflow. - `PaymentService.php`:
payment validation, transaction handling, invoice balance/status
consistency.

Do not create a Service class for every simple CRUD endpoint.

### 4.7 `app/Enums/`

Optional location for PHP enums when the project benefits from strongly
named status values.

Possible examples: - `BatchStatus.php` - `AttendanceStatus.php` -
`EnrollmentStatus.php` - `InvoiceStatus.php`

Use only after the allowed status values are finalized. Do not duplicate
the same status definitions in multiple places.

------------------------------------------------------------------------

## 5. Frontend Folder Responsibilities

The frontend uses a feature-oriented structure under `resources/js/`.

### 5.1 `resources/js/app.ts`

Main Vue application entry point.

Responsibilities: - Create the Vue application. - Register Vue Router. -
Register Pinia. - Load global styles and compatible AdminLTE assets. -
Mount the application to the root element from `app.blade.php`.

### 5.2 `resources/js/bootstrap.ts`

Frontend bootstrap configuration.

Responsibilities may include: - Shared Axios defaults or CSRF-related
setup. - Global frontend initialization. - Other small application-wide
setup tasks.

Keep API client configuration centralized in `api/http.ts`.

### 5.3 `resources/js/api/`

Contains the shared HTTP client and common API configuration.

Example:

``` text
api/
├── http.ts
└── ...
```

`http.ts` should centralize: - Axios instance. - Base URL. - Credential
configuration required by Sanctum SPA authentication. - Common
request/response behavior. - Shared error handling conventions.

Feature-specific endpoint functions may live inside their module folder
when that keeps code easier to maintain.

### 5.4 `resources/js/router/`

Contains Vue Router configuration.

  File          Responsibility
  ------------- --------------------------------------
  `index.ts`    Create and export the router
  `routes.ts`   Route definitions
  `guards.ts`   Authentication and navigation guards

Route guards improve navigation and user experience. Laravel API
authorization remains the real security boundary.

### 5.5 `resources/js/stores/`

Contains Pinia stores for shared application state.

Examples: - `auth.ts`: authenticated user and authentication state. -
`notification.ts`: shared notification/toast state.

Add more stores only when state genuinely needs to be shared across
multiple components.

### 5.6 `resources/js/layouts/`

Contains high-level application layouts.

  Component            Responsibility
  -------------------- -------------------------------------------------
  `AdminLayout.vue`    AdminLTE navbar, sidebar, content shell, footer
  `PortalLayout.vue`   Student/teacher portal layout
  `AuthLayout.vue`     Login and authentication pages

These are Vue layouts, not Blade layouts.

### 5.7 `resources/js/components/`

Contains reusable UI components shared across multiple feature modules.

Suggested groups: - `common/`: buttons, badges, modal wrappers, reusable
UI elements. - `forms/`: shared form fields and validation display. -
`tables/`: reusable data-table components. - `feedback/`: loading,
empty, error, and confirmation UI. - `navigation/`: reusable sidebar and
navigation elements.

Do not put a feature-specific component here if it is only used by one
module. Keep it inside that module.

### 5.8 `resources/js/modules/`

Contains feature-specific Vue pages, components, API functions, and
types.

Recommended module pattern:

``` text
modules/
└── categories/
    ├── api/
    │   └── categories.ts
    ├── components/
    │   └── CategoryForm.vue
    ├── pages/
    │   ├── CategoryIndex.vue
    │   └── CategoryTrash.vue
    └── types.ts
```

Apply this pattern only where useful. A small module does not need every
subfolder.

Suggested modules:

  Module                  Main Responsibilities
  ----------------------- -------------------------------------------
  `auth/`                 Login, password, authenticated screens
  `dashboard/`            Role-specific dashboards
  `users/`                User management
  `categories/`           Category CRUD and Trash
  `sub-categories/`       Sub-category CRUD and Trash
  `courses/`              Course CRUD and media
  `batches/`              Batch CRUD, teacher assignment, schedules
  `students/`             Student profiles and documents
  `teachers/`             Teacher profiles and assignments
  `enrollments/`          Student-to-batch enrolments
  `attendance/`           Sessions and attendance
  `finance/`              Fee invoices and payments
  `announcements/`        Notices and audience selection
  `learning-materials/`   Notes and learning resources
  `exams/`                Advanced exam and result features
  `settings/`             Authorized application settings

Every module's normal CRUD, forms, search, filtering, pagination,
uploads, Trash, Restore, and Force Delete actions must use Axios/API
communication. They must update Vue state without a full page reload.

### 5.9 `resources/js/composables/`

Contains reusable Vue Composition API logic shared by multiple modules.

Possible examples: - `usePagination.ts` - `usePermissions.ts`

Add a composable only when the logic is reused or has a clear reusable
responsibility. Feature-specific composables may remain inside their
feature module.

### 5.10 `resources/js/types/`

Contains shared TypeScript types.

Examples: - `api.ts`: common API response and pagination types. -
`user.ts`: authenticated user and profile types. - `permissions.ts`:
permission names and authorization-related types, if needed.

Feature-specific types should remain inside the relevant module when
they are not shared.

### 5.11 `resources/css/` and `resources/js/styles/`

-   `resources/css/app.css`: global application styles.
-   `resources/css/adminlte-overrides.css`: project-specific AdminLTE
    overrides.
-   `resources/js/styles/`: optional JavaScript/TypeScript style imports
    or integration helpers if required by the selected AdminLTE package.

Use the asset strategy compatible with the chosen AdminLTE major
version. Do not mix incompatible Bootstrap or AdminLTE assets.

------------------------------------------------------------------------

## 6. Blade and Routing Responsibilities

### 6.1 `resources/views/app.blade.php`

This is the minimal Blade entry point.

It should: - Provide the root HTML document. - Load Vite assets. -
Provide the Vue mount element. - Include required meta/CSRF
configuration.

It should not contain: - Admin dashboard page markup. - Student or
teacher portal pages. - CRUD forms. - Data tables. - Business logic. -
Traditional full-page form submissions for Vue-managed workflows.

### 6.2 `routes/web.php`

Use for: - Serving the initial SPA entry page. - A suitable SPA fallback
for Vue Router-managed paths, excluding API paths and other reserved
routes.

Do not define separate Blade-rendered pages for each Admin Panel module.

### 6.3 `routes/api.php`

Use for: - Versioned REST API routes under `/api/v1`. - Authentication
endpoints. - Dashboard endpoints. - Resource CRUD endpoints. - Search,
filtering, and pagination endpoints. - Trash, Restore, and Force Delete
endpoints for eligible resources. - Media upload/download endpoints.

Laravel 13 projects may need API routing to be enabled during setup.
Confirm the actual project configuration before creating or modifying
route files.

------------------------------------------------------------------------

## 7. Database and Seeder Organization

### 7.1 `database/migrations/`

Contains domain-table migrations in dependency order.

Recommended order: 1. Users. 2. Categories. 3. Sub-categories. 4.
Courses. 5. Student profiles. 6. Teacher profiles. 7. Batches. 8.
Enrolments. 9. Class sessions. 10. Attendance records. 11. Fee invoices.
12. Payments. 13. Announcements. 14. Announcement targets. 15. Learning
materials. 16. Exams. 17. Exam results. 18. Package-generated Spatie
migrations according to actual package migration dependencies.

Do not manually write package table migrations when the package supplies
its own.

### 7.2 `database/seeders/`

Recommended seeders: - `DatabaseSeeder.php`: orchestrates initial
seeders. - `RolesAndPermissionsSeeder.php`: creates approved roles and
permissions. - `SuperAdminSeeder.php`: creates the initial Super Admin
account using environment-safe setup.

Do not hard-code real production passwords in committed seeders.

### 7.3 `database/factories/`

Contains factories for test and development data: - `UserFactory.php` -
`StudentProfileFactory.php` - `TeacherProfileFactory.php` - Other
factories as the related modules are implemented.

------------------------------------------------------------------------

## 8. Tests Organization

### 8.1 Feature Tests

Use `tests/Feature/Api/V1/` for API behavior and authorization.

Suggested groups: - `Auth/` - `Category/` - `Course/` - `Batch/` -
`Enrollment/` - `Attendance/` - `Finance/`

Test: - Authentication and session behavior. - Permission enforcement. -
Validation and API responses. - Relationship integrity. - Duplicate
enrolment and attendance prevention. - Financial consistency. - Media
access. - Trash, Restore, and Force Delete safeguards.

### 8.2 Unit Tests

Use `tests/Unit/` for isolated business logic where unit testing is
useful, such as: - Payment balance calculations. - Enrolment
eligibility/capacity rules. - Other non-trivial domain logic.

Do not create empty test files for every class. Add tests with the
corresponding feature.

### 8.3 Frontend Tests

The exact Vue testing framework and directory convention will be
selected after checking the installed frontend toolchain. Keep frontend
tests near the feature or in the agreed test directory; do not add a new
testing package without a reason.

------------------------------------------------------------------------

## 9. Configuration and Storage

### 9.1 `config/`

Relevant configuration files may include: - `permission.php`: Spatie
Laravel Permission configuration. - `media-library.php`: Spatie Media
Library configuration. - `filesystems.php`: public and private media
disks. - `auth.php`: Laravel authentication configuration.

Use package-published config files from the compatible installed package
versions.

### 9.2 `bootstrap/app.php`

Laravel 13 application bootstrap configuration, including middleware and
route configuration where needed.

### 9.3 `storage/`

Use Laravel storage disks for uploaded files.

Suggested storage policy: - Public course/category images may use a
public disk when intended. - Student documents and other private files
must use a private disk. - Private files must be served through
authorized backend endpoints. - Do not expose private documents through
unrestricted public URLs.

The exact storage folders are determined by the configured disks and
Spatie Media Library configuration.

------------------------------------------------------------------------

## 10. Documentation Structure

Keep project documentation together under `docs/`:

``` text
docs/
├── project-overview.md
├── PRD.md
├── ERD.md
├── database-schema.md
└── folder-structure.md
```

The root `README.md` should explain: - Project purpose. -
Prerequisites. - Environment setup. - Installation and run commands
after versions are confirmed. - Database setup. - Frontend build/dev
commands. - Testing commands. - Links to detailed documentation.

Do not store `.env` secrets in documentation or version control.

------------------------------------------------------------------------

## 11. API and Frontend Module Naming Example

For the Category module, the responsibilities may be organized as
follows.

**Backend**

``` text
app/
├── Models/Category.php
├── Http/Controllers/Api/V1/CategoryController.php
├── Http/Requests/Api/V1/Category/
│   ├── StoreCategoryRequest.php
│   └── UpdateCategoryRequest.php
├── Http/Resources/Api/V1/CategoryResource.php
└── Policies/CategoryPolicy.php
```

**Frontend**

``` text
resources/js/
├── modules/categories/
│   ├── api/categories.ts
│   ├── components/CategoryForm.vue
│   ├── pages/CategoryIndex.vue
│   ├── pages/CategoryTrash.vue
│   └── types.ts
└── router/
    └── routes.ts
```

**Database**

``` text
database/
└── migrations/
    └── create_categories_table.php
```

This pattern is illustrative. Keep the implementation concise and create
only the files the module actually needs.

------------------------------------------------------------------------

## 12. Folder Structure Rules for Development

1.  Preserve Laravel's standard application structure.
2.  Keep API controllers under `Api/V1`.
3.  Use Form Requests for backend request validation.
4.  Use API Resources for API response formatting.
5.  Use Policies and Spatie permissions for backend authorization.
6.  Keep Vue pages and feature-specific components inside their module.
7.  Put only genuinely shared UI components in `components/`.
8.  Put only genuinely shared state in Pinia stores.
9.  Put repeated reactive logic in composables.
10. Keep Axios configuration centralized.
11. Use one minimal Blade SPA entry point.
12. Do not create separate Blade pages for Admin Panel or portal
    modules.
13. Do not use jQuery to mutate Vue-managed DOM.
14. All Vue-managed internal navigation and CRUD/form actions must avoid
    full page reloads.
15. Add Trash/Restore/Force Delete UI and API behavior only for models
    approved in the database deletion policy.
16. Do not expose financial or academic history to unsafe permanent
    deletion.
17. Do not create every proposed folder at the beginning; add folders as
    the project grows.
18. Keep the application runnable after each implementation milestone.

------------------------------------------------------------------------

## 13. Implementation Checklist

### Initial Foundation

-   [ ] Verify Laravel, PHP, Composer, Node.js, and npm versions.
-   [ ] Verify the current Laravel project structure.
-   [ ] Confirm API routing is enabled.
-   [ ] Verify Vue 3 and Vite compatibility.
-   [ ] Select a compatible AdminLTE major version and asset strategy.
-   [ ] Verify Spatie Permission compatibility.
-   [ ] Verify Spatie Media Library compatibility.
-   [ ] Create the minimal Blade SPA entry point.
-   [ ] Create the Vue application entry.
-   [ ] Configure Vue Router, Pinia, and Axios.
-   [ ] Establish AdminLTE layout without jQuery conflicts.
-   [ ] Confirm internal navigation and test API requests do not cause
    full page reloads.

### Backend Foundation

-   [ ] Confirm the database schema and open decisions.
-   [ ] Create domain migrations in dependency order.
-   [ ] Install and configure Spatie packages using compatible versions.
-   [ ] Create core Eloquent models and relationships.
-   [ ] Configure API versioning.
-   [ ] Configure Sanctum SPA authentication.
-   [ ] Configure roles, permissions, middleware, and Policies.

### Module Implementation

-   [ ] Implement one module at a time.
-   [ ] Add backend routes, requests, controllers, and resources.
-   [ ] Add frontend pages and reusable components.
-   [ ] Add Axios API functions.
-   [ ] Add loading, validation, success, and error feedback.
-   [ ] Add search, filtering, and pagination where required.
-   [ ] Add Trash/Restore/Force Delete only where approved.
-   [ ] Test permission boundaries and data integrity.
-   [ ] Verify no-full-page-reload behavior.

------------------------------------------------------------------------

## 14. Source Alignment and Open Decisions

This structure follows: - `project-overview.md` for the Laravel + Vue
architecture, minimal Blade entry, AdminLTE foundation, module scope,
package choices, and development principles. - `PRD.md` for SPA
behavior, API communication, user roles, access control,
Trash/Restore/Force Delete, and acceptance criteria. - `ERD.md` for
model names, domain tables, relationships, Spatie package relationships,
and data-retention rules.

The structure is proposed rather than an assertion that all folders or
files already exist. The exact package asset integration, API route
setup, test tooling, and any additional module folders must be confirmed
against the actual project environment.

**Folder principle:** Keep Laravel backend code, Vue feature modules,
shared frontend infrastructure, and project documentation clearly
separated. Create only what the current milestone needs.
