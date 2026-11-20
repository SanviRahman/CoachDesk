# CoachDesk --- Database Schema

> **Document Type:** Database Schema Specification\
> **Product:** CoachDesk --- Coaching Management System\
> **Database:** MySQL\
> **Backend:** Laravel 13 + Eloquent ORM\
> **Source Documents:** `project-overview.md`, `PRD.md`, and `ERD.md`\
> **Status:** Proposed logical schema --- review open decisions before
> creating migrations

------------------------------------------------------------------------

## 1. Purpose and Schema Rules

This document translates the approved project scope and logical ERD into
a proposed, implementation-oriented database schema.

It describes: - Domain tables and their columns. - Primary keys, foreign
keys, unique constraints, and indexes. - Relationships and delete
behavior. - Soft-delete eligibility and data-retention rules. - Spatie
package-managed tables. - Migration dependency order.

### 1.1 Important Rules

1.  This is a proposed schema, not a record of existing migrations.
2.  Columns marked **Proposed** must be reviewed before migration
    creation.
3.  Use Laravel migrations and Eloquent relationships.
4.  Use `foreignId()`/`constrained()` for standard unsigned bigint
    foreign keys where appropriate.
5.  Use `decimal` for money; do not use floating-point columns for
    monetary values.
6.  Use Laravel timestamps on normal domain tables unless the table is a
    simple pivot that only needs `created_at`.
7.  Use `SoftDeletes` only on models approved in the deletion policy.
8.  Do not manually recreate Spatie package tables. Use the migrations
    published by the compatible installed package versions.
9.  Do not add a `role` column to `users`; role assignment is handled by
    Spatie Laravel Permission.
10. Do not add direct image foreign keys such as `category_image_id`
    when the model uses Spatie Media Library's polymorphic relationship.

### 1.2 Naming Conventions

  -----------------------------------------------------------------------
  Item                                Convention
  ----------------------------------- -----------------------------------
  Table names                         Plural snake_case

  Model names                         Singular PascalCase

  Primary key                         `id`

  Foreign key                         `{related_model}_id`

  Timestamps                          `created_at`, `updated_at`

  Soft delete                         `deleted_at`

  Money                               `DECIMAL(12,2)` unless a smaller
                                      documented range is sufficient

  Status                              Proposed `VARCHAR(30)` with backend
                                      validation / constants or PHP enums
  -----------------------------------------------------------------------

The source documents allow VARCHAR/ENUM for statuses. This schema
recommends `VARCHAR(30)` for easier future status changes, with allowed
values enforced in Laravel validation and domain logic. This is a design
recommendation and can be reviewed before migrations.

------------------------------------------------------------------------

## 2. Core Table Overview

  ------------------------------------------------------------------------------------
                     \# Table                    Purpose             Soft Delete
  --------------------- ------------------------ ------------------- -----------------
                      1 `users`                  Authentication and  Optional; confirm
                                                 common account data 

                      2 `student_profiles`       Student-specific    Yes, planned
                                                 profile             

                      3 `teacher_profiles`       Teacher-specific    Yes, planned
                                                 profile             

                      4 `categories`             Academic categories Yes

                      5 `sub_categories`         Child categories    Yes

                      6 `courses`                Course definitions  Yes

                      7 `batches`                Scheduled course    Yes
                                                 batches             

                      8 `enrollments`            Student-to-batch    Pending decision
                                                 enrolments          

                      9 `class_sessions`         Scheduled class     No generic soft
                                                 sessions            delete

                     10 `attendance_records`     Attendance for an   No
                                                 enrolment/session   

                     11 `fee_invoices`           Invoices for        No
                                                 enrolments          

                     12 `payments`               Partial/full        No
                                                 invoice payments    

                     13 `announcements`          Published notices   Optional

                     14 `announcement_targets`   Audience mapping    No
                                                 for announcements   

                     15 `learning_materials`     Learning material   Optional
                                                 metadata            

                     16 `exams`                  Batch exams;        No generic soft
                                                 advanced planned    delete
                                                 module              

                     17 `exam_results`           Student exam        No
                                                 results; advanced   
                                                 planned module      

                Package `roles`, `permissions`,  Spatie Laravel      Package-managed
                        pivots                   Permission          

                Package `media`                  Spatie Laravel      Package-managed
                                                 Media Library       
  ------------------------------------------------------------------------------------

------------------------------------------------------------------------

## 3. Table Definitions

## 3.1 `users`

**Purpose:** Laravel authentication account and common user identity.

  -------------------------------------------------------------------------------------
  Column                MySQL Type                  Null Key /         Notes
                                                         Constraint    
  --------------------- -------------- ----------------- ------------- ----------------
  `id`                  BIGINT                        No PK            Laravel default
                        UNSIGNED                                       primary key

  `name`                VARCHAR(255)                  No               Account display
                                                                       name

  `email`               VARCHAR(255)                  No UNIQUE        Login email

  `password`            VARCHAR(255)                  No               Hashed password

  `email_verified_at`   TIMESTAMP                    Yes               If email
                                                                       verification is
                                                                       enabled

  `status`              VARCHAR(30)                   No DEFAULT       Proposed:
                                                         `active`      `active`,
                                                                       `inactive`

  `remember_token`      VARCHAR(100)                 Yes               Laravel
                                                                       authentication

  `created_at`          TIMESTAMP                    Yes               

  `updated_at`          TIMESTAMP                    Yes               

  `deleted_at`          TIMESTAMP                    Yes Optional      Only if user
                                                                       soft deletion is
                                                                       approved
  -------------------------------------------------------------------------------------

