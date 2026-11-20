# CoachDesk --- Coaching Management System

> **Project Type:** Small, modular coaching-centre management
> application\
> **Backend:** Laravel 12 + PHP + MySQL\
> **Frontend:** Vue 3 + Composition API + TypeScript (preferred) + Vite\
> **Admin UI:** AdminLTE\
> **API Communication:** Axios + Laravel REST API\
> **Access Control:** Spatie Laravel Permission\
> **Media Management:** Spatie Laravel Media Library\
> **Document Status:** Initial Project Overview --- implementation has
> not started

------------------------------------------------------------------------

## 1. Project Overview

**CoachDesk** is a web-based Coaching Management System designed to
manage the daily academic and administrative activities of a coaching
centre from one dashboard.

The application will use **Laravel as the backend** and **Vue 3 as the
frontend**. Laravel will manage authentication, business logic, database
operations, validation, permissions, and file handling. Vue will provide
a responsive, interactive user interface. AdminLTE will provide the
dashboard layout and visual foundation.

The first release will target a **single coaching centre**. The
structure should remain modular so that multi-branch support can be
considered later without making the initial project unnecessarily
complex.

### Main Goals

-   Manage academic categories, sub-categories, courses, and batches.
-   Manage students, teachers, and enrolments.
-   Track class schedules and student attendance.
-   Manage course fees, invoices, and payments.
-   Control access using roles and granular permissions.
-   Upload and manage images and documents through Spatie Media Library.
-   Provide role-specific dashboards and useful reports.
-   Build a maintainable Laravel + Vue application using clear
    separation of concerns.

------------------------------------------------------------------------

## 2. Target Users and Roles

The initial system will support these roles:

  -----------------------------------------------------------------------
  Role                                Main Responsibility
  ----------------------------------- -----------------------------------
  Super Admin                         Full system control, configuration,
                                      user and permission management

  Admin                               Daily academic and administrative
                                      management

  Teacher                             Assigned batches, class sessions,
                                      attendance, learning materials, and
                                      results

  Accountant                          Fee invoices, payment records, and
                                      financial reports

  Student                             Own profile, enrolled batches,
                                      schedule, attendance, payments,
                                      notices, and results
  -----------------------------------------------------------------------

A user account will be stored in Laravel's `users` table. Student and
teacher information will be stored in their respective profile tables
and linked to the user account where login access is required.

**Important:** Roles and permissions will be managed through Spatie
Laravel Permission. The application will not use hard-coded role checks
as its main authorization system.

------------------------------------------------------------------------

## 3. Technology Stack

  -----------------------------------------------------------------------
  Layer                   Technology              Responsibility
  ----------------------- ----------------------- -----------------------
  Backend                 Laravel 12              Business logic, API,
                                                  validation,
                                                  authentication

  Language                PHP                     Backend development

  Database                MySQL                   Persistent application
                                                  data

  Frontend                Vue 3                   Interactive user
                                                  interface

  Vue API                 Composition API +       Component logic and
                          `<script setup>`        reactivity

  Frontend language       TypeScript (preferred)  Type-safe frontend
                                                  development

  Build tool              Vite                    Frontend bundling and
                                                  development server

  Routing                 Vue Router              SPA page navigation

  State management        Pinia                   Shared frontend state

  HTTP client             Axios                   Laravel API
                                                  communication

  Admin template          AdminLTE                Admin dashboard layout
                                                  and UI foundation

  Authorization           Spatie Laravel          Roles and permissions
                          Permission              

  Media                   Spatie Laravel Media    Images, documents,
                          Library                 collections,
                                                  conversions

  Authentication          Laravel Sanctum         SPA authentication

  Testing                 PHPUnit/Pest + Vue      Backend and frontend
                          testing tools           verification

  Version control         Git                     Source control
  -----------------------------------------------------------------------

### Version and Package Compatibility Rule

