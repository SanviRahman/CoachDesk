# CoachDesk --- Entity Relationship Diagram (ERD)

> **Document Type:** Database Design / ERD\
> **Product:** CoachDesk --- Coaching Management System\
> **Database:** MySQL\
> **Backend:** Laravel 12 + Eloquent ORM\
> **Source Documents:** `project-overview.md` and `PRD.md` supplied for
> this project\
> **Status:** Initial logical ERD --- proposed schema, to be finalized
> before migrations

------------------------------------------------------------------------

## 1. Purpose

This document describes the initial database entities and their
relationships for CoachDesk.

It is based on the supplied Project Overview and PRD. It covers the
single-coaching-centre scope and the modules defined in those documents:

-   Users, student profiles, and teacher profiles.
-   Categories, sub-categories, courses, and batches.
-   Enrolments, class sessions, and attendance.
-   Fee invoices and payments.
-   Announcements and learning materials.
-   Exams and results as a planned advanced module.
-   Spatie Laravel Permission tables.
-   Spatie Laravel Media Library's polymorphic media table.

### Important Schema Note

The source documents define the main entities and core relationships,
but they do not finalize every database column. Therefore:

-   Relationships explicitly stated in the source documents are treated
    as required.
-   Suggested columns in this ERD are a **proposed logical design**, not
    a claim that migrations already exist.
-   Package-managed Spatie tables must be generated from the compatible
    installed package migrations. Do not manually recreate their schema
    from this diagram.
-   Exact field types, nullability, indexes, unique constraints, delete
    rules, and package versions must be finalized during migration
    design.

------------------------------------------------------------------------

## 2. Core ERD --- Academic, People, and Operations

``` mermaid
erDiagram
    USERS ||--o| STUDENT_PROFILES : "has optional profile"
    USERS ||--o| TEACHER_PROFILES : "has optional profile"

    CATEGORIES ||--o{ SUB_CATEGORIES : contains
    SUB_CATEGORIES ||--o{ COURSES : contains
    COURSES ||--o{ BATCHES : has

    TEACHER_PROFILES o|--o{ BATCHES : "assigned to"

    STUDENT_PROFILES ||--o{ ENROLLMENTS : makes
    BATCHES ||--o{ ENROLLMENTS : receives

    BATCHES ||--o{ CLASS_SESSIONS : schedules
    CLASS_SESSIONS ||--o{ ATTENDANCE_RECORDS : has
    ENROLLMENTS ||--o{ ATTENDANCE_RECORDS : receives

    ENROLLMENTS ||--o{ FEE_INVOICES : billed
    FEE_INVOICES ||--o{ PAYMENTS : receives

    BATCHES ||--o{ EXAMS : has
    EXAMS ||--o{ EXAM_RESULTS : contains
    ENROLLMENTS ||--o{ EXAM_RESULTS : receives

    USERS ||--o{ ANNOUNCEMENTS : creates
    ANNOUNCEMENTS ||--o{ ANNOUNCEMENT_TARGETS : targets

    USERS ||--o{ LEARNING_MATERIALS : uploads
    BATCHES o|--o{ LEARNING_MATERIALS : scopes
```

### Relationship Summary

  -----------------------------------------------------------------------
  Parent            Child             Cardinality       Meaning
  ----------------- ----------------- ----------------- -----------------
  Users             Student Profiles  1 to 0/1          A student profile
                                                        may be linked to
                                                        a login account

  Users             Teacher Profiles  1 to 0/1          A teacher profile
                                                        may be linked to
                                                        a login account

  Categories        Sub-categories    1 to many         Each sub-category
                                                        belongs to one
                                                        category

  Sub-categories    Courses           1 to many         Each course
                                                        belongs to one
                                                        sub-category

  Courses           Batches           1 to many         Each batch
                                                        belongs to one
                                                        course

  Teacher Profiles  Batches           1 to many         A teacher may be
                                                        assigned to
                                                        multiple batches;
                                                        a batch has one
                                                        assigned teacher
                                                        in the initial
                                                        model

  Student Profiles  Enrolments        1 to many         A student can
                                                        have multiple
                                                        enrolments

  Batches           Enrolments        1 to many         A batch can
                                                        contain multiple
                                                        student
                                                        enrolments

  Batches           Class Sessions    1 to many         A batch has
                                                        multiple
                                                        scheduled class
                                                        sessions

  Class Sessions    Attendance        1 to many         Each session has
                    Records                             attendance
                                                        records

  Enrolments        Attendance        1 to many         An enrolment
                    Records                             receives
                                                        attendance
                                                        records across
                                                        sessions

  Enrolments        Fee Invoices      1 to many         An enrolment may
                                                        have one or more
                                                        invoices

  Fee Invoices      Payments          1 to many         An invoice can
                                                        receive partial
                                                        or full payments

  Batches           Exams             1 to many         A batch may have
                                                        multiple exams

  Exams             Exam Results      1 to many         An exam contains
                                                        results for
                                                        enrolled students

  Enrolments        Exam Results      1 to many         An enrolment may
                                                        receive results
                                                        for multiple
                                                        exams

  Users             Announcements     1 to many         A user creates
                                                        announcements

  Announcements     Announcement      1 to many         An announcement
                    Targets                             may target
                                                        multiple
                                                        audiences

  Users             Learning          1 to many         A user uploads
                    Materials                           learning
                                                        materials

  Batches           Learning          1 to many,        A learning
                    Materials         optional          material may be
                                                        scoped to a batch
  -----------------------------------------------------------------------

