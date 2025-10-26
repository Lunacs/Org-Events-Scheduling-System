# Database Seeding Guide

## Overview

This guide explains how to use the enhanced database factories and seeders to create realistic event scheduling data for testing and development.

## What's New

### Database Structure Updates

1. **Student Organizations Table** - Added `status` column

    - Values: `active`, `inactive`, `suspended`
    - Default: `active`

2. **Courses Table** - Added `department` column
    - Stores the department/college that the course belongs to

## Factories Available

All factories have been updated with realistic data generators:

### 1. CourseFactory

Creates realistic academic courses with:

-   Course codes (BSCS, BSIT, BSCpE, etc.)
-   Course names (Bachelor of Science in Computer Science, etc.)
-   Departments (College of Engineering, College of IT, etc.)

### 2. StudentOrganizationFactory

Generates student organizations with:

-   Unique organization codes (ORG-####)
-   Organization names (Computer Society, Engineering Society, etc.)
-   Associated courses
-   Adviser names
-   Status (75% active, 25% inactive)

**State Methods:**

-   `active()` - Set organization as active
-   `inactive()` - Set organization as inactive
-   `suspended()` - Set organization as suspended

### 3. EventTypeFactory

Creates event types like:

-   Academic Conference
-   Cultural Event
-   Sports Competition
-   Community Service
-   Workshop
-   Fundraising
-   Social Gathering
-   Competition

### 4. OfficeFactory

Generates university offices:

-   GSO (General Services Office)
-   VPAA (Vice President for Academic Affairs)
-   VPSA (Vice President for Student Affairs)
-   Finance Office
-   Security Office
-   Registrar Office

### 5. TicketFactory

Creates event request tickets with:

-   Unique ticket numbers (TKT-YYYY-####)
-   Event titles and descriptions
-   Requested venues
-   Requested dates (1 week to 3 months in the future)
-   Various statuses (pending, approved, rejected)

**State Methods:**

-   `pending()` - Set ticket as pending
-   `approved()` - Set ticket as approved
-   `rejected()` - Set ticket as rejected

### 6. EventFactory

Creates events linked to approved tickets with:

-   Event notes and special instructions

### 7. EventSchedulesFactory

Generates event schedules with:

-   Schedule dates and times
-   Venue assignments
-   Status (pending, approved, rejected)
-   Remarks and notes

**State Methods:**

-   `pending()` - Set schedule as pending
-   `approved()` - Set schedule as approved
-   `rejected()` - Set schedule as rejected

### 8. OSAApprovalFactory

Creates OSA approval records with:

-   Decision types (approved, rejected, pending, need_revision)
-   Realistic approval remarks

**State Methods:**

-   `approved()` - Set as approved
-   `rejected()` - Set as rejected
-   `pending()` - Set as pending
-   `needRevision()` - Set as needs revision

### 9. OfficeApprovalFactory

Generates office approval records with:

-   Decision types (approved, rejected, pending, conditional)
-   Office-specific remarks

**State Methods:**

-   `approved()` - Set as approved
-   `rejected()` - Set as rejected
-   `pending()` - Set as pending
-   `conditional()` - Set as conditional approval

## Seeders

The following seeders are available and run in order:

1. **CourseSeeder** - Creates 10 specific courses across different departments
2. **StudentOrganizationSeeder** - Creates 10 student organizations (9 active, 1 inactive)
3. **OfficeSeeder** - Creates 6 university offices
4. **UserSeeder** - Creates admin and student organization users
5. **EventTypeSeeder** - Creates 8 event types
6. **TicketSeeder** - Creates 10 realistic event tickets with various statuses
7. **EventSeeder** - Creates events for approved tickets
8. **EventSchedulesSeeder** - Creates schedules for all events
9. **OSAApprovalSeeder** - Creates OSA approvals for all tickets
10. **OfficeApprovalSeeder** - Creates office approvals for approved tickets

## How to Use

### Fresh Database with Seed Data

To reset the database and seed with realistic data:

```bash
php artisan migrate:fresh --seed
```

### Seed Existing Database

To add seed data to an existing database:

```bash
php artisan db:seed
```

### Seed Specific Seeder

To run a specific seeder:

```bash
php artisan db:seed --class=StudentOrganizationSeeder
```

## Using Factories in Tinker or Tests

### Create Single Records

```php
// Create a single active student organization
$org = \App\Models\Student_Organization::factory()->active()->create();

// Create a pending ticket
$ticket = \App\Models\Ticket::factory()->pending()->create();

// Create a completed event schedule
$schedule = \App\Models\Event_Schedule::factory()->completed()->create();
```

### Create Multiple Records

```php
// Create 5 active student organizations
\App\Models\Student_Organization::factory(5)->active()->create();

// Create 10 tickets with random statuses
\App\Models\Ticket::factory(10)->create();

// Create 3 approved tickets
\App\Models\Ticket::factory(3)->approved()->create();
```

### Create Related Records

```php
// Create an organization with users
$org = \App\Models\Student_Organization::factory()
    ->has(\App\Models\User::factory(3)->state(['role' => 'student_org']), 'users')
    ->create();

// Create a ticket with event
$ticket = \App\Models\Ticket::factory()
    ->approved()
    ->has(\App\Models\Event::factory())
    ->create();
```

## Default User Credentials

After seeding, you can login with these credentials:

-   **SuperAdmin**: `superadmin@plv.edu.ph` / `password`
-   **OSA Admin**: `osa@plv.edu.ph` / `password`
-   **GSO Admin**: `gso@plv.edu.ph` / `password`
-   **Student Org**: `student@plv.edu.ph` / `password`

## Sample Data Generated

After a full seed, you will have:

-   10 Courses across 6 departments
-   10 Student Organizations
-   6 University Offices
-   Multiple Users (SuperAdmin, OSA, GSO, Student Org members)
-   8 Event Types
-   10 Event Tickets (various statuses)
-   Events for approved tickets
-   Event Schedules for all events
-   OSA Approvals for all tickets
-   Office Approvals for approved tickets

## Tips for Testing

1. **Test Different Workflows**: Use different ticket statuses to test approval workflows
2. **Test Calendar Views**: Event schedules span 1 week to 3 months
3. **Test Organization Management**: Mix of active and inactive organizations
4. **Test Multi-office Approvals**: Approved tickets have approvals from multiple offices

## Customizing Seed Data

To customize the seed data:

1. Edit the seeders in `database/seeders/`
2. Modify the factory definitions in `database/factories/`
3. Run `php artisan db:seed` to apply changes

## Troubleshooting

### "No tickets found" warning

This means you need to run the earlier seeders first. Run seeders in order or use `php artisan migrate:fresh --seed`.

### Duplicate entry errors

This usually happens when running seeders multiple times. Use `php artisan migrate:fresh --seed` to reset.

### Foreign key constraint errors

Ensure seeders run in the correct order (dependencies first). The default order in DatabaseSeeder is correct.