**Relationships** - Has one optional `student_profiles` record. - Has
one optional `teacher_profiles` record. - Receives roles and direct
permissions through Spatie polymorphic tables. - Has many announcements
through `created_by_user_id`. - Has many learning materials through
`uploaded_by_user_id`. - May own media through Spatie Media Library.

**Important:** Super Admin, Admin, Teacher, Accountant, and Student are
roles---not values in a fixed `users.role` column.

------------------------------------------------------------------------

## 3.2 `student_profiles`

**Purpose:** Student-specific academic and contact information.

  ------------------------------------------------------------------------------
  Column            MySQL Type                  Null Key /         Notes
                                                     Constraint    
  ----------------- -------------- ----------------- ------------- -------------
  `id`              BIGINT                        No PK            
                    UNSIGNED                                       

  `user_id`         BIGINT                       Yes UNIQUE, FK →  Optional
                    UNSIGNED                         `users.id`    login account

  `student_code`    VARCHAR(50)                   No UNIQUE        Student
                                                                   identifier

  `full_name`       VARCHAR(255)                  No               

  `phone`           VARCHAR(30)                  Yes INDEX if      
                                                     frequently    
                                                     searched      

  `address`         TEXT                         Yes               

  `date_of_birth`   DATE                         Yes               

  `status`          VARCHAR(30)                   No DEFAULT       Proposed
                                                     `active`      student
                                                                   status

  `created_at`      TIMESTAMP                    Yes               

  `updated_at`      TIMESTAMP                    Yes               

  `deleted_at`      TIMESTAMP                    Yes INDEX         Approved
                                                     recommended   soft-delete
                                                                   workflow
  ------------------------------------------------------------------------------

**Relationships** - Belongs to an optional `users` account. - Has many
`enrollments`. - May own avatar and private documents through `media`.

**Foreign key behavior:** `user_id` is nullable. If a user account is
permanently removed under an approved policy, `nullOnDelete()` can
preserve the student profile. Soft-deleting the user does not
automatically delete the profile.

------------------------------------------------------------------------

## 3.3 `teacher_profiles`

**Purpose:** Teacher-specific professional and contact information.

  ----------------------------------------------------------------------------------
  Column                MySQL Type                  Null Key /         Notes
                                                         Constraint    
  --------------------- -------------- ----------------- ------------- -------------
  `id`                  BIGINT                        No PK            
                        UNSIGNED                                       

  `user_id`             BIGINT                       Yes UNIQUE, FK →  Optional
                        UNSIGNED                         `users.id`    login account

  `teacher_code`        VARCHAR(50)                   No UNIQUE        Teacher
                                                                       identifier

  `full_name`           VARCHAR(255)                  No               

  `phone`               VARCHAR(30)                  Yes INDEX if      
                                                         frequently    
                                                         searched      

  `professional_info`   TEXT                         Yes               

  `status`              VARCHAR(30)                   No DEFAULT       Proposed
                                                         `active`      teacher
                                                                       status

  `created_at`          TIMESTAMP                    Yes               

  `updated_at`          TIMESTAMP                    Yes               

  `deleted_at`          TIMESTAMP                    Yes INDEX         Approved
                                                         recommended   soft-delete
                                                                       workflow
  ----------------------------------------------------------------------------------

**Relationships** - Belongs to an optional `users` account. - Has many
assigned batches in the initial single-primary-teacher model. - May own
avatar and professional documents through `media`.

**Foreign key behavior:** `user_id` is nullable. An approved permanent
user-account deletion may set this field to null while preserving the
teacher profile.

------------------------------------------------------------------------

## 3.4 `categories`

**Purpose:** Top-level academic categories.

  ----------------------------------------------------------------------------
  Column          MySQL Type                  Null Key /         Notes
                                                   Constraint    
  --------------- -------------- ----------------- ------------- -------------
  `id`            BIGINT                        No PK            
                  UNSIGNED                                       

  `name`          VARCHAR(255)                  No INDEX         
                                                   recommended   

  `slug`          VARCHAR(255)                  No UNIQUE        Global
                                                                 category slug

  `description`   TEXT                         Yes               

  `status`        VARCHAR(30)                   No DEFAULT       Proposed:
                                                   `active`      `active`,
                                                                 `inactive`

  `created_at`    TIMESTAMP                    Yes               

  `updated_at`    TIMESTAMP                    Yes               

  `deleted_at`    TIMESTAMP                    Yes INDEX         Soft Delete
                                                   recommended   
  ----------------------------------------------------------------------------

**Relationships** - Has many `sub_categories`. - Owns optional category
image media through Spatie Media Library.

**Delete rule:** Do not force-delete a category while it has
sub-categories or dependent academic history. Check dependencies in
Laravel before `forceDelete()`.

------------------------------------------------------------------------

## 3.5 `sub_categories`

**Purpose:** Sub-category under one category.

  -----------------------------------------------------------------------------------
  Column          MySQL Type                  Null Key / Constraint   Notes
  --------------- -------------- ----------------- ------------------ ---------------
  `id`            BIGINT                        No PK                 
                  UNSIGNED                                            

  `category_id`   BIGINT                        No FK →               Parent category
                  UNSIGNED                         `categories.id`,   
                                                   INDEX              

  `name`          VARCHAR(255)                  No                    

  `slug`          VARCHAR(255)                  No UNIQUE strategy    Proposed
                                                   pending            parent-scoped
                                                                      uniqueness

  `description`   TEXT                         Yes                    

  `status`        VARCHAR(30)                   No DEFAULT `active`   

  `created_at`    TIMESTAMP                    Yes                    

  `updated_at`    TIMESTAMP                    Yes                    

  `deleted_at`    TIMESTAMP                    Yes INDEX recommended  Soft Delete
  -----------------------------------------------------------------------------------

