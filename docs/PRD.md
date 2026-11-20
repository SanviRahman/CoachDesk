# CoachDesk --- Product Requirements Document (PRD)

> **Document Type:** Product Requirements Document\
> **Product Name:** CoachDesk\
> **Product:** Coaching Management System\
> **Backend:** Laravel 12 + PHP\
> **Frontend:** Vue 3 + Composition API + `<script setup>` + TypeScript
> (preferred)\
> **Database:** MySQL\
> **Build Tool:** Vite\
> **UI Foundation:** AdminLTE\
> **API Client:** Axios\
> **Routing:** Vue Router\
> **State Management:** Pinia\
> **Authentication:** Laravel Sanctum\
> **Authorization:** Spatie Laravel Permission\
> **Media Management:** Spatie Laravel Media Library\
> **Source:** User-provided `project-overview.md`\
> **Status:** Initial PRD --- requirements baseline; implementation has
> not started

------------------------------------------------------------------------

## 1. Executive Summary

CoachDesk is a web-based Coaching Management System for managing the
academic and administrative operations of a single coaching centre.

The product will provide a role-based application for Super Admins,
Admins, Teachers, Accountants, and Students. It will support academic
category and sub-category management, courses, batches, student and
teacher profiles, enrolments, class schedules, attendance, fees,
invoices, payments, announcements, learning materials, exams, results,
dashboards, and reports.

The system will use **Laravel 12 as the backend** and **Vue 3 as the
frontend**. Laravel will expose versioned REST APIs and remain
responsible for authentication, authorization, validation, business
rules, database operations, and media access. Vue will provide the
application UI as a Single Page Application (SPA). AdminLTE will provide
the dashboard visual foundation.

A mandatory product requirement is that normal user interactions must
not trigger a full browser page reload. Admin and user-facing Vue
screens will use Vue Router for internal navigation and Axios for CRUD,
form submissions, search, filtering, pagination, and uploads.

The Admin Panel will also provide a controlled record-recovery workflow
for eligible records: **Soft Delete → Trash → Restore or Force Delete**.
Permanent deletion must be permission-protected and blocked for
protected financial or academic history.

------------------------------------------------------------------------

## 2. Product Vision

Provide a small, modular, maintainable coaching-centre system that
centralizes day-to-day academic and administrative work in one
application.

The first release is for **one coaching centre**. The architecture
should remain modular enough that multi-branch support can be considered
later, but multi-branch operation is not part of the initial release.

### 2.1 Product Goals

-   Centralize academic categories, sub-categories, courses, and
    batches.
-   Manage student and teacher information.
-   Manage student enrolments into batches.
-   Organize class schedules and attendance.
-   Manage fee invoices, partial payments, and payment history.
-   Provide role-based access and granular permissions.
-   Manage images and documents through Spatie Media Library.
-   Provide role-specific dashboards and useful reports.
-   Provide a responsive, interactive Vue SPA.
-   Avoid full page reloads during normal Admin Panel and user-facing
    workflows.
-   Provide safe Trash, Restore, and Force Delete workflows for eligible
    records.
-   Keep the application secure, maintainable, and practical to complete
    incrementally.

### 2.2 Success Outcomes

The initial usable release should allow: - Administrators to configure
academic structures and manage people. - Students to be enrolled into
batches. - Teachers to manage academic work only for assigned batches. -
Attendance to be recorded and reviewed. - Accountants to manage invoices
and payment records. - Students to access only their own permitted
academic and financial information. - Authorized administrators to
recover eligible deleted records. - Users to complete normal workflows
without full browser document reloads.

------------------------------------------------------------------------

## 3. Scope

### 3.1 Included in the Initial Product Scope

-   Single coaching-centre operation.
-   Authentication and user profile management.
-   Super Admin, Admin, Teacher, Accountant, and Student roles.
-   Category → Sub-category → Course → Batch hierarchy.
-   Student and teacher profile management.
-   Student enrolment management.
-   Class schedules and attendance.
-   Fee invoices and partial/full payment records.
-   Announcements and learning materials.
-   Role-specific dashboards and reports.
-   Spatie Laravel Permission.
-   Spatie Laravel Media Library.
-   AdminLTE dashboard layout.
-   Vue 3 SPA with Vue Router, Pinia, Axios, and Vite.
-   Asynchronous CRUD and form workflows.
-   Search, filtering, sorting, and server-side pagination.
-   Soft Delete, Trash, Restore, and Force Delete for approved records.
-   CSV export for selected reports.
-   Responsive dashboard UI.

### 3.2 Advanced Planned Module

The Exams and Results module is planned, but should be introduced after
the main academic workflow is stable.

### 3.3 Out of Scope for the Initial Release

-   Full accounting software.
-   Payroll.
-   Live video classes.
-   Online payment gateway integration.
-   Native mobile applications.
-   Multi-tenant SaaS.
-   Multi-branch operations.

### 3.4 Optional Extensions After Core Completion

