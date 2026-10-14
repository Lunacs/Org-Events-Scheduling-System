<?php

use App\Livewire\Components\EventCalendar;
use App\Livewire\Osa\TicketReview\Show;
use App\Livewire\StudentOrg\SubmitTicket;
use App\Livewire\StudentOrg\TicketDetails;
use App\Models\ApprovalHistory;
use App\Models\Event;
use App\Models\Event_Schedule;
use App\Models\Fund_Sources;
use App\Models\Office;
use App\Models\Office_Approval;
use App\Models\Ticket;
use App\Models\User;
use App\Models\Venue;
use App\Notifications\TicketForwardedToGsoNotification;
use App\Providers\AppServiceProvider;
use App\Services\TicketWorkflowService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

beforeEach(function (): void {
    (new AppServiceProvider($this->app))->boot();
    Notification::fake();

    $this->osa = User::query()->where('email', 'osa@plv.edu.ph')->sole();
    $this->gso = User::query()->where('email', 'gso@plv.edu.ph')->sole();
    $this->owner = User::query()->where('email', 'student@plv.edu.ph')->sole();
});

function workflowTicket(array $attributes = []): Ticket
{
    return Ticket::factory()->create([
        'user_id' => User::query()->where('email', 'student@plv.edu.ph')->value('user_id'),
        'status' => 'received',
        ...$attributes,
    ]);
}

it('forwards to the GSO office and notifies its staff', function (): void {
    $ticket = workflowTicket();
    $gsoOfficeId = Office::query()->where('office_code', 'GSO')->value('office_id');

    Livewire::actingAs($this->osa)
        ->test(Show::class, ['ticketNumber' => $ticket->ticket_number])
        ->set('forwardRemarks', 'Please check the venue')
        ->call('forwardToGso')
        ->assertHasNoErrors();

    expect($ticket->fresh()->status)->toBe('gso_review')
        ->and(Office_Approval::query()->where('ticket_id', $ticket->ticket_id)->pluck('office_id')->all())->toBe([$gsoOfficeId])
        ->and(ApprovalHistory::query()->where('ticket_id', $ticket->ticket_id)->where('approval_type', 'office')->value('office_id'))->toBe($gsoOfficeId);

    Notification::assertSentTo($this->gso, TicketForwardedToGsoNotification::class);
});

it('moves the existing schedule instead of creating a second event when a rescheduled ticket is approved again', function (): void {
    $ticket = workflowTicket();
    $workflow = app(TicketWorkflowService::class);

    $workflow->approve($ticket, $this->osa, 'Approved');
    $ticket->refresh();

    $newDate = now()->addMonths(2)->format('Y-m-d');
    $ticket->update(['date_from' => $newDate, 'date_to' => $newDate]);
    $workflow->recordReschedule($ticket, $this->owner, ['Date changed']);

    expect($ticket->fresh()->status)->toBe('amended')
        ->and($ticket->osaApprovals()->value('decision'))->toBe('pending');

    $workflow->approve($ticket->fresh(), $this->osa, 'New date approved');

    expect(Event::query()->where('ticket_id', $ticket->ticket_id)->count())->toBe(1)
        ->and(Event_Schedule::query()->whereHas('event', fn ($q) => $q->where('ticket_id', $ticket->ticket_id))->sole()->start_date->format('Y-m-d'))->toBe($newDate);
});

it('rejects remarks longer than the column instead of failing in the database', function (): void {
    $ticket = workflowTicket();

    Livewire::actingAs($this->osa)
        ->test(Show::class, ['ticketNumber' => $ticket->ticket_number])
        ->set('approvalRemarks', str_repeat('a', 256))
        ->call('approveTicket')
        ->assertHasErrors(['approvalRemarks' => 'max']);

    expect($ticket->fresh()->status)->toBe('received');
});