**Recommended slug rule:** `UNIQUE(category_id, slug)` if sub-category
slugs only need to be unique inside a category. If routes require
globally unique slugs, use a global unique constraint instead. Confirm
before migration.

**Relationships** - Belongs to one `category`. - Has many `courses`. -
Owns optional sub-category image media.

------------------------------------------------------------------------

## 3.6 `courses`

**Purpose:** Course definition under one sub-category.

  -------------------------------------------------------------------------------------------
  Column              MySQL Type                   Null Key / Constraint       Notes
  ------------------- --------------- ----------------- ---------------------- --------------
  `id`                BIGINT UNSIGNED                No PK                     

  `sub_category_id`   BIGINT UNSIGNED                No FK →                   Parent
                                                        `sub_categories.id`,   sub-category
                                                        INDEX                  

  `title`             VARCHAR(255)                   No INDEX recommended      

  `code`              VARCHAR(100)                   No UNIQUE                 Course code

  `description`       TEXT                          Yes                        

  `duration`          VARCHAR(100)                  Yes                        Format to be
                                                                               finalized

  `fee`               DECIMAL(12,2)                  No DEFAULT `0.00`         Base course
                                                                               fee

  `status`            VARCHAR(30)                    No DEFAULT `active`       

  `created_at`        TIMESTAMP                     Yes                        

  `updated_at`        TIMESTAMP                     Yes                        

  `deleted_at`        TIMESTAMP                     Yes INDEX recommended      Soft Delete
  -------------------------------------------------------------------------------------------

**Relationships** - Belongs to one `sub_category`. - Has many
`batches`. - Owns an optional course cover image through `media`.

**Delete rule:** A course with batches or enrolment history must not be
force-deleted unless dependencies are resolved under an approved rule.

------------------------------------------------------------------------

## 3.7 `batches`

**Purpose:** Scheduled delivery instance of a course.

  -------------------------------------------------------------------------------------------------
  Column                 MySQL Type                  Null Key / Constraint         Notes
  ---------------------- -------------- ----------------- ------------------------ ----------------
  `id`                   BIGINT                        No PK                       
                         UNSIGNED                                                  

  `course_id`            BIGINT                        No FK → `courses.id`, INDEX Course
                         UNSIGNED                                                  

  `teacher_profile_id`   BIGINT                       Yes FK →                     Primary assigned
                         UNSIGNED                         `teacher_profiles.id`,   teacher
                                                          INDEX                    

  `name`                 VARCHAR(255)                  No                          

  `code`                 VARCHAR(100)                  No UNIQUE                   Batch code

  `start_date`           DATE                          No                          

  `end_date`             DATE                         Yes                          

  `class_days`           JSON                         Yes                          Days of the week

  `class_time`           VARCHAR(100)                 Yes                          Initial schedule
                                                                                   representation

  `capacity`             INT UNSIGNED                 Yes                          Maximum seats

  `status`               VARCHAR(30)                   No DEFAULT `upcoming`       `upcoming`,
                                                                                   `running`,
                                                                                   `completed`,
                                                                                   `cancelled`

  `created_at`           TIMESTAMP                    Yes                          

  `updated_at`           TIMESTAMP                    Yes                          

  `deleted_at`           TIMESTAMP                    Yes INDEX recommended        Soft Delete
  -------------------------------------------------------------------------------------------------

**Relationships** - Belongs to one `course`. - Belongs to an optional
primary `teacher_profile`. - Has many `enrollments`. - Has many
`class_sessions`. - Has many `exams`. - May have many batch-scoped
`learning_materials`.

**Teacher assignment decision:** This initial schema supports one
primary teacher per batch. If multiple teachers per batch are required,
introduce a `batch_teacher` pivot table before migrations.

**Schedule note:** `class_days` and `class_time` describe the recurring
batch schedule. `class_sessions` stores individual dated sessions.

------------------------------------------------------------------------

## 3.8 `enrollments`

**Purpose:** Connect a student to a batch and retain enrolment
status/history.

  -----------------------------------------------------------------------------------------------
  Column                 MySQL Type                 Null Key / Constraint         Notes
  ---------------------- ------------- ----------------- ------------------------ ---------------
  `id`                   BIGINT                       No PK                       
                         UNSIGNED                                                 

  `student_profile_id`   BIGINT                       No FK →                     Student
                         UNSIGNED                        `student_profiles.id`,   
                                                         INDEX                    

  `batch_id`             BIGINT                       No FK → `batches.id`, INDEX Batch
                         UNSIGNED                                                 

  `enrolled_at`          DATE                         No                          Enrolment date

  `status`               VARCHAR(30)                  No DEFAULT `active`         Proposed:
                                                                                  `active`,
                                                                                  `completed`,
                                                                                  `withdrawn`

  `created_at`           TIMESTAMP                   Yes                          

  `updated_at`           TIMESTAMP                   Yes                          

  `deleted_at`           TIMESTAMP                   Yes Optional; pending        Do not enable
                                                         decision                 automatically
  -----------------------------------------------------------------------------------------------

**Relationships** - Belongs to one `student_profile`. - Belongs to one
`batch`. - Has many `attendance_records`. - Has many `fee_invoices`. -
Has many `exam_results`.

**Required business rule:** Prevent duplicate active enrolment for the
same student and batch.

**Important unresolved decision:** Decide whether re-enrolment into the
same batch should: 1. Reactivate the existing enrolment record, or 2.
Create a new historical enrolment record.

Do not add a final unique constraint until this is decided. A simple
unique constraint on `(student_profile_id, batch_id)` may prevent valid
historical re-enrolment. If soft deletes are enabled, the unique-index
strategy must also account for MySQL behavior and the chosen
re-enrolment policy.