------------------------------------------------------------------------

## 3. Core ERD --- Authorization and Media

Spatie package tables use polymorphic and pivot relationships. Their
actual schema is package-managed and version-dependent.

``` mermaid
erDiagram
    ROLES ||--o{ ROLE_HAS_PERMISSIONS : grants
    PERMISSIONS ||--o{ ROLE_HAS_PERMISSIONS : assigned

    USERS ||--o{ MODEL_HAS_ROLES : receives
    ROLES ||--o{ MODEL_HAS_ROLES : assigned

    USERS ||--o{ MODEL_HAS_PERMISSIONS : receives
    PERMISSIONS ||--o{ MODEL_HAS_PERMISSIONS : assigned

    USERS ||--o{ MEDIA : "may own media polymorphically"
    STUDENT_PROFILES ||--o{ MEDIA : "may own media polymorphically"
    TEACHER_PROFILES ||--o{ MEDIA : "may own media polymorphically"
    CATEGORIES ||--o{ MEDIA : "may own media polymorphically"
    SUB_CATEGORIES ||--o{ MEDIA : "may own media polymorphically"
    COURSES ||--o{ MEDIA : "may own media polymorphically"
    ANNOUNCEMENTS ||--o{ MEDIA : "may own media polymorphically"
    LEARNING_MATERIALS ||--o{ MEDIA : "may own media polymorphically"
```

### Package Relationship Notes

**Spatie Laravel Permission** - `roles` stores role definitions. -
`permissions` stores permission definitions. - `role_has_permissions`
connects roles and permissions. - `model_has_roles` assigns roles to a
model using `model_type` and `model_id`. - `model_has_permissions`
assigns direct permissions to a model using `model_type` and
`model_id`. - In the initial application, the authenticated model is
Laravel's `User`. - Use the package migrations and configuration for the
installed compatible version.

**Spatie Laravel Media Library** - `media` stores media metadata. -
Media is connected to owning Eloquent models through the package's
polymorphic `model_type` and `model_id` fields. - Do not add separate
`category_image_id`, `course_image_id`, or similar media foreign keys
when using the planned Media Library relationship. - Use named media
collections to distinguish avatars, category images, course covers,
announcement attachments, learning materials, and student documents. -
Private student documents must be protected by an authorized backend
download flow.

------------------------------------------------------------------------

## 4. Entity Definitions and Proposed Columns

The following tables describe the initial logical schema. Column names
and data types are suggestions for implementation and must be reviewed
before migrations.

### 4.1 `users`

Purpose: Authentication account and common user identity.

  ---------------------------------------------------------------------------
  Column                Suggested Type    Key / Rule        Notes
  --------------------- ----------------- ----------------- -----------------
  `id`                  BIGINT UNSIGNED   PK                User identifier

  `name`                VARCHAR(255)                        Account name

  `email`               VARCHAR(255)      UNIQUE            Login email

  `password`            VARCHAR(255)                        Hashed password

  `email_verified_at`   TIMESTAMP NULL                      If email
                                                            verification is
                                                            enabled

  `status`              VARCHAR / ENUM                      Active or
                                                            inactive account
                                                            state

  `remember_token`      VARCHAR(100) NULL                   Laravel
                                                            authentication
                                                            support

  `created_at`          TIMESTAMP                           Laravel timestamp

  `updated_at`          TIMESTAMP                           Laravel timestamp

  `deleted_at`          TIMESTAMP NULL    Optional          Only if user soft
                                                            deletion is
                                                            approved
  ---------------------------------------------------------------------------

**Relationships** - Has an optional one-to-one student profile. - Has an
optional one-to-one teacher profile. - Receives roles and direct
permissions through Spatie polymorphic tables. - May create
announcements and upload learning materials. - May own media through
Spatie Media Library.

A user may have a role such as Super Admin, Admin, Teacher, Accountant,
or Student. Role definitions are not stored as a fixed `role` column in
`users`.

### 4.2 `student_profiles`

Purpose: Student-specific academic and contact information.

  -----------------------------------------------------------------------
  Column            Suggested Type    Key / Rule        Notes
  ----------------- ----------------- ----------------- -----------------
  `id`              BIGINT UNSIGNED   PK                Profile
                                                        identifier

  `user_id`         BIGINT UNSIGNED   FK → users.id,    Optional login
                    NULL              UNIQUE when       account
                                      present           

  `student_code`    VARCHAR(50)       UNIQUE            Student
                                                        identifier

  `full_name`       VARCHAR(255)                        Student name

  `phone`           VARCHAR(30) NULL                    Contact number

  `address`         TEXT NULL                           Contact address

  `date_of_birth`   DATE NULL                           Optional profile
                                                        detail

  `status`          VARCHAR / ENUM                      Student status

  `created_at`      TIMESTAMP                           

  `updated_at`      TIMESTAMP                           

  `deleted_at`      TIMESTAMP NULL    Optional          For approved
                                                        student recovery
                                                        workflow
  -----------------------------------------------------------------------