Laravel 12 and Vue 3 are the project baseline. Before installing any
dependency, inspect the actual `composer.json`, `package.json`, PHP
version, Node.js version, and package compatibility.

The exact AdminLTE major version and its Bootstrap/JavaScript
dependencies must be selected after checking compatibility. Do not mix
assets from different AdminLTE major versions.

Spatie package versions must also be selected according to the installed
Laravel and PHP versions. No installation command should be treated as
final until this check is complete.

------------------------------------------------------------------------

## 4. Application Architecture

### Selected Architecture

**Laravel REST API + Vue 3 SPA inside one Laravel repository.**

This keeps the first project manageable while still teaching a
professional separation between frontend and backend.

``` text
Browser
   |
   v
Vue 3 SPA
   |
   | Axios / HTTP
   v
Laravel REST API
   |
   +---- Authentication (Sanctum)
   |
   +---- Authorization (Spatie Permission)
   |
   +---- Controllers / Form Requests / API Resources
   |
   +---- Models / Business Logic
   |
   v
MySQL Database

Laravel also serves the initial SPA entry page through Vite.
AdminLTE provides the dashboard layout and visual foundation.
```

### SPA Interaction Contract — No Full Page Reload

The application must behave as a Vue-powered Single Page Application (SPA) for all normal user interactions after the initial application load.

**Mandatory rule:** CRUD operations, form submissions, search, filtering, pagination, uploads, and other interactive workflows must communicate with Laravel through Axios/API requests. These actions must not trigger a full browser document reload.

This applies to both:
- **Admin Panel:** category, sub-category, course, batch, student, teacher, enrolment, attendance, finance, announcement, exam, settings, and user-management screens.
- **User-facing Frontend:** student and teacher forms, profile updates, enrolment-related forms, and other Vue-managed screens.

Expected behavior:
- Vue Router handles internal page navigation without a full document reload.
- Create, view, update, and delete actions use Axios requests and update the relevant Vue state after the API response.
- Form submission is handled asynchronously; native browser form submission must be prevented.
- Search, filters, sorting, and pagination update the displayed data through API requests without reloading the page.
- File and image uploads use asynchronous requests and show upload progress or loading feedback where appropriate.
- Validation errors returned by Laravel are displayed beside the relevant fields.
- Loading, success, error, empty, and confirmation states are handled in the Vue UI.
- Buttons that trigger requests must prevent accidental duplicate submissions while a request is in progress.

A full document load is expected only for the initial application load, an explicit browser refresh, or navigation outside the SPA. Do not use traditional form submission or normal full-page links for internal Vue-managed workflows.

### Responsibility Separation

**Laravel Backend** - Authentication and authorization - Database access
and relationships - Request validation - Business rules - API response
formatting - File and media handling - Pagination, search, and filtering

**Vue Frontend** - Pages and reusable components - Forms and client-side
interaction - Routing and navigation - Shared UI state - API requests
and response handling - Loading, empty, success, and error states

**No-reload interaction rule** - Vue Router handles internal navigation and Axios handles all CRUD/form/API operations. AdminLTE styling and plugins must not reintroduce traditional page submissions or full-page navigation for Vue-managed features.

**AdminLTE** - Admin dashboard shell - Sidebar, navbar, content layout -
Dashboard cards and layout patterns - Compatible UI components and
styling

AdminLTE should be treated as the visual foundation, not as a
replacement for Vue's component system. Avoid using jQuery to directly
manipulate DOM elements managed by Vue. Where an AdminLTE plugin
requires JavaScript initialization, integrate it carefully and clean it
up when the relevant Vue component is unmounted.

### Authentication Approach

For the initial same-origin SPA, use Laravel Sanctum's cookie-based SPA
authentication approach where compatible with the deployment setup.

The implementation must correctly handle: - CSRF cookie initialization -
Stateful domains - Session authentication - Credentialed Axios
requests - Logout and session expiry - Backend authorization on every
protected API action