------------------------------------------------------------------------

## 3.9 `class_sessions`

**Purpose:** Individual class session scheduled under a batch.

  --------------------------------------------------------------------------------
  Column           MySQL Type                  Null Key /           Notes
                                                    Constraint      
  ---------------- -------------- ----------------- --------------- --------------
  `id`             BIGINT                        No PK              
                   UNSIGNED                                         

  `batch_id`       BIGINT                        No FK →            Parent batch
                   UNSIGNED                         `batches.id`,   
                                                    INDEX           

  `title`          VARCHAR(255)                 Yes                 Optional
                                                                    session title

  `session_date`   DATE                          No INDEX with      Scheduled date
                                                    `batch_id`      

  `starts_at`      TIME                         Yes                 Session start

  `ends_at`        TIME                         Yes                 Session end

  `status`         VARCHAR(30)                   No DEFAULT         Proposed:
                                                    `scheduled`     `scheduled`,
                                                                    `completed`,
                                                                    `cancelled`

  `created_at`     TIMESTAMP                    Yes                 

  `updated_at`     TIMESTAMP                    Yes                 
  --------------------------------------------------------------------------------

**Relationships** - Belongs to one `batch`. - Has many
`attendance_records`.

**Deletion policy:** No generic soft-delete or force-delete workflow by
default because class sessions may have attendance history.

------------------------------------------------------------------------

## 3.10 `attendance_records`

**Purpose:** Record attendance for one enrolment in one class session.

  -----------------------------------------------------------------------------------------
  Column                MySQL Type                 Null Key / Constraint      Notes
  --------------------- ------------- ----------------- --------------------- -------------
  `id`                  BIGINT                       No PK                    
                        UNSIGNED                                              

  `class_session_id`    BIGINT                       No FK →                  Session
                        UNSIGNED                        `class_sessions.id`   

  `enrollment_id`       BIGINT                       No FK → `enrollments.id` Student's
                        UNSIGNED                                              batch
                                                                              enrolment

  `status`              VARCHAR(30)                  No                       `present`,
                                                                              `absent`,
                                                                              `late`,
                                                                              `excused`

  `marked_by_user_id`   BIGINT                      Yes FK → `users.id`,      User who
                        UNSIGNED                        INDEX optional        marked
                                                                              attendance

  `remarks`             TEXT                        Yes                       

  `created_at`          TIMESTAMP                   Yes                       

  `updated_at`          TIMESTAMP                   Yes                       
  -----------------------------------------------------------------------------------------

**Required constraints** - `UNIQUE(class_session_id, enrollment_id)` to
prevent duplicate attendance for the same enrolment and session. - Index
`enrollment_id` for student attendance history. - Use `nullOnDelete()`
for `marked_by_user_id` if preserving attendance after a user-account
deletion is approved. - Do not cascade-delete attendance when a class
session or enrolment is removed. - Attendance is protected academic
history.

------------------------------------------------------------------------

## 3.11 `fee_invoices`

**Purpose:** Fee invoice issued for an enrolment.

  --------------------------------------------------------------------------------------
  Column             MySQL Type                   Null Key / Constraint    Notes
  ------------------ --------------- ----------------- ------------------- -------------
  `id`               BIGINT UNSIGNED                No PK                  

  `enrollment_id`    BIGINT UNSIGNED                No FK →                Related
                                                       `enrollments.id`,   enrolment
                                                       INDEX               

  `invoice_number`   VARCHAR(100)                   No UNIQUE              Invoice
                                                                           identifier

  `amount`           DECIMAL(12,2)                  No                     Invoice
                                                                           amount

  `due_date`         DATE                          Yes INDEX optional      Payment due
                                                                           date

  `status`           VARCHAR(30)                    No DEFAULT `pending`   Proposed:
                                                                           `pending`,
                                                                           `partial`,
                                                                           `paid`,
                                                                           `overdue`

  `issued_at`        DATE                          Yes                     Issue date

  `created_at`       TIMESTAMP                     Yes                     

  `updated_at`       TIMESTAMP                     Yes                     
  --------------------------------------------------------------------------------------

**Relationships** - Belongs to one `enrollment`. - Has many `payments`.

**Financial calculation** - Total paid = sum of valid payments for the
invoice. - Due amount = invoice amount − total valid payments. - Avoid
storing duplicate calculated totals unless a deliberate performance
strategy is approved. - Invoice status must remain consistent with
payment totals and due date. - Invoices are protected financial records
and must not be generically force-deleted.

------------------------------------------------------------------------

## 3.12 `payments`

**Purpose:** Record partial or full payment against an invoice.

  ------------------------------------------------------------------------------------------------------------
  Column                  MySQL Type                   Null Key / Constraint     Notes
  ----------------------- --------------- ----------------- -------------------- -----------------------------
  `id`                    BIGINT UNSIGNED                No PK                   

  `fee_invoice_id`        BIGINT UNSIGNED                No FK →                 Invoice
                                                            `fee_invoices.id`,   
                                                            INDEX                

  `received_by_user_id`   BIGINT UNSIGNED               Yes FK → `users.id`      Accountant/user recording
                                                                                 payment

  `amount`                DECIMAL(12,2)                  No                      Payment amount

  `method`                VARCHAR(30)                    No                      `cash`, `bank`,
                                                                                 `mobile_financial_service`,
                                                                                 `other`

  `reference`             VARCHAR(255)                  Yes                      Transaction/reference
                                                                                 information

  `paid_at`               DATETIME                       No INDEX                Payment time

  `created_at`            TIMESTAMP                     Yes                      

  `updated_at`            TIMESTAMP                     Yes                      
  ------------------------------------------------------------------------------------------------------------