These are not required to complete the core release: - In-app
notifications. - Bulk student import. - Bulk attendance operations. -
Advanced exam analytics. - Multi-branch support. - Activity/audit
history. - Automated scheduled reminders.

------------------------------------------------------------------------

## 4. Target Users and Roles

The system shall support the following roles.

  -----------------------------------------------------------------------
  Role                                Product Responsibility
  ----------------------------------- -----------------------------------
  Super Admin                         Full system control, configuration,
                                      user management, role and
                                      permission management

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

### 4.1 Account and Profile Model

-   Authentication accounts shall be stored in Laravel's `users` table.
-   Student-specific information shall be stored in a student profile
    table.
-   Teacher-specific information shall be stored in a teacher profile
    table.
-   Student and teacher profiles shall be linked to a user account when
    login access is required.
-   Roles and permissions shall be managed through Spatie Laravel
    Permission.
-   Hard-coded role checks shall not be the primary authorization
    mechanism.

### 4.2 Role Access Expectations

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

The exact permission set and any narrower record-level access rules must
be finalized before implementation.

------------------------------------------------------------------------

## 5. Technology and Architecture Requirements

### 5.1 Required Technology Stack

  -----------------------------------------------------------------------
  Layer                   Technology              Requirement
  ----------------------- ----------------------- -----------------------
  Backend                 Laravel 12              API, business logic,
                                                  validation,
                                                  authentication

  Language                PHP                     Backend implementation

  Database                MySQL                   Persistent data

  Frontend                Vue 3                   SPA user interface

  Vue API                 Composition API +       Component logic and
                          `<script setup>`        reactivity

  Frontend language       TypeScript preferred    Type-safe frontend
                                                  development

  Build tool              Vite                    Asset bundling and
                                                  development server

  Routing                 Vue Router              SPA navigation

  State                   Pinia                   Shared frontend state

  HTTP                    Axios                   API communication

  Admin UI                AdminLTE                Dashboard visual
                                                  foundation

  Authentication          Laravel Sanctum         Cookie-based SPA
                                                  authentication

  Authorization           Spatie Laravel          Roles and permissions
                          Permission              

  Media                   Spatie Laravel Media    File and image
                          Library                 management

  Testing                 PHPUnit/Pest + Vue      Backend and frontend
                          testing tools           verification

  Version control         Git                     Source control
  -----------------------------------------------------------------------

### 5.2 Selected Architecture

**Laravel REST API + Vue 3 SPA inside one Laravel repository.**

``` text
Browser
   |
   v
Minimal Blade SPA Entry (app.blade.php)
   |
   v
Vue 3 SPA
   |
   | Axios / HTTP
   v
Laravel REST API
   |
   +---- Sanctum Authentication
   |
   +---- Spatie Permission Authorization
   |
   +---- Controllers / Form Requests / API Resources
   |
   +---- Models / Business Logic
   |
   v
MySQL Database
```

### 5.3 Blade Usage

Blade shall be used only as the minimal SPA entry point in the initial
architecture.

Expected entry file:

``` text
resources/views/app.blade.php
```

The Blade entry shall: - Provide the root HTML document. - Load the Vite
application assets. - Provide the root element where Vue mounts. -
Include required meta and CSRF-related configuration where appropriate.

Admin pages, student/teacher portal pages, CRUD screens, and Vue-managed
forms shall be implemented as Vue components---not as separate
Blade-rendered pages.

### 5.4 Frontend Responsibilities

-   Vue Router shall manage internal page navigation.
-   Pinia shall manage shared frontend state.
-   Axios shall handle HTTP communication with Laravel APIs.
-   Composables shall hold reusable frontend logic.
-   Vue components shall implement pages and reusable UI.
-   AdminLTE shall provide the dashboard shell and visual foundation.
-   jQuery shall not directly manipulate DOM elements managed by Vue.
-   Any AdminLTE JavaScript plugin that requires initialization must be
    integrated carefully and cleaned up when the relevant Vue component
    is unmounted.

### 5.5 Backend Responsibilities

-   Authentication and authorization.
-   Database operations and relationships.
-   Request validation.
-   Business rules.
-   API response formatting.
-   File and media handling.
-   Pagination, search, and filtering.
-   Record deletion, restoration, and permanent deletion safeguards.

### 5.6 Version Compatibility

Before dependency installation, the development environment and package
compatibility must be checked: - PHP and Composer versions. - Laravel
version. - Node.js and npm versions. - Vue and Vite versions. - AdminLTE
major version and its Bootstrap/JavaScript dependencies. - Compatible
Spatie Laravel Permission version. - Compatible Spatie Laravel Media
Library version.

No package version or installation command is final until compatibility
is verified.

------------------------------------------------------------------------

## 6. Functional Requirements

Requirements are grouped by module. Each requirement has a unique
identifier for implementation and testing traceability.