**Relationships** - Belongs to an optional `users` account. - Has many
enrolments. - May own avatar and private documents through the `media`
polymorphic relationship.

The PRD requires student profile/contact details, photo/documents,
status, enrolment history, attendance summary, and fee/payment history.
The exact profile fields must be finalized during migration design.

### 4.3 `teacher_profiles`

Purpose: Teacher-specific professional and contact information.

  ---------------------------------------------------------------------------
  Column                Suggested Type    Key / Rule        Notes
  --------------------- ----------------- ----------------- -----------------
  `id`                  BIGINT UNSIGNED   PK                Profile
                                                            identifier

  `user_id`             BIGINT UNSIGNED   FK → users.id,    Optional login
                        NULL              UNIQUE when       account
                                          present           

  `teacher_code`        VARCHAR(50)       UNIQUE            Teacher
                                                            identifier

  `full_name`           VARCHAR(255)                        Teacher name

  `phone`               VARCHAR(30) NULL                    Contact number

  `professional_info`   TEXT NULL                           Professional
                                                            information

  `status`              VARCHAR / ENUM                      Teacher status

  `created_at`          TIMESTAMP                           

  `updated_at`          TIMESTAMP                           

  `deleted_at`          TIMESTAMP NULL    Optional          For approved
                                                            teacher recovery
                                                            workflow
  ---------------------------------------------------------------------------

**Relationships** - Belongs to an optional `users` account. - Has many
assigned batches in the initial single-teacher-per-batch model. - May
own avatar and professional documents through `media`.

### 4.4 `categories`

Purpose: Top-level academic categories.

  Column          Suggested Type    Key / Rule   Notes
  --------------- ----------------- ------------ -------------------------
  `id`            BIGINT UNSIGNED   PK           
  `name`          VARCHAR(255)                   Category name
  `slug`          VARCHAR(255)      UNIQUE       URL-friendly identifier
  `description`   TEXT NULL                      
  `status`        VARCHAR / ENUM                 Active/inactive
  `created_at`    TIMESTAMP                      
  `updated_at`    TIMESTAMP                      
  `deleted_at`    TIMESTAMP NULL                 Soft Delete

**Relationships** - Has many sub-categories. - May own category image
media.

### 4.5 `sub_categories`

Purpose: Sub-category under a category.

  -----------------------------------------------------------------------
  Column            Suggested Type    Key / Rule        Notes
  ----------------- ----------------- ----------------- -----------------
  `id`              BIGINT UNSIGNED   PK                

  `category_id`     BIGINT UNSIGNED   FK →              Parent category
                                      categories.id     

  `name`            VARCHAR(255)                        

  `slug`            VARCHAR(255)                        Unique globally
                                                        or within
                                                        category;
                                                        finalize

  `description`     TEXT NULL                           

  `status`          VARCHAR / ENUM                      Active/inactive

  `created_at`      TIMESTAMP                           

  `updated_at`      TIMESTAMP                           

  `deleted_at`      TIMESTAMP NULL                      Soft Delete
  -----------------------------------------------------------------------

**Relationships** - Belongs to one category. - Has many courses. - May
own sub-category image media.

### 4.6 `courses`

Purpose: Course definition under a sub-category.

  ---------------------------------------------------------------------------
  Column              Suggested Type    Key / Rule          Notes
  ------------------- ----------------- ------------------- -----------------
  `id`                BIGINT UNSIGNED   PK                  

  `sub_category_id`   BIGINT UNSIGNED   FK →                Parent
                                        sub_categories.id   sub-category

  `title`             VARCHAR(255)                          Course title

  `code`              VARCHAR(100)      UNIQUE              Course code

  `description`       TEXT NULL                             

  `duration`          VARCHAR(100) NULL                     Duration
                                                            representation to
                                                            finalize

  `fee`               DECIMAL(12,2)                         Base course fee

  `status`            VARCHAR / ENUM                        Active/inactive

  `created_at`        TIMESTAMP                             

  `updated_at`        TIMESTAMP                             

  `deleted_at`        TIMESTAMP NULL                        Soft Delete
  ---------------------------------------------------------------------------

**Relationships** - Belongs to one sub-category. - Has many batches. -
May own a course cover image through `media`.

### 4.7 `batches`