**Relationships** - Belongs to one `fee_invoice`. - May be recorded by
one `user`.

**Integrity and retention** - Validate payment amount against the
remaining invoice balance. - Use database transactions when recording a
payment and updating invoice state. - Do not add generic soft-delete or
force-delete behavior. - Use a correction, void, or reversal workflow if
later approved. - Use `nullOnDelete()` for `received_by_user_id` if the
user account can be removed while preserving payment history.

------------------------------------------------------------------------

## 3.13 `announcements`

**Purpose:** Notices and announcements published by authorized users.

  ------------------------------------------------------------------------------------
  Column                 MySQL Type                  Null Key /         Notes
                                                          Constraint    
  ---------------------- -------------- ----------------- ------------- --------------
  `id`                   BIGINT                        No PK            
                         UNSIGNED                                       

  `created_by_user_id`   BIGINT                        No FK →          Creator
                         UNSIGNED                         `users.id`,   
                                                          INDEX         

  `title`                VARCHAR(255)                  No               

  `body`                 LONGTEXT                      No               Announcement
                                                                        content

  `status`               VARCHAR(30)                   No DEFAULT       Proposed:
                                                          `draft`       `draft`,
                                                                        `published`,
                                                                        `archived`

  `published_at`         DATETIME                     Yes INDEX with    Publication
                                                          `status`      time

  `created_at`           TIMESTAMP                    Yes               

  `updated_at`           TIMESTAMP                    Yes               

  `deleted_at`           TIMESTAMP                    Yes Optional      Soft deletion
                                                                        if approved
  ------------------------------------------------------------------------------------

**Relationships** - Belongs to the creating `user`. - Has many
`announcement_targets`. - May own attachments through Spatie Media
Library.

**Delete rule:** If announcement soft deletion is enabled, its target
rows and media must be handled consistently. Do not permanently delete
an announcement if retention or audit requirements prohibit it.

------------------------------------------------------------------------

## 3.14 `announcement_targets`

**Purpose:** Proposed audience mapping for an announcement.

The PRD requires audience selection but does not define the exact
database structure. This table is a proposed design that supports
multiple targets.

  ----------------------------------------------------------------------------------------
  Column              MySQL Type                  Null Key / Constraint     Notes
  ------------------- -------------- ----------------- -------------------- --------------
  `id`                BIGINT                        No PK                   
                      UNSIGNED                                              

  `announcement_id`   BIGINT                        No FK →                 Parent
                      UNSIGNED                         `announcements.id`   announcement

  `target_type`       VARCHAR(100)                  No                      Polymorphic
                                                                            target model
                                                                            type

  `target_id`         BIGINT                        No                      Target model
                      UNSIGNED                                              identifier

  `created_at`        TIMESTAMP                    Yes                      
  ----------------------------------------------------------------------------------------

**Recommended indexes** - INDEX(`announcement_id`) -
INDEX(`target_type`, `target_id`) - Optional UNIQUE(`announcement_id`,
`target_type`, `target_id`) to prevent duplicate audience targets.

**Important:** `target_type` + `target_id` is a polymorphic reference.
It cannot have a conventional foreign key to every possible target
table. Define the allowed target types in application logic. Potential
targets include a Spatie role or a batch.

If the final product needs only one audience per announcement, this
table can be simplified. Confirm before migration.

------------------------------------------------------------------------

## 3.15 `learning_materials`

**Purpose:** Store metadata and access scope for class notes, PDFs, and
learning resources.

  --------------------------------------------------------------------------------------
  Column                  MySQL Type                  Null Key /           Notes
                                                           Constraint      
  ----------------------- -------------- ----------------- --------------- -------------
  `id`                    BIGINT                        No PK              
                          UNSIGNED                                         

  `batch_id`              BIGINT                       Yes FK →            Optional
                          UNSIGNED                         `batches.id`,   batch scope
                                                           INDEX           

  `uploaded_by_user_id`   BIGINT                        No FK →            Uploader
                          UNSIGNED                         `users.id`,     
                                                           INDEX           

  `title`                 VARCHAR(255)                  No                 

  `description`           TEXT                         Yes                 

  `visibility`            VARCHAR(30)                   No                 Scope to be
                                                                           finalized

  `created_at`            TIMESTAMP                    Yes                 

  `updated_at`            TIMESTAMP                    Yes                 

  `deleted_at`            TIMESTAMP                    Yes Optional        If approved
                                                                           for soft
                                                                           deletion
  --------------------------------------------------------------------------------------

**Relationships** - Belongs to the uploading `user`. - May belong to a
`batch`. - Owns file media through Spatie Media Library. -
Enrolled-student access is checked through the batch's enrolments and
backend authorization.

------------------------------------------------------------------------

## 3.16 `exams` --- Advanced Planned Module

**Purpose:** Examination scheduled for a batch.

  -------------------------------------------------------------------------------
  Column          MySQL Type                  Null Key /           Notes
                                                   Constraint      
  --------------- -------------- ----------------- --------------- --------------
  `id`            BIGINT                        No PK              
                  UNSIGNED                                         

  `batch_id`      BIGINT                        No FK →            Related batch
                  UNSIGNED                         `batches.id`,   
                                                   INDEX           

  `title`         VARCHAR(255)                  No                 Exam name

  `exam_date`     DATE                          No INDEX with      
                                                   `batch_id`      

  `total_marks`   DECIMAL(8,2)                  No                 Maximum marks

  `status`        VARCHAR(30)                   No DEFAULT `draft` Proposed:
                                                                   `draft`,
                                                                   `published`,
                                                                   `completed`

  `created_at`    TIMESTAMP                    Yes                 

  `updated_at`    TIMESTAMP                    Yes                 
  -------------------------------------------------------------------------------