Frontend route guards improve navigation experience, but **they are not
a security boundary**. Laravel middleware, policies, and permission
checks must enforce access on the backend.

------------------------------------------------------------------------

## 5. Main Application Modules

### 5.1 Authentication and User Management

-   Login and logout
-   Authenticated user profile
-   User listing and search
-   Create, update, deactivate, and view users
-   Assign roles and permissions
-   Student and teacher profile management
-   Password change and reset workflow

### 5.2 Academic Category Management

**Category** - Create, view, update, and delete categories - Category
name, slug, description, status, and optional image - Search, filtering,
and pagination

**Sub-category** - Belongs to a category - Create, update, view, and
delete sub-categories - Sub-category name, slug, description, status,
and optional image - Filter sub-categories by category

**Course** - Belongs to a sub-category - Course title, code,
description, duration, fee, status, and cover image - Course listing,
filtering, and details

The category hierarchy will be:

``` text
Category
   |
   +---- Sub-category
             |
             +---- Course
                       |
                       +---- Batch
```

### 5.3 Batch Management

-   Create and manage batches
-   Assign a course to a batch
-   Assign a teacher to a batch
-   Batch name/code
-   Start and end dates
-   Class days and time
-   Capacity and available seats
-   Batch status: upcoming, running, completed, or cancelled
-   View enrolled students

### 5.4 Student Management

-   Student profile and contact details
-   Student photo and permitted documents
-   Student status
-   Enrolment history
-   Assigned batches
-   Attendance summary
-   Fee and payment history

### 5.5 Teacher Management

-   Teacher profile
-   Contact and professional information
-   Profile image and relevant documents
-   Assigned courses and batches
-   Teaching schedule
-   Attendance and result-related access for assigned batches

### 5.6 Enrolment Management

-   Enrol a student into a batch
-   Prevent duplicate active enrolment in the same batch
-   Enrolment date and status
-   Enrolment history
-   Batch capacity check
-   Enrolment confirmation

### 5.7 Class Schedule and Attendance

-   Create class sessions for a batch
-   View daily and weekly schedules
-   Mark attendance for enrolled students
-   Attendance status: present, absent, late, or excused
-   Attendance history and summary
-   Teacher access limited to assigned batches

### 5.8 Fees and Payment Management

-   Configure course or batch fee information
-   Generate fee invoices for enrolments
-   Record partial and full payments
-   Payment methods: cash, bank, mobile financial service, or other
    configured methods
-   Due amount and payment status
-   Payment history and receipts
-   Financial summary and reports

Payment records must be created and updated through validated backend
operations. Multi-step financial operations should use database
transactions.

### 5.9 Announcements and Learning Materials

-   Create and publish announcements
-   Set announcement audience
-   Attach images or documents
-   Upload class notes and learning materials
-   Restrict materials to relevant batches or enrolled students

### 5.10 Exams and Results

This is an advanced module and may be introduced after the main academic
workflow is stable.

-   Create exams for a batch
-   Set exam date and total marks
-   Enter student marks
-   Calculate percentage and grade
-   Publish results
-   Allow students to view their own results

### 5.11 Dashboard and Reports

Dashboard content will depend on the authenticated user's role.

**Admin Dashboard** - Total students - Total teachers - Active courses
and batches - Current enrolments - Attendance overview - Fee collection
and outstanding dues - Recent activities

**Teacher Dashboard** - Assigned batches - Upcoming classes - Attendance
tasks - Recent announcements

**Accountant Dashboard** - Today's collections - Outstanding invoices -
Recent payments - Financial summaries

**Student Dashboard** - Enrolled courses and batches - Class schedule -
Attendance summary - Fee status - Notices and results

------------------------------------------------------------------------

## 6. Role and Permission Design

Use Spatie Laravel Permission to create granular permissions and assign
them to roles.