Purpose: A scheduled delivery instance of a course.

  --------------------------------------------------------------------------------
  Column                 Suggested Type    Key / Rule            Notes
  ---------------------- ----------------- --------------------- -----------------
  `id`                   BIGINT UNSIGNED   PK                    

  `course_id`            BIGINT UNSIGNED   FK → courses.id       Course

  `teacher_profile_id`   BIGINT UNSIGNED   FK →                  Assigned teacher
                         NULL              teacher_profiles.id   

  `name`                 VARCHAR(255)                            Batch name

  `code`                 VARCHAR(100)      UNIQUE                Batch code

  `start_date`           DATE                                    

  `end_date`             DATE NULL                               

  `class_days`           JSON / TEXT NULL                        Days of the week;
                                                                 final format to
                                                                 decide

  `class_time`           VARCHAR(100) NULL                       Initial schedule
                                                                 representation

  `capacity`             INT UNSIGNED NULL                       Maximum seats

  `status`               VARCHAR / ENUM                          Upcoming,
                                                                 running,
                                                                 completed,
                                                                 cancelled

  `created_at`           TIMESTAMP                               

  `updated_at`           TIMESTAMP                               

  `deleted_at`           TIMESTAMP NULL                          Soft Delete
  --------------------------------------------------------------------------------

**Relationships** - Belongs to one course. - Belongs to an assigned
teacher profile in the initial model; assignment may be nullable. - Has
many enrolments, class sessions, exams, and batch-scoped learning
materials.

**Design note:** The source says a teacher is assigned to a batch. This
ERD assumes one primary teacher per batch. If multiple teachers per
batch are required, replace `teacher_profile_id` with a `batch_teacher`
pivot table.

### 4.8 `enrollments`

Purpose: Connect students to batches and retain enrolment
status/history.

  --------------------------------------------------------------------------------
  Column                 Suggested Type    Key / Rule            Notes
  ---------------------- ----------------- --------------------- -----------------
  `id`                   BIGINT UNSIGNED   PK                    

  `student_profile_id`   BIGINT UNSIGNED   FK →                  Student
                                           student_profiles.id   

  `batch_id`             BIGINT UNSIGNED   FK → batches.id       Batch

  `enrolled_at`          DATE                                    Enrolment date

  `status`               VARCHAR / ENUM                          Active,
                                                                 completed,
                                                                 withdrawn, or
                                                                 other agreed
                                                                 values

  `created_at`           TIMESTAMP                               

  `updated_at`           TIMESTAMP                               

  `deleted_at`           TIMESTAMP NULL    Optional              Only if enrolment
                                                                 soft deletion is
                                                                 approved
  --------------------------------------------------------------------------------

**Relationships** - Belongs to one student profile. - Belongs to one
batch. - Has many attendance records, fee invoices, and exam results.

**Integrity requirement** - Prevent duplicate active enrolment for the
same student and batch. - The exact unique-index strategy must account
for MySQL behavior and the chosen soft-delete/re-enrolment policy.
Decide whether an existing enrolment is reactivated or a new historical
enrolment row is created.

### 4.9 `class_sessions`

Purpose: Individual class session scheduled under a batch.

  -----------------------------------------------------------------------
  Column            Suggested Type    Key / Rule        Notes
  ----------------- ----------------- ----------------- -----------------
  `id`              BIGINT UNSIGNED   PK                

  `batch_id`        BIGINT UNSIGNED   FK → batches.id   Parent batch

  `title`           VARCHAR(255) NULL                   Optional session
                                                        title

  `session_date`    DATE                                Scheduled date

  `starts_at`       TIME NULL                           Session start
                                                        time

  `ends_at`         TIME NULL                           Session end time

  `status`          VARCHAR / ENUM                      Scheduled,
                                                        completed,
                                                        cancelled;
                                                        finalize

  `created_at`      TIMESTAMP                           

  `updated_at`      TIMESTAMP                           
  -----------------------------------------------------------------------

**Relationships** - Belongs to one batch. - Has many attendance records.

### 4.10 `attendance_records`

Purpose: Attendance status for an enrolment in a class session.

  -----------------------------------------------------------------------------
  Column                Suggested Type    Key / Rule          Notes
  --------------------- ----------------- ------------------- -----------------
  `id`                  BIGINT UNSIGNED   PK                  

  `class_session_id`    BIGINT UNSIGNED   FK →                Session
                                          class_sessions.id   

  `enrollment_id`       BIGINT UNSIGNED   FK → enrollments.id Student's batch
                                                              enrolment

  `status`              VARCHAR / ENUM                        Present, absent,
                                                              late, excused

  `marked_by_user_id`   BIGINT UNSIGNED   FK → users.id       User who marked
                        NULL                                  attendance

  `remarks`             TEXT NULL                             Optional note

  `created_at`          TIMESTAMP                             

  `updated_at`          TIMESTAMP                             
  -----------------------------------------------------------------------------

**Integrity requirement** - Add a unique constraint on
`(class_session_id, enrollment_id)` to prevent duplicate attendance for
the same enrolment and session. - Attendance is historical academic data
and must not be casually permanently deleted.

### 4.11 `fee_invoices`