### 6.1 Authentication and User Management

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-AUTH-01                          The system shall allow authorized
                                      users to log in and log out.

  FR-AUTH-02                          The system shall expose the
                                      authenticated user's profile
                                      through a protected API.

  FR-AUTH-03                          Authorized administrators shall be
                                      able to list, search, view, create,
                                      update, and deactivate users.

  FR-AUTH-04                          Authorized administrators shall be
                                      able to assign roles and
                                      permissions to users.

  FR-AUTH-05                          The system shall support student
                                      and teacher profile management.

  FR-AUTH-06                          The system shall support password
                                      change and reset workflows.

  FR-AUTH-07                          Protected API endpoints shall
                                      reject unauthenticated requests.

  FR-AUTH-08                          Logout and session-expiry behavior
                                      shall be handled in the Vue
                                      application.
  -----------------------------------------------------------------------

### 6.2 Academic Category Management

#### Category

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-CAT-01                           Authorized users shall be able to
                                      create categories.

  FR-CAT-02                           Authorized users shall be able to
                                      view and list categories.

  FR-CAT-03                           Authorized users shall be able to
                                      update category name, slug,
                                      description, status, and optional
                                      image.

  FR-CAT-04                           Authorized users shall be able to
                                      soft-delete eligible categories.

  FR-CAT-05                           Authorized users shall be able to
                                      search, filter, and paginate
                                      categories.
  -----------------------------------------------------------------------

#### Sub-category

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-SUB-01                           Each sub-category shall belong to a
                                      category.

  FR-SUB-02                           Authorized users shall be able to
                                      create, view, update, and delete
                                      sub-categories.

  FR-SUB-03                           Sub-category data shall include
                                      name, slug, description, status,
                                      and optional image.

  FR-SUB-04                           Users shall be able to filter
                                      sub-categories by parent category.

  FR-SUB-05                           Eligible sub-categories shall
                                      support Trash, Restore, and Force
                                      Delete according to permissions and
                                      dependency rules.
  -----------------------------------------------------------------------

#### Course

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-COURSE-01                        Each course shall belong to a
                                      sub-category.

  FR-COURSE-02                        Authorized users shall be able to
                                      create, view, update, and delete
                                      courses.

  FR-COURSE-03                        Course information shall include
                                      title, code, description, duration,
                                      fee, status, and cover image.

  FR-COURSE-04                        Users shall be able to search and
                                      filter courses.

  FR-COURSE-05                        Eligible courses shall support
                                      Trash, Restore, and Force Delete
                                      according to permissions and
                                      dependency rules.
  -----------------------------------------------------------------------

The required academic hierarchy is:

``` text
Category
   └── Sub-category
         └── Course
               └── Batch
```

### 6.3 Batch Management

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-BATCH-01                         Authorized users shall be able to
                                      create and manage batches.

  FR-BATCH-02                         Each batch shall be associated with
                                      a course.

  FR-BATCH-03                         A teacher shall be assignable to a
                                      batch.

  FR-BATCH-04                         Batch data shall include name/code,
                                      start date, end date, class days,
                                      class time, capacity, and status.

  FR-BATCH-05                         Batch status shall support
                                      upcoming, running, completed, and
                                      cancelled.

  FR-BATCH-06                         Authorized users shall be able to
                                      view enrolled students in a batch.

  FR-BATCH-07                         Eligible batches shall support
                                      Trash, Restore, and Force Delete
                                      subject to dependencies and
                                      permissions.
  -----------------------------------------------------------------------

### 6.4 Student Management

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-STU-01                           Authorized users shall be able to
                                      create and manage student profiles.

  FR-STU-02                           Student profiles shall store
                                      profile and contact details.

  FR-STU-03                           The system shall support student
                                      photos and permitted documents.

  FR-STU-04                           Authorized users shall be able to
                                      view student status and enrolment
                                      history.

  FR-STU-05                           Authorized users shall be able to
                                      view assigned batches, attendance
                                      summaries, and fee/payment history
                                      according to permissions.

  FR-STU-06                           Students shall be restricted to
                                      their own profile and permitted
                                      records.

  FR-STU-07                           Eligible student records shall
                                      support Trash, Restore, and Force
                                      Delete subject to historical-data
                                      safeguards.
  -----------------------------------------------------------------------

### 6.5 Teacher Management

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-TEACH-01                         Authorized users shall be able to
                                      create and manage teacher profiles.

  FR-TEACH-02                         Teacher profiles shall include
                                      contact and professional
                                      information.

  FR-TEACH-03                         The system shall support teacher
                                      profile images and relevant
                                      documents.

  FR-TEACH-04                         Teachers shall be able to view
                                      their assigned courses, batches,
                                      and teaching schedules.

  FR-TEACH-05                         Teachers shall only access
                                      attendance and result-related data
                                      for assigned batches.

  FR-TEACH-06                         Eligible teacher records shall
                                      support Trash, Restore, and Force
                                      Delete subject to dependencies and
                                      permissions.
  -----------------------------------------------------------------------