### Initial Role Matrix

  -----------------------------------------------------------------------------------------
  Module              Super Admin Admin        Teacher         Accountant   Student
  ------------------- ----------- ------------ --------------- ------------ ---------------
  Dashboard           Full        Full         Own data        Financial    Own data

  Users               Full        Manage       No              No           Own profile

  Categories          Full        Manage       View            View         View

  Sub-categories      Full        Manage       View            View         View

  Courses             Full        Manage       Assigned/view   View         Enrolled/view

  Batches             Full        Manage       Assigned        View         Enrolled/view

  Students            Full        Manage       Assigned        View         Own profile
                                               students                     

  Teachers            Full        Manage       Own profile     No           No

  Enrolments          Full        Manage       Assigned/view   View         Own enrolments

  Attendance          Full        Manage       Mark assigned   View         Own attendance

  Fees                Full        Manage       No              Manage       Own fee status

  Payments            Full        Manage       No              Manage       Own payment
                                                                            history

  Exams/Results       Full        Manage       Assigned        No           Own results
                                               batches                      

  Announcements       Full        Manage       Assigned        View         View
                                               audience                     

  Reports             Full        Manage       Assigned data   Financial    Own data
                                                               reports      

  Roles/Permissions   Full        Limited by   No              No           No
                                  policy                                    

  System Settings     Full        Authorized   No              No           No
                                  settings                                  
  -----------------------------------------------------------------------------------------

The exact permission set will be finalized before implementing the
authorization layer.

### Permission Naming Convention

Examples:

``` text
dashboard.view

users.view
users.create
users.update
users.delete

categories.view
categories.create
categories.update
categories.delete

subcategories.view
subcategories.create
subcategories.update
subcategories.delete

courses.view
courses.create
courses.update
courses.delete

batches.view
batches.create
batches.update
batches.delete

students.view
students.create
students.update
students.delete

attendance.view
attendance.mark
attendance.update

fees.view
fees.create
fees.update

payments.view
payments.create
payments.verify

reports.view
reports.export

roles.view
roles.create
roles.update
roles.delete

permissions.assign
settings.manage
```

Use middleware and Laravel Policies for backend authorization. Vue may
hide or show UI elements according to permissions, but backend checks
remain mandatory.

------------------------------------------------------------------------

## 7. Database Overview

The following is the initial logical data model. Exact columns, indexes,
constraints, and migrations will be designed during implementation.

  Table                      Purpose
  -------------------------- ---------------------------------------------
  `users`                    Authentication and common user information
  `student_profiles`         Student-specific profile data
  `teacher_profiles`         Teacher-specific profile data
  `categories`               Main academic categories
  `sub_categories`           Sub-categories belonging to categories
  `courses`                  Courses belonging to sub-categories
  `batches`                  Course batches and schedules
  `enrollments`              Student-to-batch enrolments
  `class_sessions`           Individual scheduled classes
  `attendance_records`       Attendance per enrolment and class session
  `fee_invoices`             Fee invoices for enrolments
  `payments`                 Payments against invoices
  `exams`                    Batch examinations
  `exam_results`             Student results for examinations
  `announcements`            Published notices and announcements
  `learning_materials`       Learning material metadata and access scope
  `media`                    Spatie Media Library records
  Spatie permission tables   Roles, permissions, and model assignments

Spatie Laravel Permission will create and manage its own
role/permission-related tables, including its pivot tables. Spatie Media
Library will manage its media table and model relationships.

### Core Relationships

``` text
Category 1 ---- N SubCategory
SubCategory 1 - N Course
Course 1 ------ N Batch

Student 1 ----- N Enrolment
Batch 1 ------- N Enrolment

Batch 1 ------- N ClassSession
ClassSession 1 - N AttendanceRecord
Enrolment 1 --- N AttendanceRecord

Enrolment 1 --- N FeeInvoice
FeeInvoice 1 -- N Payment

Batch 1 ------- N Exam
Exam 1 -------- N ExamResult
Enrolment 1 --- N ExamResult
```

### Important Database Rules