Purpose: Fee invoice issued for an enrolment.

  ------------------------------------------------------------------------
  Column             Suggested Type    Key / Rule        Notes
  ------------------ ----------------- ----------------- -----------------
  `id`               BIGINT UNSIGNED   PK                

  `enrollment_id`    BIGINT UNSIGNED   FK →              Related enrolment
                                       enrollments.id    

  `invoice_number`   VARCHAR(100)      UNIQUE            Invoice
                                                         identifier

  `amount`           DECIMAL(12,2)                       Invoice amount

  `due_date`         DATE NULL                           Payment due date

  `status`           VARCHAR / ENUM                      Pending, partial,
                                                         paid, overdue;
                                                         finalize

  `issued_at`        DATE NULL                           Issue date

  `created_at`       TIMESTAMP                           

  `updated_at`       TIMESTAMP                           
  ------------------------------------------------------------------------

**Relationships** - Belongs to one enrolment. - Has many payments.

**Financial calculation** - Total paid should be calculated from
associated valid payments. - Due amount = invoice amount − total valid
payments. - Avoid storing duplicate calculated totals unless a
deliberate performance strategy is introduced. - Invoices are historical
financial records and must not be permanently deleted through generic
CRUD actions.

### 4.12 `payments`

Purpose: Record partial or full payment against an invoice.

  -----------------------------------------------------------------------------------
  Column                  Suggested Type    Key / Rule        Notes
  ----------------------- ----------------- ----------------- -----------------------
  `id`                    BIGINT UNSIGNED   PK                

  `fee_invoice_id`        BIGINT UNSIGNED   FK →              Invoice
                                            fee_invoices.id   

  `received_by_user_id`   BIGINT UNSIGNED   FK → users.id     Accountant/user
                          NULL                                recording payment

  `amount`                DECIMAL(12,2)                       Payment amount

  `method`                VARCHAR / ENUM                      Cash, bank, mobile
                                                              financial service,
                                                              other

  `reference`             VARCHAR(255) NULL                   Transaction/reference
                                                              information

  `paid_at`               DATETIME                            Payment time

  `created_at`            TIMESTAMP                           

  `updated_at`            TIMESTAMP                           
  -----------------------------------------------------------------------------------

**Relationships** - Belongs to one fee invoice. - May be recorded by one
user.

**Integrity and retention** - Validate payment amount against the
remaining invoice balance according to the approved business rules. -
Use database transactions when recording payment and updating related
invoice state. - Payments must not be casually force-deleted. Use a
correction, void, or reversal workflow if later approved.

### 4.13 `announcements`

Purpose: Notices and announcements published by authorized users.

  ----------------------------------------------------------------------------
  Column                 Suggested Type    Key / Rule        Notes
  ---------------------- ----------------- ----------------- -----------------
  `id`                   BIGINT UNSIGNED   PK                

  `created_by_user_id`   BIGINT UNSIGNED   FK → users.id     Creator

  `title`                VARCHAR(255)                        

  `body`                 LONGTEXT                            Announcement
                                                             content

  `status`               VARCHAR / ENUM                      Draft, published,
                                                             archived;
                                                             finalize

  `published_at`         DATETIME NULL                       Publication time

  `created_at`           TIMESTAMP                           

  `updated_at`           TIMESTAMP                           

  `deleted_at`           TIMESTAMP NULL    Optional          If approved for
                                                             soft deletion
  ----------------------------------------------------------------------------

**Relationships** - Belongs to the user who created it. - Has many
announcement targets. - May own attachment media through Spatie Media
Library.

### 4.14 `announcement_targets` --- Proposed Audience Mapping

Purpose: Allow an announcement to target one or more audiences.

The PRD requires audience selection but does not define its exact
database structure. This table is a proposed design to support multiple
targets.

  --------------------------------------------------------------------------
  Column              Suggested Type    Key / Rule         Notes
  ------------------- ----------------- ------------------ -----------------
  `id`                BIGINT UNSIGNED   PK                 

  `announcement_id`   BIGINT UNSIGNED   FK →               Announcement
                                        announcements.id   

  `target_type`       VARCHAR(100)                         Target model
                                                           type, e.g. role
                                                           or batch

  `target_id`         BIGINT UNSIGNED                      ID of target
                                                           model

  `created_at`        TIMESTAMP                            
  --------------------------------------------------------------------------

**Design note** - `target_type` and `target_id` form a polymorphic
target reference. - Possible audiences include a role or a batch. - If
the final product only needs one audience per announcement, this can be
simplified to fields on `announcements`. - Finalize the audience rules
before migrations.

### 4.15 `learning_materials`

Purpose: Store metadata and access scope for class notes, PDFs, and
other learning resources.

  -------------------------------------------------------------------------------------
  Column                  Suggested Type    Key / Rule        Notes
  ----------------------- ----------------- ----------------- -------------------------
  `id`                    BIGINT UNSIGNED   PK                

  `batch_id`              BIGINT UNSIGNED   FK → batches.id   Optional batch scope
                          NULL                                

  `uploaded_by_user_id`   BIGINT UNSIGNED   FK → users.id     Uploader

  `title`                 VARCHAR(255)                        

  `description`           TEXT NULL                           

  `visibility`            VARCHAR / ENUM                      Batch/enrolled-students
                                                              or another approved scope

  `created_at`            TIMESTAMP                           

  `updated_at`            TIMESTAMP                           

  `deleted_at`            TIMESTAMP NULL    Optional          If approved for soft
                                                              deletion
  -------------------------------------------------------------------------------------