### 6.6 Enrolment Management

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-ENR-01                           Authorized users shall be able to
                                      enrol a student into a batch.

  FR-ENR-02                           The system shall prevent duplicate
                                      active enrolment for the same
                                      student and batch.

  FR-ENR-03                           Enrolment data shall include
                                      enrolment date and status.

  FR-ENR-04                           The system shall check batch
                                      capacity before confirming
                                      enrolment.

  FR-ENR-05                           Authorized users shall be able to
                                      view enrolment history.

  FR-ENR-06                           The system shall provide an
                                      enrolment confirmation state.
  -----------------------------------------------------------------------

### 6.7 Class Schedule and Attendance

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-ATT-01                           Authorized users shall be able to
                                      create class sessions for a batch.

  FR-ATT-02                           Users shall be able to view daily
                                      and weekly class schedules.

  FR-ATT-03                           Teachers shall be able to mark
                                      attendance for students in assigned
                                      batches.

  FR-ATT-04                           Attendance status shall support
                                      present, absent, late, and excused.

  FR-ATT-05                           Authorized users shall be able to
                                      view attendance history and
                                      summaries.

  FR-ATT-06                           The system shall prevent duplicate
                                      attendance records for the same
                                      enrolment and class session.

  FR-ATT-07                           Students shall be able to view only
                                      their own attendance records.
  -----------------------------------------------------------------------

### 6.8 Fees, Invoices, and Payments

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-FIN-01                           Authorized users shall be able to
                                      configure course or batch fee
                                      information.

  FR-FIN-02                           The system shall generate fee
                                      invoices for enrolments.

  FR-FIN-03                           Authorized accountants shall be
                                      able to record partial and full
                                      payments.

  FR-FIN-04                           Payment methods shall support cash,
                                      bank, mobile financial service, and
                                      other configured methods.

  FR-FIN-05                           The system shall calculate and
                                      display due amounts and payment
                                      status.

  FR-FIN-06                           Authorized users shall be able to
                                      view payment history and receipts.

  FR-FIN-07                           The system shall provide financial
                                      summaries and reports.

  FR-FIN-08                           Students shall be able to view only
                                      their own fee and payment
                                      information.

  FR-FIN-09                           Multi-step financial operations
                                      shall use database transactions
                                      where consistency requires it.

  FR-FIN-10                           Financial records shall not be
                                      permanently deleted through generic
                                      CRUD or Trash actions unless an
                                      explicitly approved business rule
                                      allows it.
  -----------------------------------------------------------------------

Online payment gateway integration is outside the initial release scope.

### 6.9 Announcements and Learning Materials

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-CONT-01                          Authorized users shall be able to
                                      create and publish announcements.

  FR-CONT-02                          Announcements shall support
                                      audience selection.

  FR-CONT-03                          Announcements shall support image
                                      or document attachments.

  FR-CONT-04                          Authorized users shall be able to
                                      upload class notes and learning
                                      materials.

  FR-CONT-05                          Learning materials shall be
                                      restricted to relevant batches or
                                      enrolled students where applicable.
  -----------------------------------------------------------------------

### 6.10 Exams and Results --- Advanced Planned Module

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-EXAM-01                          Authorized users shall be able to
                                      create exams for a batch.

  FR-EXAM-02                          Exam information shall include exam
                                      date and total marks.

  FR-EXAM-03                          Authorized teachers/users shall be
                                      able to enter student marks for
                                      permitted batches.

  FR-EXAM-04                          The system shall calculate
                                      percentage and grade according to
                                      configured grading rules.

  FR-EXAM-05                          Authorized users shall be able to
                                      publish results.

  FR-EXAM-06                          Students shall be able to view only
                                      their own published results.
  -----------------------------------------------------------------------

The exact grading scale and exam workflow are open decisions to be
finalized before this module is implemented.

### 6.11 Dashboards and Reports

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-DASH-01                          Dashboard content shall depend on
                                      the authenticated user's role and
                                      permissions.

  FR-DASH-02                          Admin dashboard shall show total
                                      students, total teachers, active
                                      courses/batches, current
                                      enrolments, attendance overview,
                                      fee collection, outstanding dues,
                                      and recent activities.

  FR-DASH-03                          Teacher dashboard shall show
                                      assigned batches, upcoming classes,
                                      attendance tasks, and recent
                                      announcements.

  FR-DASH-04                          Accountant dashboard shall show
                                      today's collections, outstanding
                                      invoices, recent payments, and
                                      financial summaries.

  FR-DASH-05                          Student dashboard shall show
                                      enrolled courses/batches, schedule,
                                      attendance summary, fee status,
                                      notices, and results.

  FR-DASH-06                          Selected reports shall support CSV
                                      export.

  FR-DASH-07                          Users shall only access dashboard
                                      metrics and reports permitted for
                                      their role and record scope.
  -----------------------------------------------------------------------