-   Use foreign keys for relational integrity.
-   Add appropriate indexes for frequently searched fields.
-   Prevent duplicate enrolment for the same student and batch where the
    business rule requires it.
-   Prevent duplicate attendance records for the same enrolment and
    class session.
-   Use transactions for enrolment and payment workflows where multiple
    records must remain consistent.
-   Use soft deletes only for records where recovery is appropriate.
-   Avoid deleting historical payment and attendance records casually.

------------------------------------------------------------------------

## 8. Spatie Laravel Permission

This package will handle role-based and permission-based access control.

### Planned Features

-   Create and edit roles
-   Create and manage permissions
-   Assign multiple permissions to a role
-   Assign roles to users
-   Assign direct permissions to users when genuinely required
-   Check permissions in Laravel middleware and Policies
-   Display authorized navigation and actions in Vue
-   Protect API endpoints

### Example Roles

``` text
Super Admin
Admin
Teacher
Accountant
Student
```

### Security Principle

Hiding a button in Vue does not secure an API endpoint. Every protected
backend action must independently verify the authenticated user's
permission and record ownership or assigned scope.

------------------------------------------------------------------------

## 9. Spatie Laravel Media Library

Media Library will centralize file and image management.

### Planned Media Collections

  Collection                   Used For
  ---------------------------- -------------------------------------------
  `avatar`                     User, student, and teacher profile photos
  `category-image`             Category image
  `subcategory-image`          Sub-category image
  `course-cover`               Course cover image
  `announcement-attachments`   Notice attachments
  `learning-materials`         Notes, PDFs, and class resources
  `student-documents`          Authorized student documents

### Planned Capabilities

-   Upload and associate media with Eloquent models
-   Replace or remove media
-   Validate file type and size
-   Generate image conversions/thumbnails where useful
-   Organize files into named collections
-   Use queued conversions when appropriate
-   Store private documents on a private disk
-   Serve private documents through authorized download endpoints

Public course images and private student documents must not share the
same access policy. Private documents must not be exposed through
unrestricted public URLs.

------------------------------------------------------------------------

## 10. Frontend Structure

Vue 3 will use Single File Components and Composition API.

Proposed structure:

``` text
resources/
  js/
    app.ts
    bootstrap.ts

    api/
      http.ts

    router/
      index.ts

    stores/
      auth.ts
      notification.ts

    layouts/
      AdminLayout.vue
      AuthLayout.vue

    components/
      common/
      forms/
      tables/
      dashboard/

    views/
      auth/
      dashboard/
      categories/
      sub-categories/
      courses/
      batches/
      students/
      teachers/
      enrollments/
      attendance/
      finance/
      announcements/
      exams/
      settings/

    composables/
      useAuth.ts
      usePagination.ts
      usePermissions.ts

    types/
      api.ts
      user.ts

resources/
  views/
    app.blade.php
```

This is a proposed structure, not a requirement to create every folder
immediately. Add folders as the corresponding feature is implemented.

### Frontend Responsibilities

-   Vue Router handles page navigation.
-   Pinia handles shared application state.
-   Axios handles HTTP communication.
-   Composables hold reusable UI and application logic.
-   Components handle reusable UI sections.
-   AdminLTE provides the dashboard visual shell.

------------------------------------------------------------------------

## 11. Backend Structure

Proposed Laravel structure:

``` text
app/
  Models/
    User.php
    StudentProfile.php
    TeacherProfile.php
    Category.php
    SubCategory.php
    Course.php
    Batch.php
    Enrollment.php
    ClassSession.php
    AttendanceRecord.php
    FeeInvoice.php
    Payment.php

  Http/
    Controllers/
      Api/
        V1/
          Auth/
          Dashboard/
          CategoryController.php
          SubCategoryController.php
          CourseController.php
          BatchController.php
          StudentController.php
          TeacherController.php
          EnrollmentController.php
          AttendanceController.php
          FeeInvoiceController.php
          PaymentController.php

    Requests/
      Api/
        V1/

    Resources/
      Api/
        V1/

  Policies/
  Services/
```