it('refuses a decision once another one has already moved the ticket', function (): void {
    $ticket = workflowTicket();
    $workflow = app(TicketWorkflowService::class);

    $workflow->forwardToGso($ticket, $this->osa, 'Forwarded first');

    expect(fn () => $workflow->forwardToGso($ticket, $this->osa, 'Forwarded twice'))->toThrow(DomainException::class)
        ->and(Event::query()->where('ticket_id', $ticket->ticket_id)->exists())->toBeFalse();
});

function revisableTicket(): Ticket
{
    return workflowTicket([
        'status' => 'for_revision',
        'title' => 'Leadership Summit',
        'description' => 'A leadership summit for all officers of the organization.',
        'adviser_contact' => '09171234567',
        'venue_requested' => Venue::query()->where('venue_name', 'PLV Auditorium')->value('venue_id'),
        'venue_other' => null,
        'alternate_venue' => null,
        'igp_requested' => false,
        'igp_details' => null,
        'oc_accommodation' => null,
        'oc_tsp' => null,
        'oc_driver_name' => null,
        'oc_transportation_type' => null,
        'oc_vehicle_plate_number' => null,
        'oc_driver_contact_number' => null,
        'fund_source_id' => Fund_Sources::query()->where('source_name', 'Organizational Funds')->value('source_id'),
    ]);
}

it('amends the same ticket when a revised ticket is resubmitted', function (): void {
    $ticket = revisableTicket();
    $ticketCount = Ticket::query()->count();

    Livewire::actingAs($this->owner)
        ->withQueryParams(['amend' => $ticket->ticket_number])
        ->test(SubmitTicket::class)
        ->assertSet('amendingTicketId', $ticket->ticket_id)
        ->assertSet('eventTitle', 'Leadership Summit')
        ->set('eventTitle', 'Leadership Summit Revised')
        ->set('agreeToTerms', true)
        ->call('save')
        ->assertHasNoErrors();

    $ticket->refresh();

    expect(Ticket::query()->count())->toBe($ticketCount)
        ->and($ticket->status)->toBe('amended')
        ->and($ticket->title)->toBe('Leadership Summit Revised')
        ->and($ticket->osaApprovals()->value('decision'))->toBe('pending');
});

it('only lets a ticket in for_revision be revised, and only by its organization', function (): void {
    $approved = workflowTicket(['status' => 'approved']);
    $revisable = revisableTicket();
    $outsider = User::query()->where('email', 'john.martinez@plv.edu.ph')->sole();

    expect(Gate::forUser($this->owner)->allows('resubmit', $approved))->toBeFalse()
        ->and(Gate::forUser($this->owner)->allows('resubmit', $revisable))->toBeTrue()
        ->and(Gate::forUser($outsider)->allows('resubmit', $revisable))->toBeFalse();
});

it('opens the revise form from the ticket page instead of a missing component', function (): void {
    $ticket = revisableTicket();

    Livewire::actingAs($this->owner)
        ->test(TicketDetails::class, ['ticketNumber' => $ticket->ticket_number])
        ->call('reviseAndResubmit')
        ->assertRedirect(route('student-org.submit-ticket', ['amend' => $ticket->ticket_number]));
});

it('keeps an approved event on the calendar while its reschedule is pending', function (): void {
    $ticket = workflowTicket();
    $workflow = app(TicketWorkflowService::class);
    $workflow->approve($ticket, $this->osa, 'Approved');
    $workflow->recordReschedule($ticket->fresh(), $this->owner, []);

    $component = new class extends EventCalendar
    {
        public function visibleScheduleIds(): array
        {
            return $this->baseCalendarSchedulesQuery()->pluck('schedule_id')->all();
        }
    };
    $component->showPastEvents = true;

    $scheduleId = Event_Schedule::query()->whereHas('event', fn ($q) => $q->where('ticket_id', $ticket->ticket_id))->value('schedule_id');

    expect($component->visibleScheduleIds())->toContain($scheduleId);
});