**Relationships** - Belongs to one `batch`. - Has many `exam_results`.

The module is planned after the core academic workflow is stable.

------------------------------------------------------------------------

## 3.17 `exam_results` --- Advanced Planned Module

**Purpose:** Store a student's result for an exam.

  ------------------------------------------------------------------------------------
  Column             MySQL Type                  Null Key / Constraint   Notes
  ------------------ -------------- ----------------- ------------------ -------------
  `id`               BIGINT                        No PK                 
                     UNSIGNED                                            

  `exam_id`          BIGINT                        No FK → `exams.id`    Exam
                     UNSIGNED                                            

  `enrollment_id`    BIGINT                        No FK →               Student's
                     UNSIGNED                         `enrollments.id`   enrolment

  `marks_obtained`   DECIMAL(8,2)                  No                    Obtained
                                                                         marks

  `percentage`       DECIMAL(6,2)                 Yes                    May be
                                                                         calculated

  `grade`            VARCHAR(20)                  Yes                    Based on
                                                                         grading rules

  `published_at`     DATETIME                     Yes INDEX optional     Result
                                                                         publication

  `created_at`       TIMESTAMP                    Yes                    

  `updated_at`       TIMESTAMP                    Yes                    
  ------------------------------------------------------------------------------------

**Required constraint** - `UNIQUE(exam_id, enrollment_id)` for one
result per enrolment per exam.

If retakes or multiple attempts are required, introduce an attempt model
or attempt number before migration. Published results are protected
academic history.

------------------------------------------------------------------------

## 4. Spatie Package-Managed Tables

## 4.1 Spatie Laravel Permission

The package manages the following tables. Their exact columns, indexes,
morph key types, and optional configuration depend on the installed
compatible package version.

  Table                     Purpose
  ------------------------- -------------------------------------------
  `roles`                   Role definitions
  `permissions`             Permission definitions
  `model_has_roles`         Polymorphic model-to-role assignments
  `model_has_permissions`   Polymorphic direct permission assignments
  `role_has_permissions`    Role-to-permission pivot

**Rules** - Use the official package migrations. - Configure the correct
guard for the Laravel authentication model. - Do not add a manual
`role_id` or `role` field to `users`. - The initial authenticated model
is `App\Models\User`. - Do not manually alter package tables without a
documented compatibility reason.

## 4.2 Spatie Laravel Media Library

The package manages the `media` table and its schema.

Media is associated with owning models through a polymorphic
relationship, typically using: - `model_type` - `model_id` -
`collection_name` - Package-managed file metadata and disk information.

Potential media owners: - Users. - Student profiles. - Teacher
profiles. - Categories. - Sub-categories. - Courses. - Announcements. -
Learning materials.

**Rules** - Use the compatible package migration. - Do not create a
separate image foreign key on each domain table. - Use named collections
for avatar, category image, sub-category image, course cover,
announcement attachments, learning materials, and student documents. -
Public course images and private student documents must have different
access policies. - Private student documents must be served through
authorized backend access.

------------------------------------------------------------------------

## 5. Foreign Key and Delete Behavior

The following are recommended defaults. Review them against the final
migration and retention policy.

  ------------------------------------------------------------------------------------------------------
  Child Table / FK                           Parent                  Recommended       Reason
                                                                     On-Delete         
                                                                     Behavior          
  ------------------------------------------ ----------------------- ----------------- -----------------
  `student_profiles.user_id`                 `users.id`              SET NULL          Preserve profile
                                                                                       if login account
                                                                                       is removed

  `teacher_profiles.user_id`                 `users.id`              SET NULL          Preserve profile
                                                                                       if login account
                                                                                       is removed

  `sub_categories.category_id`               `categories.id`         RESTRICT          Prevent deleting
                                                                                       a category with
                                                                                       children

  `courses.sub_category_id`                  `sub_categories.id`     RESTRICT          Prevent orphan
                                                                                       courses

  `batches.course_id`                        `courses.id`            RESTRICT          Preserve
                                                                                       course/batch
                                                                                       history

  `batches.teacher_profile_id`               `teacher_profiles.id`   RESTRICT          Require
                                                                     initially         reassignment
                                                                                       before permanent
                                                                                       teacher deletion

  `enrollments.student_profile_id`           `student_profiles.id`   RESTRICT          Preserve
                                                                                       enrolment history

  `enrollments.batch_id`                     `batches.id`            RESTRICT          Preserve batch
                                                                                       history

  `class_sessions.batch_id`                  `batches.id`            RESTRICT          Preserve class
                                                                                       schedule history

  `attendance_records.class_session_id`      `class_sessions.id`     RESTRICT          Preserve
                                                                                       attendance
                                                                                       history

  `attendance_records.enrollment_id`         `enrollments.id`        RESTRICT          Preserve student
                                                                                       attendance
                                                                                       history

  `attendance_records.marked_by_user_id`     `users.id`              SET NULL          Preserve
                                                                                       attendance if
                                                                                       marker account is
                                                                                       removed

  `fee_invoices.enrollment_id`               `enrollments.id`        RESTRICT          Preserve invoice
                                                                                       history

  `payments.fee_invoice_id`                  `fee_invoices.id`       RESTRICT          Preserve
                                                                                       financial history

  `payments.received_by_user_id`             `users.id`              SET NULL          Preserve payment
                                                                                       if recorder
                                                                                       account is
                                                                                       removed

  `announcements.created_by_user_id`         `users.id`              RESTRICT          Preserve
                                                                                       announcement
                                                                                       creator reference

  `announcement_targets.announcement_id`     `announcements.id`      CASCADE only on   Child audience
                                                                     approved hard     rows have no
                                                                     delete            independent
                                                                                       history

  `learning_materials.batch_id`              `batches.id`            RESTRICT          Preserve material
                                                                                       scope

  `learning_materials.uploaded_by_user_id`   `users.id`              RESTRICT          Preserve uploader
                                                                                       reference

  `exams.batch_id`                           `batches.id`            RESTRICT          Preserve exam
                                                                                       history

  `exam_results.exam_id`                     `exams.id`              RESTRICT          Preserve
                                                                                       published results

  `exam_results.enrollment_id`               `enrollments.id`        RESTRICT          Preserve student
                                                                                       result history
  ------------------------------------------------------------------------------------------------------