### 6.12 Roles and Permissions

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-RBAC-01                          The system shall use Spatie Laravel
                                      Permission for role and permission
                                      management.

  FR-RBAC-02                          Authorized users shall be able to
                                      create and edit roles.

  FR-RBAC-03                          Authorized users shall be able to
                                      create and manage permissions.

  FR-RBAC-04                          Authorized users shall be able to
                                      assign multiple permissions to a
                                      role.

  FR-RBAC-05                          Authorized users shall be able to
                                      assign roles to users.

  FR-RBAC-06                          Direct user permissions may be
                                      assigned when genuinely required.

  FR-RBAC-07                          Laravel middleware and Policies
                                      shall enforce backend access
                                      control.

  FR-RBAC-08                          Vue may hide or show
                                      navigation/actions based on
                                      permissions, but backend
                                      authorization shall remain
                                      mandatory.
  -----------------------------------------------------------------------

Permission naming examples:

``` text
categories.view
categories.create
categories.update
categories.delete
categories.restore
categories.force_delete

subcategories.view
subcategories.create
subcategories.update
subcategories.delete
subcategories.restore
subcategories.force_delete

courses.view
courses.create
courses.update
courses.delete
courses.restore
courses.force_delete

students.view
students.create
students.update
students.delete
students.restore
students.force_delete

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

The complete permission list and role assignments shall be finalized
before implementation.

### 6.13 Media Management

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-MEDIA-01                         The system shall use Spatie Laravel
                                      Media Library for model-associated
                                      media.

  FR-MEDIA-02                         The system shall support media
                                      upload, replacement, and removal
                                      where authorized.

  FR-MEDIA-03                         The system shall validate allowed
                                      file types and file sizes.

  FR-MEDIA-04                         The system shall organize media
                                      into named collections.

  FR-MEDIA-05                         The system may generate image
                                      conversions/thumbnails where
                                      useful.

  FR-MEDIA-06                         Queued conversions may be used
                                      where appropriate.

  FR-MEDIA-07                         Public course images and private
                                      student documents shall have
                                      different access policies.

  FR-MEDIA-08                         Private documents shall be stored
                                      on a private disk and served
                                      through authorized download
                                      endpoints.
  -----------------------------------------------------------------------

Planned collections:

  Collection                   Purpose
  ---------------------------- -------------------------------------------
  `avatar`                     User, student, and teacher profile photos
  `category-image`             Category image
  `subcategory-image`          Sub-category image
  `course-cover`               Course cover image
  `announcement-attachments`   Announcement attachments
  `learning-materials`         Notes, PDFs, and class resources
  `student-documents`          Authorized student documents

### 6.14 SPA Navigation, CRUD, and Forms --- No Full Page Reload

This is a mandatory product-wide requirement.

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-SPA-01                           The application shall behave as a
                                      Vue SPA after the initial
                                      application load.

  FR-SPA-02                           Vue Router shall handle internal
                                      navigation without full document
                                      reloads.

  FR-SPA-03                           All Vue-managed create, view,
                                      update, and delete actions shall
                                      communicate with Laravel through
                                      Axios/API requests.

  FR-SPA-04                           Admin Panel forms shall submit
                                      asynchronously and shall not use
                                      native full-page form submission.

  FR-SPA-05                           Student/teacher-facing Vue forms
                                      shall submit asynchronously without
                                      full page reloads.

  FR-SPA-06                           Search, filtering, sorting, and
                                      pagination shall update data
                                      asynchronously.

  FR-SPA-07                           File and image uploads shall use
                                      API requests and provide suitable
                                      loading/progress feedback where
                                      appropriate.

  FR-SPA-08                           Laravel validation errors shall be
                                      displayed beside the relevant Vue
                                      form fields.

  FR-SPA-09                           The UI shall provide loading,
                                      success, error, empty, and
                                      confirmation states.

  FR-SPA-10                           Request-triggering controls shall
                                      prevent accidental duplicate
                                      submissions while requests are in
                                      progress.

  FR-SPA-11                           Vue state/list views shall update
                                      after successful API actions
                                      without a full document reload.
  -----------------------------------------------------------------------

A full document load is permitted only for: - Initial application
load. - Explicit browser refresh. - Navigation outside the SPA.

Normal internal Vue-managed workflows must not trigger a full page
reload.

### 6.15 Soft Delete, Trash, Restore, and Force Delete

  -----------------------------------------------------------------------
  ID                                  Requirement
  ----------------------------------- -----------------------------------
  FR-TRASH-01                         Eligible models shall use Laravel
                                      SoftDeletes and a `deleted_at`
                                      column.

  FR-TRASH-02                         Normal listings shall exclude
                                      soft-deleted records by default.

  FR-TRASH-03                         Authorized admins shall be able to
                                      soft-delete eligible records.

  FR-TRASH-04                         The Admin Panel shall provide a
                                      Trash view or shared Trash
                                      interface with module filtering.

  FR-TRASH-05                         Authorized admins shall be able to
                                      view trashed records separately
                                      from active records.

  FR-TRASH-06                         Authorized admins shall be able to
                                      restore trashed records.

  FR-TRASH-07                         Authorized admins shall be able to
                                      permanently delete eligible trashed
                                      records after explicit
                                      confirmation.

  FR-TRASH-08                         Trash, Restore, and Force Delete
                                      actions shall use Axios and shall
                                      not trigger a full page reload.

  FR-TRASH-09                         Restore and Force Delete actions
                                      shall require clear confirmation in
                                      the UI.

  FR-TRASH-10                         Laravel shall enforce separate
                                      permissions for delete, restore,
                                      and force delete.

  FR-TRASH-11                         Force deletion shall be blocked
                                      when dependencies or data-retention
                                      rules make permanent deletion
                                      unsafe.

  FR-TRASH-12                         Financial and academic history
                                      shall be protected from generic
                                      permanent deletion.
  -----------------------------------------------------------------------

#### Eligible Record Coverage

Planned for Soft Delete, Restore, and Force Delete subject to
dependencies: - Categories. - Sub-categories. - Courses. - Batches. -
Students. - Teachers. - Users. - Other approved master/content records.

Protected from casual permanent deletion: - Payments. - Invoices. -
Attendance records. - Published exam results. - Other historically
important academic or financial records.

Where a protected record needs correction, cancellation, voiding, or
archival workflows should be considered instead of generic permanent
deletion.

#### Required Backend Behavior

-   Use normal Eloquent queries for active records.
-   Use `onlyTrashed()` for Trash listings.
-   Restore through `restore()`.
-   Permanently delete through `forceDelete()`.
-   Restore routes must explicitly resolve soft-deleted models.
-   Force-delete routes must target trashed records only.
-   Check dependent relationships and retention rules before force
    deletion.
-   Do not cascade-force-delete financial or academic history because a
    parent record is deleted.

#### API Route Pattern

``` text
GET    /api/v1/categories/trash
PATCH  /api/v1/categories/{category}/restore
DELETE /api/v1/categories/{category}/force

