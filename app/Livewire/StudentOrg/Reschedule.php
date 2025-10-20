<?php

namespace App\Livewire\StudentOrg;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Layout;
use Livewire\WithFileUploads;

class Reschedule extends Component
{
    use WithFileUploads;

    #[Title('Reschedule Request - Student Organization')]
    #[Layout('components.layouts.student-org-layout')]

    public $selectedEventId = '';
    public $changeDate = false;
    public $changeTime = false;
    public $changeVenue = false;
    public $newStartDateTime = '';
    public $newEndDateTime = '';
    public $newStartTime = '';
    public $newEndTime = '';
    public $newVenue = '';
    public $alternativeVenue = '';
    public $rescheduleReason = '';
    public $detailedReason = '';
    public $impactAssessment = '';
    public $supportingDocuments = [];
    public $contactPerson = '';
    public $contactEmail = '';
    public $contactPhone = '';
    public $preferredContact = '';
    public $urgencyLevel = 'normal';
    public $urgencyJustification = '';
    public $agreeToTerms = false;

    public function submitReschedule()
    {
        $this->validate([
            'selectedEventId' => 'required',
            'rescheduleReason' => 'required',
            'detailedReason' => 'required',
            'contactPerson' => 'required',
            'contactEmail' => 'required|email',
            'contactPhone' => 'required',
            'preferredContact' => 'required',
            'agreeToTerms' => 'accepted',
        ]);

        // Implement reschedule logic
        session()->flash('success', 'Reschedule request submitted successfully!');
    }

    public function saveDraft()
    {
        // Implement save draft logic
        session()->flash('info', 'Draft saved successfully!');
    }

    public function render()
    {
        return view('livewire.student-org.reschedule');
    }
}