**Important:** Soft deletion does not execute a physical foreign-key
delete action. Before `forceDelete()`, the backend must check dependent
records. Never cascade-force-delete financial or academic history.

------------------------------------------------------------------------

## 6. Index and Unique Constraint Summary

  ------------------------------------------------------------------------------
  Table                    Index / Constraint            Purpose
  ------------------------ ----------------------------- -----------------------
  `users`                  UNIQUE(`email`)               Unique login email

  `student_profiles`       UNIQUE(`student_code`)        Unique student code

  `student_profiles`       UNIQUE(`user_id`)             Optional one-to-one
                                                         login link

  `teacher_profiles`       UNIQUE(`teacher_code`)        Unique teacher code

  `teacher_profiles`       UNIQUE(`user_id`)             Optional one-to-one
                                                         login link

  `categories`             UNIQUE(`slug`)                Category URL identifier

  `sub_categories`         INDEX(`category_id`)          Parent filtering

  `sub_categories`         UNIQUE(`category_id`, `slug`) Pending slug policy
                           or UNIQUE(`slug`)             

  `courses`                INDEX(`sub_category_id`)      Parent filtering

  `courses`                UNIQUE(`code`)                Unique course code

  `batches`                INDEX(`course_id`)            Course batches

  `batches`                INDEX(`teacher_profile_id`)   Teacher batch lookup

  `batches`                UNIQUE(`code`)                Unique batch code

  `enrollments`            INDEX(`student_profile_id`)   Student enrolment
                                                         history

  `enrollments`            INDEX(`batch_id`)             Batch enrolment list

  `enrollments`            Active duplicate prevention   Exact strategy pending
                                                         re-enrolment decision

  `class_sessions`         INDEX(`batch_id`,             Schedule queries
                           `session_date`)               

  `attendance_records`     UNIQUE(`class_session_id`,    Prevent duplicate
                           `enrollment_id`)              attendance

  `attendance_records`     INDEX(`enrollment_id`)        Student attendance
                                                         history

  `fee_invoices`           UNIQUE(`invoice_number`)      Invoice lookup

  `fee_invoices`           INDEX(`enrollment_id`)        Enrolment invoices

  `payments`               INDEX(`fee_invoice_id`)       Invoice payment history

  `payments`               INDEX(`paid_at`)              Collection reports

  `exams`                  INDEX(`batch_id`,             Batch exam listing
                           `exam_date`)                  

  `exam_results`           UNIQUE(`exam_id`,             One result per
                           `enrollment_id`)              enrolment/exam

  `announcements`          INDEX(`status`,               Published announcement
                           `published_at`)               listing

  `announcement_targets`   INDEX(`announcement_id`)      Audience lookup

  `announcement_targets`   INDEX(`target_type`,          Polymorphic target
                           `target_id`)                  lookup

  `announcement_targets`   UNIQUE(`announcement_id`,     Avoid duplicate target
                           `target_type`, `target_id`)   mapping

  `learning_materials`     INDEX(`batch_id`)             Batch material lookup
  ------------------------------------------------------------------------------

Unique constraints involving soft-deleted records must be designed
carefully. MySQL unique-index behavior and the chosen
reactivation/history rules must be considered before finalizing the
enrolment and slug constraints.

------------------------------------------------------------------------

## 7. Status Values --- Proposed Starting Set

These are suggested application-level values based on the ERD and PRD.
Final values should be confirmed before implementation.

  Entity                             Proposed Values
  ---------------------------------- -----------------------------------------------------
  User                               `active`, `inactive`
  Student Profile                    `active`, `inactive`
  Teacher Profile                    `active`, `inactive`
  Category / Sub-category / Course   `active`, `inactive`
  Batch                              `upcoming`, `running`, `completed`, `cancelled`
  Enrolment                          `active`, `completed`, `withdrawn`
  Class Session                      `scheduled`, `completed`, `cancelled`
  Attendance                         `present`, `absent`, `late`, `excused`
  Fee Invoice                        `pending`, `partial`, `paid`, `overdue`
  Payment Method                     `cash`, `bank`, `mobile_financial_service`, `other`
  Announcement                       `draft`, `published`, `archived`
  Exam                               `draft`, `published`, `completed`

Payment validity/reversal status, announcement audience types, and exam
grading rules remain open decisions.

------------------------------------------------------------------------

## 8. Laravel Migration Dependency Order

Create migrations in dependency order so referenced tables exist before
foreign keys are added.

1.  `users` --- Laravel authentication table.
2.  `categories`.
3.  `sub_categories`.
4.  `courses`.
5.  `student_profiles`.
6.  `teacher_profiles`.
7.  `batches`.
8.  `enrollments`.
9.  `class_sessions`.
10. `attendance_records`.
11. `fee_invoices`.
12. `payments`.
13. `announcements`.
14. `announcement_targets`.
15. `learning_materials`.
16. `exams` --- when the advanced module is scheduled.
17. `exam_results` --- after `exams` and `enrollments`.
18. Spatie Laravel Permission migrations --- generated by the installed
    package.