Use Laravel's standard MVC conventions. Form Requests should handle
request validation, API Resources should format responses, and
Policies/middleware should enforce access. A Service class should be
introduced when a workflow has meaningful business logic or multiple
coordinated operations---not automatically for every CRUD action.

------------------------------------------------------------------------

## 12. API Design Convention

Use versioned API endpoints under:

``` text
/api/v1
```

Example endpoint groups:

``` text
/api/v1/auth/me
/api/v1/auth/logout

/api/v1/dashboard

/api/v1/categories
/api/v1/sub-categories
/api/v1/courses
/api/v1/batches

/api/v1/students
/api/v1/teachers
/api/v1/enrollments

/api/v1/class-sessions
/api/v1/attendance

/api/v1/fee-invoices
/api/v1/payments

/api/v1/announcements
/api/v1/exams
/api/v1/exam-results
```

Use: - RESTful resource routes - Laravel Form Requests for validation -
API Resources for response formatting - Consistent HTTP status codes -
Pagination metadata - Search and filter query parameters - Clear
validation and authorization errors

The exact endpoint list may change slightly as workflows are finalized.

### Trash, Restore, and Permanent Delete API Convention

For resources that support soft deletion, provide separate endpoints to view trashed records, restore a record, and permanently delete a record.

Example pattern:

```text
GET    /api/v1/categories/trash
PATCH  /api/v1/categories/{category}/restore
DELETE /api/v1/categories/{category}/force

GET    /api/v1/sub-categories/trash
PATCH  /api/v1/sub-categories/{subCategory}/restore
DELETE /api/v1/sub-categories/{subCategory}/force
```

Apply the same route pattern to other eligible resources. The backend must use the appropriate Eloquent soft-delete queries (`onlyTrashed`, `restore`, and `forceDelete`) and enforce permissions on every endpoint. Restore routes must explicitly resolve soft-deleted models (for example, with `withTrashed()` or an `onlyTrashed()` query). Force-delete routes must target trashed records only. Do not expose permanent-delete endpoints for protected historical records.


------------------------------------------------------------------------

## 13. Advanced Features

The project should remain small enough to finish, but include selected
advanced capabilities.

### SPA CRUD and Form Interaction Requirements

All Admin Panel and Vue-managed frontend CRUD workflows must be asynchronous:
- Create and update forms submit through Axios.
- Delete actions call the Laravel API and update the current list after success.
- Details, edit forms, modal forms, and inline actions must not cause a full page reload.
- Search, filters, sorting, and pagination must fetch/update data asynchronously.
- Uploads must use API requests and show suitable loading/preview/error feedback.
- Laravel validation responses must be mapped to Vue form errors.
- Success and failure feedback must be clear and consistent.

### Included in the Planned Scope

-   Role-specific dashboards
-   Granular permission management
-   Category/sub-category hierarchy
-   Reusable Vue components
-   Search, filtering, and server-side pagination
-   Media collections and image conversions
-   Private document access
-   Attendance summaries
-   Fee invoices and partial payments
-   Dashboard statistics
-   Soft delete, restore, and force delete for eligible admin-managed records
-   Dedicated Trash view with separate Active and Trashed records
-   No full page reload for Admin Panel or Vue-managed frontend CRUD/forms
-   CSV export for selected reports
-   Responsive AdminLTE dashboard

### Soft Delete, Restore, and Force Delete Workflow

The Admin Panel must provide a complete record-recovery workflow for eligible modules.

**Normal records**
- Active records are shown in the regular listing.
- Soft-deleted records are excluded from normal listings by default.
- Authorized admins can move eligible records to Trash using a soft delete.
- Soft deletion sets the model's `deleted_at` timestamp; it does not immediately remove the database row.

