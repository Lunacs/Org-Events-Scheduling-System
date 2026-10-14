<?php

use App\Livewire\Superadmin\AdminTools\Index as AdminTools;
use App\Livewire\Superadmin\Archive\Index as Archive;
use App\Livewire\Superadmin\SystemSettings\CourseManager;
use App\Livewire\Superadmin\SystemSettings\EventTypeManager;
use App\Livewire\Superadmin\SystemSettings\VenueManager;
use App\Livewire\Superadmin\Tickets\Index as TicketIndex;
use App\Livewire\Superadmin\Users\Index as UsersIndex;
use App\Models\ContentSection;
use App\Models\Course;
use App\Models\Event;
use App\Models\Event_Type;
use App\Models\Permission;
use App\Models\Roles;
use App\Models\Student_Organization;
use App\Models\Ticket;
use App\Models\Transaction_Logs;
use App\Models\User;
use App\Models\Venue;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function (): void {
    (new AppServiceProvider($this->app))->boot();
    Notification::fake();

    $this->superadmin = User::query()->where('email', 'superadmin@plv.edu.ph')->sole();
    $this->student = User::query()->where('email', 'student@plv.edu.ph')->sole();
});

it('refuses to delete an event type that tickets still use', function (): void {
    $type = Event_Type::query()->first();
    $ticket = Ticket::factory()->create(['user_id' => $this->student->user_id, 'event_type_id' => $type->event_type_id]);
    $ticket->delete();

    Livewire::actingAs($this->superadmin)
        ->test(EventTypeManager::class)
        ->call('openDeleteModal', $type->event_type_id)
        ->call('confirmDelete');

    expect(Event_Type::query()->whereKey($type->event_type_id)->exists())->toBeTrue()
        ->and(Ticket::withTrashed()->whereKey($ticket->ticket_id)->exists())->toBeTrue();
});

it('refuses to delete a course whose organizations were soft-deleted', function (): void {
    $course = Course::query()->create(['course_code' => 'BSX', 'course_name' => 'Bachelor of Science in X', 'department' => 'Test']);
    $org = Student_Organization::query()->create(['org_code' => 'XORG', 'org_name' => 'X Org', 'course_id' => $course->course_id, 'adviser_name' => 'A. Adviser', 'status' => 'active']);
    $member = User::factory()->create(['org_id' => $org->org_id, 'role_id' => User::getRoleId('student-org')]);
    $org->delete();

    Livewire::actingAs($this->superadmin)
        ->test(CourseManager::class)
        ->call('openDeleteCourseModal', $course->course_id)
        ->call('confirmDeleteCourse');

    expect(Course::query()->whereKey($course->course_id)->exists())->toBeTrue()
        ->and(User::withTrashed()->whereKey($member->user_id)->exists())->toBeTrue();
});

it('opens the venue delete dialog without a database error', function (): void {
    $venue = Venue::query()->where('venue_name', 'PLV Auditorium')->sole();
    Ticket::factory()->create(['user_id' => $this->student->user_id, 'venue_requested' => $venue->venue_id]);

    Livewire::actingAs($this->superadmin)
        ->test(VenueManager::class)
        ->call('openDeleteModal', $venue->venue_id)
        ->assertSet('deletingVenueId', $venue->venue_id);
});

it('bulk-approves through the workflow so each ticket gets its event', function (): void {
    $awaiting = Ticket::factory()->count(2)->create(['user_id' => $this->student->user_id, 'status' => 'received']);
    $alreadyApproved = Ticket::factory()->create(['user_id' => $this->student->user_id, 'status' => 'for_revision']);

    Livewire::actingAs($this->superadmin)
        ->test(TicketIndex::class)
        ->set('selectedTickets', [...$awaiting->pluck('ticket_id')->all(), $alreadyApproved->ticket_id])
        ->set('bulkAction', 'approve')
        ->call('executeBulkAction');

    foreach ($awaiting as $ticket) {
        expect($ticket->fresh()->status)->toBe('approved')
            ->and(Event::query()->where('ticket_id', $ticket->ticket_id)->count())->toBe(1);
    }

    expect($alreadyApproved->fresh()->status)->toBe('for_revision');
});

it('lists deleted tickets in the archive without leaking live ones, and restores them', function (): void {
    $live = Ticket::factory()->create(['user_id' => $this->student->user_id, 'status' => 'approved', 'title' => 'Live summit']);
    $deleted = Ticket::factory()->create(['user_id' => $this->student->user_id, 'title' => 'Deleted summit']);
    $deleted->delete();

    $component = Livewire::actingAs($this->superadmin)->test(Archive::class)->set('search', 'summit');
    $ids = collect($component->instance()->archivedItems)->where('type', 'ticket')->pluck('id');

    expect($ids)->toContain($deleted->ticket_id)->not->toContain($live->ticket_id);

    $component->call('openRestoreModal', $deleted->ticket_id, 'ticket')->call('restoreItem');

    expect($deleted->fresh()->trashed())->toBeFalse();
});

it('keeps the audit log when there are 100 entries or fewer', function (): void {
    Transaction_Logs::query()->create(['user_id' => $this->superadmin->user_id, 'action' => 'TEST', 'details' => 'keep me']);

    Livewire::actingAs($this->superadmin)->test(AdminTools::class)->call('clearLogs');

    expect(Transaction_Logs::query()->where('action', 'TEST')->exists())->toBeTrue();
});

it('will not give a user the GSO role without an office', function (): void {
    $gsoRoleId = User::getRoleId('gso');

    Livewire::actingAs($this->superadmin)
        ->test(UsersIndex::class)
        ->set("selectedRoleIds.{$this->student->user_id}", $gsoRoleId)
        ->call('saveUserRole', $this->student->user_id);

    expect($this->student->fresh()->role_id)->toBe(User::getRoleId('student-org'));
});

it('shows OSA-targeted announcements to custom roles on the OSA portal', function (): void {
    $role = Roles::query()->create(['role_name' => 'osa-assistant', 'base_portal' => 'osa']);
    $role->permissions()->sync(Permission::query()->where('name', 'dashboard.view')->pluck('permission_id'));
    $assistant = User::factory()->create(['role_id' => $role->role_id]);

    $announcement = new ContentSection(['target_roles' => ['osa']]);

    expect($announcement->isVisibleToUser($assistant))->toBeTrue()
        ->and($announcement->isVisibleToUser($this->student))->toBeFalse();
});