GET    /api/v1/sub-categories/trash
PATCH  /api/v1/sub-categories/{subCategory}/restore
DELETE /api/v1/sub-categories/{subCategory}/force
```

Apply equivalent patterns only to resources approved for soft deletion.

------------------------------------------------------------------------

## 7. User Experience and Interface Requirements

### 7.1 General UI Requirements

-   AdminLTE shall provide the Admin Panel's visual foundation.
-   Vue components shall own Vue-managed UI behavior.
-   The interface shall be responsive.
-   Internal navigation shall use Vue Router.
-   CRUD and forms shall use Axios/API communication.
-   UI shall display loading, empty, success, validation-error,
    permission-error, and server-error states.
-   Confirmation shall be required for destructive actions, especially
    Force Delete.
-   Lists shall support server-side pagination where appropriate.
-   Search and filters shall update the list without full page reloads.

### 7.2 Admin Panel Requirements

The Admin Panel shall provide authorized screens for: - Dashboard. -
Users and role/permission management. - Categories and sub-categories. -
Courses and batches. - Students and teachers. - Enrolments. - Class
sessions and attendance. - Fees, invoices, and payments. - Announcements
and learning materials. - Exams and results when that module is
introduced. - Reports. - Settings. - Trash management for eligible
modules.

### 7.3 Student and Teacher Portal Requirements

The Vue-based user-facing area shall provide role-appropriate access: -
Student: own profile, enrolments, schedule, attendance, payments,
notices, and results. - Teacher: assigned batches, class sessions,
attendance tasks, learning materials, and permitted result functions.

The portal shall not expose another user's private records.

------------------------------------------------------------------------

## 8. Data Requirements

The initial logical data model includes:

  Entity/Table               Purpose
  -------------------------- ---------------------------------------------
  `users`                    Authentication and common user information
  `student_profiles`         Student-specific profile
  `teacher_profiles`         Teacher-specific profile
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

Spatie packages shall manage their own related tables and relationships
according to their compatible versions.

### 8.1 Required Relationships

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

### 8.2 Data Integrity Rules

-   Foreign keys shall enforce relational integrity.
-   Frequently searched fields shall have appropriate indexes.
-   Duplicate active enrolments for the same student and batch shall be
    prevented.
-   Duplicate attendance records for the same enrolment and class
    session shall be prevented.
-   Multi-step enrolment and payment workflows shall use database
    transactions where consistency requires it.
-   Soft deletes shall only be applied to approved models.
-   Historical payment and attendance data shall not be casually
    deleted.
-   Exact columns, indexes, constraints, and migration details shall be
    finalized during database design.

------------------------------------------------------------------------

## 9. API Requirements

### 9.1 API Base Path

Use versioned REST API endpoints under:

``` text
/api/v1
```

### 9.2 Planned Endpoint Groups

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

### 9.3 API Behavior

-   Use RESTful resource routes.
-   Use Laravel Form Requests for validation.
-   Use API Resources for response formatting.
-   Return consistent HTTP status codes.
-   Include pagination metadata.
-   Support search and filter query parameters.
-   Return clear validation and authorization errors.
-   Enforce authentication, permissions, and record ownership/assignment
    scope.
-   Provide separate Trash, Restore, and Force Delete endpoints for
    eligible models.

The exact endpoint list and response schemas shall be finalized during
API design.

------------------------------------------------------------------------

## 10. Security and Non-Functional Requirements

### 10.1 Authentication and Authorization

-   Use Laravel Sanctum for the selected same-origin SPA authentication
    approach, subject to deployment compatibility.
-   Handle CSRF cookie initialization and stateful-domain configuration
    correctly.
-   Use credentialed Axios requests as required by the Sanctum SPA flow.
-   Enforce backend permissions through middleware and Policies.
-   Protect every private API endpoint.
-   Restrict students to their own records.
-   Restrict teachers to assigned batches and permitted academic data.
-   Do not rely on Vue route guards or hidden buttons as a security
    boundary.

### 10.2 Media Security

-   Public course images and private student documents shall use
    separate access policies.
-   Private documents shall be stored on a private disk.
-   Private downloads shall require backend authorization.
-   File type and size shall be validated.
-   Unrestricted public URLs shall not expose private student documents.

### 10.3 Data and Performance

-   Use eager loading where needed to avoid N+1 queries.
-   Use pagination for large lists.
-   Add appropriate indexes for common filters and searches.
-   Use transactions for multi-step financial and enrolment operations
    where necessary.
-   Avoid unnecessary dependencies and duplicated business logic.
-   Keep Vue components focused and reusable.

### 10.4 SPA Reliability

-   Normal CRUD and form workflows shall not reload the browser
    document.
-   Duplicate submissions shall be prevented while requests are pending.
-   API failures shall be communicated clearly.
-   UI state shall remain consistent after successful create, update,
    delete, restore, and upload operations.
-   AdminLTE plugins shall not conflict with Vue-managed DOM.

### 10.5 Testing

The project shall include verification for: - Authentication and
logout. - Role and permission enforcement. - CRUD and validation. -
Category/sub-category/course/batch relationships. - Enrolment and
duplicate-enrolment prevention. - Attendance and duplicate-attendance
prevention. - Invoice and payment consistency. - Media upload and
private document access. - Trash, Restore, and Force Delete
safeguards. - No-full-page-reload behavior for Vue-managed
interactions. - Role-specific dashboard and record visibility.

The exact test framework and frontend testing tools shall be confirmed
during environment setup.

------------------------------------------------------------------------

## 11. Acceptance Criteria

The first usable release will be accepted when all applicable criteria
below are verified.

### 11.1 Authentication and Access

-   [ ] Users can log in and log out.
-   [ ] Authenticated users can access only authorized screens and API
    endpoints.
-   [ ] Students can access only their own permitted records.
-   [ ] Teachers can access only assigned academic data.
-   [ ] Unauthorized API requests are rejected by Laravel.

### 11.2 Academic Setup

-   [ ] Admin can create and manage categories.
-   [ ] Admin can create sub-categories linked to categories.
-   [ ] Admin can create courses linked to sub-categories.
-   [ ] Admin can create batches linked to courses.
-   [ ] Search, filtering, and pagination work on the main listings.

### 11.3 People and Enrolment

-   [ ] Admin can create student and teacher profiles.
-   [ ] Students can be enrolled into batches.
-   [ ] Duplicate active enrolments are prevented.
-   [ ] Batch capacity rules are enforced.
-   [ ] Teachers can access only their assigned batches.

### 11.4 Attendance and Finance

-   [ ] Class sessions can be scheduled and viewed.
-   [ ] Attendance can be recorded and reviewed.
-   [ ] Duplicate attendance records are prevented.
-   [ ] Accountants can create invoices and record payments.
-   [ ] Partial and full payment records are handled correctly.
-   [ ] Students can view only their own payment information.

### 11.5 Media and Content

-   [ ] Authorized users can upload permitted images and documents.
-   [ ] Public images and private documents follow separate access
    rules.
-   [ ] Unauthorized users cannot access private student documents.
-   [ ] Announcements and learning materials follow their intended
    audience restrictions.

### 11.6 SPA and No-Reload Behavior

-   [ ] Internal navigation uses Vue Router without full document
    reload.
-   [ ] Admin Panel CRUD actions do not trigger a full page reload.
-   [ ] Vue-managed frontend form submissions do not trigger a full page
    reload.
-   [ ] Search, filtering, sorting, and pagination update
    asynchronously.
-   [ ] Uploads use API requests and show suitable feedback.
-   [ ] Loading, success, validation-error, and server-error states are
    visible.
-   [ ] Vue state/list views update after successful API actions without
    full reload.

### 11.7 Trash and Recovery

-   [ ] Eligible modules provide Active and Trash listings.
-   [ ] Authorized users can soft-delete eligible records.
-   [ ] Authorized users can restore trashed records.
-   [ ] Authorized users can permanently delete eligible trashed records
    after confirmation.
-   [ ] Restore and Force Delete are protected by separate backend
    permissions.
-   [ ] Force Delete is blocked for protected financial/academic
    history.
-   [ ] Force Delete is blocked when disallowed dependent records exist.
-   [ ] Trash, Restore, and Force Delete actions do not trigger full
    page reload.

### 11.8 Dashboards and Delivery

-   [ ] Dashboard content is role-specific.
-   [ ] Selected reports can be exported as CSV.
-   [ ] Critical workflows and authorization rules have been tested.
-   [ ] The application remains runnable after each implementation
    milestone.

------------------------------------------------------------------------

## 12. Release and Development Roadmap

Implementation shall proceed incrementally. Each phase must be designed,
implemented, integrated, and verified before moving to the next.

  -----------------------------------------------------------------------
  Phase                   Work                    Expected Result
  ----------------------- ----------------------- -----------------------
  0                       Inspect environment and Confirmed compatible
                          verify versions         stack

  1                       Laravel + Vue + Vite +  Application shell and
                          AdminLTE foundation     layouts

  2                       Authentication and      Sanctum, roles, and
                          authorization           permissions

  3                       Category, sub-category, Academic setup with
                          course, and batch       no-reload CRUD, Trash,
                                                  Restore, and Force
                                                  Delete

  4                       Student, teacher, and   People and enrolment
                          enrolment               management

  5                       Attendance and schedule Academic operations

  6                       Fees, invoices, and     Finance module
                          payments                

  7                       Media Library and       File and content
                          announcements           management

  8                       Dashboard and reports   Role-specific dashboard

  9                       Exams and results       Advanced academic
                                                  module

  10                      Testing, optimization,  Stable project handover
                          and documentation       
  -----------------------------------------------------------------------

### 12.1 Implementation Workflow for Each Module

1.  Confirm the feature requirement and user workflow.
2.  Identify database entities and relationships.
3.  Define backend routes, validation, authorization, and API responses.
4.  Define Vue pages, components, stores, and composables.
5.  Implement the backend.
6.  Implement the frontend using Axios and Vue Router.
7.  Verify no-full-page-reload behavior.
8.  Test permissions, validation, errors, and data integrity.
9.  Verify the module before proceeding.

------------------------------------------------------------------------

## 13. Constraints, Dependencies, and Open Decisions

The source project overview intentionally leaves some implementation
details open. These must be resolved before the relevant work begins.

  -----------------------------------------------------------------------
  Item                                Current Status / Decision Needed
  ----------------------------------- -----------------------------------
  PHP, Composer, Node.js, npm         Inspect actual environment
  versions                            

  Existing Laravel project            Confirm whether to create a new
                                      project or use an existing one

  Vue/Vite package versions           Verify compatibility

  AdminLTE major version              Select after checking Bootstrap and
                                      JavaScript dependencies

  Spatie Permission version           Verify compatibility with
                                      Laravel/PHP

  Spatie Media Library version        Verify compatibility with
                                      Laravel/PHP

  Final permission list and role      Finalize before authorization
  assignments                         implementation

  Exact database columns, indexes,    Finalize during database design
  and constraints                     

  Exact API response schemas          Finalize during API design

  Exam grading rules                  Define before Exams/Results
                                      implementation

  Trash UI approach                   Decide per-module Trash view or
                                      shared Trash interface with module
                                      filter

  Soft-delete eligibility per model   Confirm before adding `SoftDeletes`

  Force-delete dependency rules       Define for each eligible model

  CSV report formats                  Define when report module is
                                      planned

  Frontend test tooling               Select during environment setup

  AdminLTE plugin integration         Verify which plugins are needed and
                                      Vue-compatible integration approach
  -----------------------------------------------------------------------

No unsupported assumption should be silently treated as an approved
requirement. Any new feature or scope change should be reviewed before
implementation.

------------------------------------------------------------------------

## 14. Product Constraints and Principles

1.  Build for one coaching centre in the initial release.
2.  Keep the system modular without introducing unnecessary
    architecture.
3.  Use Laravel as the source of truth for business rules and
    authorization.
4.  Use Vue 3 for all Admin Panel and user-facing application screens.
5.  Use Blade only as the minimal SPA entry point.
6.  Use Axios for all Vue-managed API and CRUD operations.
7.  Do not trigger full page reloads for normal SPA interactions.
8.  Use Spatie Laravel Permission for granular authorization.
9.  Use Spatie Media Library for model-associated media.
10. Provide Soft Delete, Trash, Restore, and Force Delete for approved
    records.
11. Protect historical financial and academic data from unsafe permanent
    deletion.
12. Implement one module at a time.
13. Verify compatibility before installing packages.
14. Preserve a runnable application after every milestone.
15. Do not claim completion until the relevant acceptance criteria have
    been verified.

------------------------------------------------------------------------

## 15. Immediate Next Step

Before implementation, inspect the development environment and
confirm: - PHP and Composer versions. - Laravel version. - Node.js and
npm versions. - MySQL availability. - Existing Laravel project
structure, if any. - AdminLTE major version compatibility. - Compatible
versions of Spatie Laravel Permission and Spatie Laravel Media Library.

After verification, establish the Laravel + Vue + Vite foundation and
integrate the compatible AdminLTE layout.

**Product principle:** Deliver a small, complete, secure, and
maintainable coaching management system first. Add advanced capabilities
only after the core workflows are stable.