**Trash management**
- Each eligible module provides a Trash view or a shared Trash interface with module filtering.
- Admins can view trashed records separately from active records.
- Authorized admins can restore a trashed record.
- Restoring a record clears `deleted_at` and returns it to the normal listing.
- Authorized admins can permanently delete a trashed record after an explicit confirmation.

**Suggested module coverage**
- Soft delete + restore + force delete: categories, sub-categories, courses, batches, students, teachers, users, and other eligible master/content records.
- Historical/financial records such as payments, invoices, attendance records, and published exam results must be protected from casual permanent deletion. Use cancellation, voiding, correction, or archival workflows where appropriate.
- If a record has dependent or historically important records, the backend must block force deletion unless a specifically approved business rule allows it.

**Permissions**
Use granular Spatie permissions, for example:

```text
categories.delete
categories.restore
categories.force_delete

subcategories.delete
subcategories.restore
subcategories.force_delete

courses.delete
courses.restore
courses.force_delete

students.delete
students.restore
students.force_delete
```

Follow the same naming pattern for other eligible modules. Only authorized roles should see or execute the relevant actions. Laravel must enforce the permission on the API endpoint; hiding a button in Vue is not sufficient.

**Frontend behavior**
- Trash, Restore, and Force Delete actions use Axios and must not reload the page.
- Restore and Force Delete require a clear confirmation step.
- After a successful action, update the active/trash list in Vue without a full page reload.
- Show success, validation, permission, and server-error feedback.
- Prevent duplicate requests while an action is processing.

**Backend implementation**
- Add Laravel's `SoftDeletes` trait and `deleted_at` column only to models approved for soft deletion.
- Use normal Eloquent queries for active records, `onlyTrashed()` for Trash listings, `restore()` for restoration, and `forceDelete()` for permanent deletion.
- Enforce authorization through middleware and Policies.
- Check dependent relationships and data-retention rules before permanent deletion.
- Never cascade-force-delete financial or academic history simply because a parent record is being deleted.

### Optional Extensions After Core Completion

-   In-app notifications
-   Bulk student import
-   Bulk attendance operations
-   Advanced exam analytics
-   Multi-branch coaching-centre support
-   Activity/audit history
-   Automated scheduled reminders

Optional extensions should not delay the core application.

------------------------------------------------------------------------

## 14. Security and Quality Requirements

-   Use Laravel Sanctum for the selected SPA authentication approach.
-   Enforce permissions on the backend using middleware and Policies.
-   Validate all incoming requests on the backend.
-   Protect private media and student documents.
-   Use CSRF protection and correct stateful-domain configuration.
-   Avoid exposing secrets in frontend environment variables.
-   Use database transactions for multi-step financial and enrolment
    workflows.
-   Prevent unauthorized access to another student's records.
-   Use eager loading where needed to avoid N+1 queries.
-   Use pagination for large lists.
-   Avoid unnecessary packages and duplicated business logic.
-   Keep Vue components focused and reusable.
-   Do not manipulate Vue-managed DOM directly with jQuery.
-   Test critical workflows and authorization rules.

------------------------------------------------------------------------

### SPA and Data Recovery Safeguards

- All Vue-managed internal navigation and form workflows must avoid full document reloads.
- Backend API endpoints must validate every create, update, delete, restore, and force-delete request.
- Restore and force-delete actions must be permission-protected and confirmed in the UI.
- Force deletion must be blocked when dependent records or retention rules make permanent deletion unsafe.
- Financial and academic history must not be permanently deleted through generic CRUD actions.
- The frontend must never be treated as the authorization boundary; Laravel must verify the user's permission and record scope.

## 15. Development Roadmap

Implementation will be incremental. Do not build all modules at once.