**Relationships** - Belongs to the uploading user. - May belong to a
batch. - Owns file media through Spatie Media Library. -
Enrolled-student access is determined through the batch's enrolments and
backend authorization.

### 4.16 `exams` --- Advanced Planned Module

Purpose: Examination scheduled for a batch.

  Column          Suggested Type    Key / Rule        Notes
  --------------- ----------------- ----------------- ---------------------------------------
  `id`            BIGINT UNSIGNED   PK                
  `batch_id`      BIGINT UNSIGNED   FK → batches.id   Related batch
  `title`         VARCHAR(255)                        Exam name
  `exam_date`     DATE                                
  `total_marks`   DECIMAL(8,2)                        Maximum marks
  `status`        VARCHAR / ENUM                      Draft, published, completed; finalize
  `created_at`    TIMESTAMP                           
  `updated_at`    TIMESTAMP                           

**Relationships** - Belongs to one batch. - Has many exam results.

The exam module is planned for implementation after the main academic
workflow is stable.

### 4.17 `exam_results` --- Advanced Planned Module

Purpose: Store a student's result for an exam.

  ------------------------------------------------------------------------
  Column             Suggested Type    Key / Rule        Notes
  ------------------ ----------------- ----------------- -----------------
  `id`               BIGINT UNSIGNED   PK                

  `exam_id`          BIGINT UNSIGNED   FK → exams.id     Exam

  `enrollment_id`    BIGINT UNSIGNED   FK →              Student's
                                       enrollments.id    enrolment

  `marks_obtained`   DECIMAL(8,2)                        Obtained marks

  `percentage`       DECIMAL(6,2) NULL                   May be calculated

  `grade`            VARCHAR(20) NULL                    Based on grading
                                                         rules

  `published_at`     DATETIME NULL                       Result
                                                         publication

  `created_at`       TIMESTAMP                           

  `updated_at`       TIMESTAMP                           
  ------------------------------------------------------------------------

**Integrity requirement** - Add a unique constraint on
`(exam_id, enrollment_id)` so one enrolment has at most one result per
exam, unless a retake/attempt model is later approved. - Students may
view only their own published results. - Published result history must
be protected from casual permanent deletion.

------------------------------------------------------------------------

## 5. Spatie Package Tables

### 5.1 Spatie Laravel Permission

The following tables are package-managed. Their exact columns and
indexes must come from the installed package migration.

  -----------------------------------------------------------------------
  Table                               Purpose
  ----------------------------------- -----------------------------------
  `roles`                             Role definitions such as Super
                                      Admin, Admin, Teacher, Accountant,
                                      Student

  `permissions`                       Permission definitions such as
                                      `categories.view` and
                                      `students.update`

  `model_has_roles`                   Polymorphic assignment of roles to
                                      users/models

  `model_has_permissions`             Polymorphic direct permission
                                      assignment

  `role_has_permissions`              Many-to-many relationship between
                                      roles and permissions
  -----------------------------------------------------------------------

Conceptual relationships:

``` text
Role N ---- N Permission
User N ---- N Role
User N ---- N Direct Permission
```

The many-to-many relationships are implemented through the package's
pivot tables.

### 5.2 Spatie Laravel Media Library

The package-managed `media` table connects media to models using a
polymorphic relationship.

Conceptual structure:

``` text
Media
  ├── model_type
  ├── model_id
  ├── collection_name
  ├── file_name
  ├── mime_type
  ├── disk
  └── package-managed metadata
```

Potential media owners in CoachDesk:

-   Users.
-   Student profiles.
-   Teacher profiles.
-   Categories.
-   Sub-categories.
-   Courses.
-   Announcements.
-   Learning materials.

Do not add a conventional foreign key from `media.model_id` to every
possible owner table. It is a polymorphic association.

------------------------------------------------------------------------

## 6. Soft Delete and Data Retention Map

