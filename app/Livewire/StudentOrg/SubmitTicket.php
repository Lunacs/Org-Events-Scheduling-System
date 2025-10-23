<?php

namespace App\Livewire\StudentOrg;

use App\Models\Ticket;
use Livewire\Attributes\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;

class SubmitTicket extends Component
{
    #[Title('Submit Ticket - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]
    public $search = '';
    public $statusFilter = '';
    public $dateFilter = '';
    public $organizationName = '';

    #[Validate('required|string')]
    public $adviser = '';
    #[Validate('required|string|email')]
    public $contactEmail = '';
    #[Validate('required|string')]
    public $proponentName = '';
    public $proponentPosition = '';
    #[Validate('required|string')]
    public $organizationCourse = '';
    #[Validate('required|integer|min:1')]
    public $expectedPLVParticipants = 0;
    #[Validate('required|integer|min:1')]
    public $expectedNonPLVParticipants = 0;
    #[Validate('required|string|max:255')]
    public $eventTitle = '';
    #[Validate('required|string|max:2000')]
    public $eventDescription = '';
    #[Validate('required|string|max:255')]
    public $preferredVenue = '';
    #[Validate('nullable|string|max:255')]
    public $alternativeVenue = '';
    #[Validate('nullable|string|max:2000')]
    public $specialRequirements = '';


    public function mount()
    {
        $currentUser = auth()->user();
        $currentUserinfo = $currentUser->studentOrganization;
        $currentUserPosition = $currentUser->position;

        $this->organizationName = $currentUserinfo->org_code ?? '';
        $this->adviser = $currentUserinfo->adviser_name ?? '';
        $this->contactEmail = $currentUser->email ?? '';
        $this->proponentName = $currentUser->name ?? '';
        $this->organizationCourse = $currentUserinfo->course->course_name ?? '';
        $this->proponentPosition = $currentUserPosition->position_name ?? '';
    }

    public function save()
    {
        $currentUser = auth()->user();
        $currentUserinfo = $currentUser->studentOrganization;
        $ticketCode = "{$currentUserinfo->org_code}-" . (Ticket::count() + 1);
        $this->validate();


        Ticket::create([
            'user_id' => $currentUser->user_id,
            'ticket_number' => $ticketCode,
            'event_type_id' => 1, //test
            'plv_participants' => $this->expectedPLVParticipants,
            'external_participants' => $this->expectedNonPLVParticipants,
            'title' => $this->eventTitle,
            'description' => $this->eventDescription,
            'venue_requested' => $this->preferredVenue,
            'alternate_venue' => $this->alternativeVenue,
            'special_requirements' => $this->specialRequirements,
            'total_participants' => $this->expectedPLVParticipants + $this->expectedNonPLVParticipants,
            // 'sponsoring_body' => $this->organizationName,
            // Default or placeholder values for dates
            'date-from' => now()->toDateString(),
            'date-to' => now()->toDateString(),
            'time-from' => now()->toTimeString(),
            'time-to' => now()->toTimeString(),
            'status' => 'Pending',
        ]);

        session()->flash('message', 'Ticket submitted successfully!');
    }

    public function getExpectedParticipantsProperty()
    {
        return (int)$this->expectedPLVParticipants + (int)$this->expectedNonPLVParticipants;
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->statusFilter = '';
        $this->dateFilter = '';
    }

    public function render()
    {
        return view('livewire.student-org.submit-ticket');
    }
}