19. Spatie Media Library migration --- generated by the installed
    package.

The exact migration ordering for package tables may be adjusted
according to the package-generated migrations and installed versions.
Framework infrastructure tables such as sessions, cache, password reset
tokens, and queue jobs depend on Laravel configuration and are not
CoachDesk domain tables.

------------------------------------------------------------------------

## 9. Eloquent Model Relationship Map

  -----------------------------------------------------------------------
  Model                               Relationship Methods to Define
  ----------------------------------- -----------------------------------
  `User`                              `studentProfile()`,
                                      `teacherProfile()`,
                                      `announcements()`,
                                      `learningMaterials()`; Spatie
                                      `HasRoles`

  `StudentProfile`                    `user()`, `enrollments()`; Media
                                      Library support

  `TeacherProfile`                    `user()`, `batches()`; Media
                                      Library support

  `Category`                          `subCategories()`; Media Library
                                      support

  `SubCategory`                       `category()`, `courses()`; Media
                                      Library support

  `Course`                            `subCategory()`, `batches()`; Media
                                      Library support

  `Batch`                             `course()`, `teacher()`,
                                      `enrollments()`, `classSessions()`,
                                      `exams()`, `learningMaterials()`

  `Enrollment`                        `student()`, `batch()`,
                                      `attendanceRecords()`,
                                      `feeInvoices()`, `examResults()`

  `ClassSession`                      `batch()`, `attendanceRecords()`

  `AttendanceRecord`                  `classSession()`, `enrollment()`,
                                      `markedBy()`

  `FeeInvoice`                        `enrollment()`, `payments()`

  `Payment`                           `feeInvoice()`, `receivedBy()`

  `Announcement`                      `createdBy()`, `targets()`; Media
                                      Library support

  `AnnouncementTarget`                `announcement()`, polymorphic
                                      `target()` if implemented with
                                      morph relations

  `LearningMaterial`                  `batch()`, `uploadedBy()`; Media
                                      Library support

  `Exam`                              `batch()`, `results()`

  `ExamResult`                        `exam()`, `enrollment()`
  -----------------------------------------------------------------------

These are proposed relationship method names. Use Laravel conventions
and the final table/foreign-key names when implementing models.

------------------------------------------------------------------------

## 10. Database Decisions Required Before Migrations

The source documents intentionally leave several decisions open. Confirm
these before creating final migrations:

  -------------------------------------------------------------------------
  Decision                Current Proposed          Must Confirm
                          Direction                 
  ----------------------- ------------------------- -----------------------
  Student/teacher login   Optional unique `user_id` Must every
                                                    student/teacher have
                                                    login access?

  Teacher assignment      One primary teacher per   Can a batch have
                          batch                     multiple teachers?

  Re-enrolment            Keep enrolment history    Reactivate existing
                          and status                record or create a new
                                                    record?

  Enrolment soft delete   Not enabled by default    Is withdrawal status
                                                    sufficient?

  Sub-category slug       Parent-scoped unique is a Global or within
                          possible choice           category?

  Invoice structure       One enrolment to many     How are fee
                          invoices                  installments
                                                    represented?

  Invoice balance         Calculate from valid      Is a cached total
                          payments                  required?

  Announcement audience   Proposed                  Which target types are
                          `announcement_targets`    required?
                          polymorphic table         

  Learning material       Optional batch scope      Is global or role-wide
  visibility                                        visibility required?

  Batch schedule          `class_days` +            Can a batch have
                          `class_time` and dated    multiple weekly time
                          `class_sessions`          slots?

  User deletion           Deactivation preferred    Is user soft deletion
                          for routine account       required?
                          closure                   

  Content soft deletion   Optional for              Which content should be
                          announcements/materials   recoverable?

  Exam attempts           One result per            Are retakes/multiple
                          exam/enrolment            attempts required?

  Grade rules             Not finalized             What grading scale is
                                                    used?

  Media conversions       Only required conversions Which image sizes are
                                                    needed?
  -------------------------------------------------------------------------

------------------------------------------------------------------------

## 11. Pre-Migration Checklist

-   [ ] Review this schema against `ERD.md`.
-   [ ] Confirm optional student and teacher login behavior.
-   [ ] Confirm one or multiple teachers per batch.
-   [ ] Decide enrolment reactivation/history behavior.
-   [ ] Decide sub-category slug uniqueness.
-   [ ] Finalize invoice and partial-payment rules.
-   [ ] Finalize announcement audience mapping.
-   [ ] Finalize learning-material visibility.
-   [ ] Confirm soft-delete eligibility per model.
-   [ ] Define force-delete dependency checks.
-   [ ] Confirm exam attempt and grading rules.
-   [ ] Verify Laravel, PHP, and MySQL versions.
-   [ ] Verify Spatie package compatibility.
-   [ ] Generate package-owned migrations from the installed package
    versions.
-   [ ] Finalize column types, nullability, foreign keys, indexes, and
    unique constraints.
-   [ ] Review migration dependency order.
-   [ ] Only then begin writing migrations.

------------------------------------------------------------------------

## 12. Source Alignment

This schema follows: - `project-overview.md` for product scope, modules,
roles, technology, architecture, package choices, and operational
rules. - `PRD.md` for functional requirements, access control, SPA
behavior, data integrity, deletion safeguards, and acceptance
criteria. - `ERD.md` for entities, cardinalities, proposed columns, core
relationships, package relationships, and open database decisions.

Where the ERD marks a field or relationship as proposed or unresolved,
this document preserves that status. It does not imply that a migration
has been created or that an open decision has been approved.

**Database principle:** Build a relational schema that supports
CoachDesk's required workflows, preserves financial and academic
history, and can be implemented incrementally with Laravel migrations.