Soft deletion must be applied only to approved models.

  ------------------------------------------------------------------------------------
  Entity           Soft Delete    Restore        Force Delete   Reason / Restriction
  ---------------- -------------- -------------- -------------- ----------------------
  Categories       Yes            Yes            Permission +   Master data
                                                 dependency     
                                                 check          

  Sub-categories   Yes            Yes            Permission +   Master data
                                                 dependency     
                                                 check          

  Courses          Yes            Yes            Permission +   Master data
                                                 dependency     
                                                 check          

  Batches          Yes            Yes            Permission +   Academic
                                                 dependency     history/dependencies
                                                 check          must be checked

  Student Profiles Yes            Yes            Permission +   Preserve linked
                                                 dependency     enrolment/history
                                                 check          

  Teacher Profiles Yes            Yes            Permission +   Preserve assigned
                                                 dependency     batch/history
                                                 check          

  Users            Optional       Optional       Strict         Account and profile
                                                 permission +   dependencies
                                                 dependency     
                                                 check          

  Enrolments       To be decided  To be decided  Not generic    Enrolment history and
                                                                financial/attendance
                                                                dependencies

  Class Sessions   Not generic by N/A            Not generic    Schedule/attendance
                   default                                      history

  Attendance       No generic     N/A            Protected      Historical academic
  Records          deletion                                     record

  Fee Invoices     No generic     N/A            Protected      Financial record
                   deletion                                     

  Payments         No generic     N/A            Protected      Financial record
                   deletion                                     

  Exam Results     No generic     N/A            Protected      Academic record
                   deletion                      after          
                                                 publication    

  Announcements    Optional       If             Permission +   Content record
                                  soft-deleted   dependency     
                                                 check          

  Learning         Optional       If             Permission +   Content and media
  Materials                       soft-deleted   media cleanup  record
                                                 check          
  ------------------------------------------------------------------------------------

### Required Deletion Rules

1.  Active listings must exclude soft-deleted records.
2.  Trash listings must use `onlyTrashed()`.
3.  Restore must use Eloquent `restore()`.
4.  Force Delete must use `forceDelete()` and target trashed records
    only.
5.  Restore and Force Delete require separate Spatie permissions.
6.  The API must check related records before permanent deletion.
7.  Financial and academic history must not be cascade-force-deleted.
8.  All Trash, Restore, and Force Delete actions must use Axios and
    update Vue state without a full page reload.

------------------------------------------------------------------------

## 7. Required Constraints and Indexes

The following are recommended database integrity rules based on the PRD.

  -------------------------------------------------------------------------------
  Table                    Constraint / Index             Purpose
  ------------------------ ------------------------------ -----------------------
  `users`                  UNIQUE(`email`)                Unique login email

  `student_profiles`       UNIQUE(`student_code`)         Unique student code

  `student_profiles`       UNIQUE(`user_id`) when not     One login account per
                           null                           student profile

  `teacher_profiles`       UNIQUE(`teacher_code`)         Unique teacher code

  `teacher_profiles`       UNIQUE(`user_id`) when not     One login account per
                           null                           teacher profile

  `categories`             UNIQUE(`slug`)                 Unique category slug

  `sub_categories`         INDEX(`category_id`)           Parent lookup/filter

  `courses`                INDEX(`sub_category_id`),      Parent lookup and
                           UNIQUE(`code`)                 course code

  `batches`                INDEX(`course_id`),            Batch filtering
                           INDEX(`teacher_profile_id`)    

  `enrollments`            INDEX(`student_profile_id`),   Student/batch lookup
                           INDEX(`batch_id`)              

  `enrollments`            Active duplicate prevention    Prevent duplicate
                           rule                           active enrolment

  `class_sessions`         INDEX(`batch_id`,              Schedule lookup
                           `session_date`)                

  `attendance_records`     UNIQUE(`class_session_id`,     Prevent duplicate
                           `enrollment_id`)               attendance

  `fee_invoices`           UNIQUE(`invoice_number`),      Invoice lookup
                           INDEX(`enrollment_id`)         

  `payments`               INDEX(`fee_invoice_id`),       Payment history and
                           INDEX(`paid_at`)               reports

  `exams`                  INDEX(`batch_id`, `exam_date`) Batch exam lookup

  `exam_results`           UNIQUE(`exam_id`,              One result per
                           `enrollment_id`)               enrolment per exam

  `announcements`          INDEX(`status`,                Published notice lookup
                           `published_at`)                

  `announcement_targets`   INDEX(`announcement_id`),      Audience lookup
                           INDEX(`target_type`,           
                           `target_id`)                   

  `learning_materials`     INDEX(`batch_id`)              Batch-scoped material
                                                          lookup
  -------------------------------------------------------------------------------

Unique constraints involving soft-deleted rows need special
consideration. For example, the enrolment duplicate-prevention strategy
must match the intended re-enrolment behavior and MySQL's available
indexing strategy.

------------------------------------------------------------------------

## 8. Business Rules Reflected in the ERD

### 8.1 Academic Hierarchy

-   A category can have multiple sub-categories.
-   A sub-category belongs to one category.
-   A sub-category can have multiple courses.
-   A course belongs to one sub-category.
-   A course can have multiple batches.
-   A batch belongs to one course.

### 8.2 People and Enrolment

-   A student profile may be linked to a login user.
-   A teacher profile may be linked to a login user.
-   A student can have multiple enrolments.
-   A batch can have multiple enrolments.
-   Duplicate active enrolment for the same student and batch must be
    prevented.
-   Batch capacity must be checked before enrolment confirmation.

### 8.3 Attendance

-   Attendance is recorded against a class session and a batch
    enrolment.