| Phase | Work | Expected Result |
|---|---|---|
| 0 | Inspect environment and verify versions | Confirmed compatible stack |
| 1 | Laravel + Vue + Vite + AdminLTE foundation | Application shell and layouts |
| 2 | Authentication and authorization | Sanctum, roles, and permissions |
| 3 | Category, sub-category, course, and batch | Academic setup with no-reload CRUD, Trash, Restore, and Force Delete |
| 4 | Student, teacher, and enrolment | People and enrolment management |
| 5 | Attendance and schedule | Academic operations |
| 6 | Fees, invoices, and payments | Finance module |
| 7 | Media Library and announcements | File and content management |
| 8 | Dashboard and reports | Role-specific dashboard |
| 9 | Exams and results | Advanced academic module |
| 10 | Testing, optimization, and documentation | Stable project handover |

Each phase will include database design, backend implementation,
frontend implementation, integration, and verification before moving to
the next phase.

------------------------------------------------------------------------

## 16. Initial Acceptance Criteria

The first usable release will be considered complete when:

-   Admin can log in and access the authorized dashboard.
-   Admin can manage categories, sub-categories, courses, and batches.
-   Admin can create student and teacher profiles.
-   Students can be enrolled into batches.
-   Teachers can access only their assigned academic data.
-   Attendance can be recorded and viewed.
-   Accountant can create invoices and record payments.
-   Students can view their own enrolments, attendance, and payment
    information.
-   Media uploads and private document access follow the intended access
    rules.
-   Unauthorized API requests are rejected by Laravel.
-   Search, filtering, and pagination work on the main listing screens.
-   Admin Panel CRUD actions and Vue-managed frontend form submissions do not cause a full browser page reload.
-   Internal navigation uses Vue Router and data operations use Axios/API requests.
-   Eligible modules provide Active and Trash listings.
-   Authorized admins can soft-delete, restore, and permanently delete eligible records.
-   Restore and force-delete actions require confirmation and are enforced by backend permissions.
-   Force deletion is blocked for protected financial or academic history and for records with disallowed dependencies.
-   Loading, success, validation-error, and server-error feedback are shown without a full page reload.
-   The main workflows have been tested.

------------------------------------------------------------------------

## 17. Scope Boundaries

To keep the first project achievable:

**Included:** Single coaching centre, core academic management, student
and teacher management, enrolment, attendance, fees, role-based access,
media management, and dashboard.

**Not included in the initial release:** Full accounting software,
payroll, live video classes, online payment gateway integration, mobile
applications, multi-tenant SaaS, and multi-branch operations.

These can be considered later if the core system is stable.

------------------------------------------------------------------------

## 18. Implementation Rules

1.  Inspect the existing project before changing files.
2.  Verify Laravel, PHP, Node, Vue, Vite, AdminLTE, and Spatie package
    compatibility.
3.  Explain the feature workflow before implementation.
4.  Design migrations and relationships before writing dependent CRUD
    code.
5.  Implement one module at a time.
6.  Preserve existing architecture and working features.
7.  Use clean, concise, version-compatible code.
8.  Do not install unnecessary packages.
9.  Do not claim a feature is complete until it has been verified.
10. Keep the project runnable after each implementation milestone.
11. All Admin Panel and Vue-managed frontend CRUD/forms must use asynchronous Axios requests; do not trigger full page reloads for normal user actions.
12. Implement Trash, Restore, and Force Delete for approved modules, with Spatie permission checks and backend relationship safeguards.
13. Keep financial and academic history protected from unsafe permanent deletion.
14. Update Vue state after successful API actions and show clear loading, success, and error feedback.

------------------------------------------------------------------------

## 19. Immediate Next Step

Before implementation, inspect the development environment and confirm:

-   PHP and Composer versions
-   Laravel version
-   Node.js and npm versions
-   MySQL availability
-   Existing Laravel project structure, if a project already exists
-   AdminLTE major version compatibility
-   Compatible versions of Spatie Permission and Spatie Media Library

Then create the Laravel + Vue foundation and integrate the AdminLTE
layout.

**Project principle:** Build a small, complete, secure, and maintainable
coaching management system first. Add advanced features only when the
core workflows are stable.