-   A student can have one attendance record per class session.
-   Allowed statuses: present, absent, late, excused.
-   Teachers can mark attendance only for assigned batches.
-   Students can view only their own attendance.

### 8.4 Finance

-   An enrolment may have multiple invoices.
-   An invoice may have multiple partial/full payments.
-   Due amount is based on invoice amount minus valid payments.
-   Payment operations must preserve financial consistency.
-   Payment and invoice records must not be generically force-deleted.

### 8.5 Exams and Results

-   An exam belongs to a batch.
-   An exam has results for enrolled students.
-   A result belongs to one exam and one enrolment.
-   Students can view only their own published results.
-   Grading rules remain an open decision.

### 8.6 Media

-   Media belongs to models through Spatie's polymorphic relationship.
-   Profile images, category images, course covers, announcement
    attachments, learning materials, and student documents use named
    collections.
-   Private student documents require authorization before download.

------------------------------------------------------------------------

## 9. Open Database Decisions

These decisions are not fully specified in the source documents and must
be finalized before migrations.

  ---------------------------------------------------------------------------------------
  Decision                  Recommended Starting Point     Needs Confirmation
  ------------------------- ------------------------------ ------------------------------
  Teacher assignment per    One primary teacher through    Whether multiple teachers per
  batch                     `batches.teacher_profile_id`   batch are needed

  Student/teacher login     Optional unique `user_id` on   Whether every student/teacher
                            each profile                   must have login access

  Student/teacher identity  Keep profile-specific code and Which fields are mandatory
  fields                    contact details                

  Enrolment history         Keep enrolment records with    How re-enrolment into the same
                            explicit status                batch should work

  Invoice structure         One enrolment to many invoices Whether installments are
                                                           separate invoices or partial
                                                           payments

  Invoice balance           Calculate from payment records Whether cached totals are
                                                           needed

  Announcement audience     Proposed                       Which target types are
                            `announcement_targets`         required
                            polymorphic table              

  Learning material         Optional batch scope and       Whether role-wide/global
  audience                  enrolment-based authorization  material is needed

  Batch class schedule      Store class days/time on batch Whether multiple weekly time
                            and dated sessions in          slots are needed
                            `class_sessions`               

  Soft delete on users      Optional, with dependency      Whether deactivation is
                            checks                         sufficient

  Soft delete on enrolments Not generic by default         Whether withdrawal/status is
                                                           enough

  Soft delete on            Optional                       Retention and restore
  announcements/materials                                  requirements

  Exam attempts             One result per exam/enrolment  Whether retakes or multiple
                                                           attempts are required

  Slug uniqueness           Decide global or parent-scoped Category/sub-category/course
                            uniqueness                     rules

  Media conversion          Generate only required         Exact thumbnail sizes and
                            conversions                    queue strategy
  ---------------------------------------------------------------------------------------

------------------------------------------------------------------------

## 10. Tables Not Included in the Core ERD

The following are not part of the current core product data model unless
a later requirement calls for them:

-   Multi-branch or multi-tenant tables.
-   Payroll and teacher salary tables.
-   Online payment gateway transaction tables.
-   Full accounting ledger and chart-of-accounts tables.
-   In-app notification tables.
-   Activity/audit log tables.
-   Bulk import staging tables.
-   Native mobile application-specific tables.

Laravel framework support tables such as cache, sessions, password reset
tokens, and queue jobs may exist depending on configuration, but they
are infrastructure tables rather than core CoachDesk domain entities.

------------------------------------------------------------------------

## 11. ERD-to-Implementation Checklist

Before writing migrations:

-   [ ] Confirm the final list of domain tables.
-   [ ] Confirm whether every student and teacher needs a login account.
-   [ ] Confirm one or multiple teachers per batch.
-   [ ] Finalize enrolment reactivation/history behavior.
-   [ ] Finalize invoice and partial-payment rules.
-   [ ] Finalize announcement audience mapping.
-   [ ] Finalize learning-material access scope.
-   [ ] Confirm which models use SoftDeletes.
-   [ ] Define Force Delete dependency rules.
-   [ ] Confirm exam attempt/retake behavior.
-   [ ] Verify package versions and use official Spatie migrations.
-   [ ] Finalize column types, nullability, foreign keys, indexes, and
    unique constraints.
-   [ ] Review the ERD before creating Laravel migrations.

------------------------------------------------------------------------

## 12. Source Alignment

This ERD follows the supplied documents:

-   **Project Overview:** target roles, technology stack, architecture,
    modules, database overview, core relationships, Spatie packages, API
    conventions, media collections, and deletion safeguards.
-   **PRD:** functional requirements, role access expectations, SPA
    behavior, Trash/Restore/Force Delete rules, security requirements,
    acceptance criteria, and open decisions.

Where the source documents name a relationship but do not define all
columns, this ERD marks the additional schema as proposed. No migration
or implementation is implied by this document.

**Database principle:** Keep the initial schema relational,
understandable, and aligned with the coaching-centre workflow. Preserve
financial and academic history, and finalize all open decisions before
migrations.
