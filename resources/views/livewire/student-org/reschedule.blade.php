    <div>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Reschedule Request') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Header Section --}}
            <div class="flex justify-between items-center">
                <div>
                    <h3 class="text-lg font-semibold">Request Event Reschedule</h3>
                    <p class="text-sm text-gray-600">Submit a request to change the date, time, or venue of your approved
                        event</p>
                </div>
                <x-mary-button label="My Tickets" icon="s-ticket" class="btn-secondary" link="/student-org/my-tickets"
                    wire:navigate />
            </div>

            {{-- Important Notice --}}
            <x-mary-card>
                <div class="bg-warning/10 p-4 rounded-lg border-l-4 border-warning">
                    <div class="flex items-start space-x-3">
                        <x-mary-icon name="s-exclamation-triangle" class="w-5 h-5 text-warning mt-0.5" />
                        <div class="text-sm">
                            <p class="font-medium mb-2 text-warning-content">Important Notice:</p>
                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                <li>Reschedule requests must be submitted at least 7 days before the current event date
                                </li>
                                <li>All reschedule requests are subject to approval by OSA and GSO</li>
                                <li>Venue availability will be checked for your new requested date</li>
                                <li>You will be notified via email about the status of your request</li>
                                <li>Frequent reschedule requests may affect future event approvals</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </x-mary-card>

            {{-- Select Event to Reschedule --}}
            <x-mary-card title="Select Event to Reschedule" subtitle="Choose from your approved events">
                <div class="space-y-4">
                    <x-mary-select label="Select Event" wire:model.live="selectedEventId" :options="[
                        ['id' => 'evt-001', 'name' => 'Annual Organization Meeting - Oct 15, 2025 (TKT-001)'],
                        ['id' => 'evt-002', 'name' => 'Fundraising Concert - Nov 1, 2025 (TKT-002)'],
                        ['id' => 'evt-003', 'name' => 'Workshop Series - Oct 20-22, 2025 (TKT-003)'],
                    ]"
                        placeholder="Select an event to reschedule" required />

                    {{-- Selected Event Details --}}
                    @if ($selectedEventId)
                        <div class="bg-base-200 p-4 rounded-lg">
                            <h4 class="font-semibold mb-3">Current Event Details:</h4>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-gray-600">Event Title:</p>
                                    <p class="font-medium">Annual Organization Meeting</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Current Date:</p>
                                    <p class="font-medium">October 15, 2025</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Current Time:</p>
                                    <p class="font-medium">2:00 PM - 5:00 PM</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Current Venue:</p>
                                    <p class="font-medium">Student Center Room 201</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Event Type:</p>
                                    <p class="font-medium">Meeting</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-600">Expected Participants:</p>
                                    <p class="font-medium">50 attendees</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </x-mary-card>

            {{-- Reschedule Form --}}
            @if ($selectedEventId)
                <x-mary-form wire:submit="submitReschedule">
                    {{-- Reschedule Type --}}
                    <x-mary-card title="What needs to be changed?" subtitle="Select the type of change you need">
                        <div class="space-y-3">
                            <x-mary-checkbox label="Change Date" wire:model.live="changeDate" />

                            <x-mary-checkbox label="Change Time" wire:model.live="changeTime" />

                            <x-mary-checkbox label="Change Venue" wire:model.live="changeVenue" />
                        </div>
                    </x-mary-card>

                    {{-- New Schedule Details --}}
                    <x-mary-card title="New Schedule Details" subtitle="Provide your preferred new schedule">
                        <div class="space-y-4">
                            @if ($changeDate)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-mary-datetime label="New Start Date & Time" wire:model="newStartDateTime"
                                        required />

                                    <x-mary-datetime label="New End Date & Time" wire:model="newEndDateTime" required />
                                </div>
                            @endif

                            @if ($changeTime && !$changeDate)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-mary-input label="New Start Time" type="time" wire:model="newStartTime"
                                        required />

                                    <x-mary-input label="New End Time" type="time" wire:model="newEndTime"
                                        required />
                                </div>
                            @endif

                            @if ($changeVenue)
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <x-mary-input label="New Preferred Venue" wire:model="newVenue"
                                        placeholder="e.g., University Auditorium" required />

                                    <x-mary-input label="Alternative Venue" wire:model="alternativeVenue"
                                        placeholder="Backup venue option" />
                                </div>
                            @endif
                        </div>
                    </x-mary-card>

                    {{-- Reason for Reschedule --}}
                    <x-mary-card title="Reason for Reschedule" subtitle="Please provide a detailed explanation">
                        <div class="space-y-4">
                            <x-mary-select label="Primary Reason" wire:model="rescheduleReason" :options="[
                                ['id' => 'venue_conflict', 'name' => 'Venue Conflict/Unavailability'],
                                ['id' => 'speaker_availability', 'name' => 'Speaker/Guest Availability'],
                                ['id' => 'weather_conditions', 'name' => 'Weather/Environmental Conditions'],
                                ['id' => 'academic_conflict', 'name' => 'Academic Schedule Conflict'],
                                ['id' => 'budget_constraints', 'name' => 'Budget/Financial Constraints'],
                                ['id' => 'health_safety', 'name' => 'Health and Safety Concerns'],
                                ['id' => 'low_attendance', 'name' => 'Expected Low Attendance'],
                                ['id' => 'university_event', 'name' => 'Conflict with University Event'],
                                ['id' => 'technical_issues', 'name' => 'Technical/Equipment Issues'],
                                ['id' => 'other', 'name' => 'Other (Please specify)'],
                            ]"
                                placeholder="Select the primary reason" required />

                            <x-mary-textarea label="Detailed Explanation" wire:model="detailedReason"
                                placeholder="Please provide a detailed explanation for the reschedule request. Include any relevant information that supports your request."
                                rows="4" required />

                            <x-mary-textarea label="Impact Assessment" wire:model="impactAssessment"
                                placeholder="Describe how this reschedule might affect participants, logistics, or other stakeholders."
                                rows="3" />
                        </div>
                    </x-mary-card>

                    {{-- Supporting Documents --}}
                    <x-mary-card title="Supporting Documents"
                        subtitle="Upload any relevant supporting documents (optional)">
                        <div class="space-y-4">
                            <div class="bg-info/10 p-4 rounded-lg border-l-4 border-info">
                                <div class="flex items-start space-x-2">
                                    <x-mary-icon name="s-information-circle" class="w-5 h-5 text-info mt-0.5" />
                                    <div class="text-sm">
                                        <p class="font-medium mb-1">Supporting documents may include:</p>
                                        <ul class="list-disc list-inside space-y-1 text-gray-600">
                                            <li>Email correspondence with speakers/guests</li>
                                            <li>Venue availability confirmations</li>
                                            <li>Weather reports or safety advisories</li>
                                            <li>Academic calendar conflicts</li>
                                            <li>Medical certificates or health advisories</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <x-mary-file wire:model="supportingDocuments" multiple
                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                                hint="Upload supporting documents (PDF, DOC, JPG, PNG). Max 5MB per file." />
                        </div>
                    </x-mary-card>

                    {{-- Contact Information --}}
                    <x-mary-card title="Contact Information" subtitle="How can we reach you regarding this request?">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <x-mary-input label="Primary Contact Person" wire:model="contactPerson"
                                placeholder="Name of person to contact" required />

                            <x-mary-input label="Contact Email" type="email" wire:model="contactEmail"
                                placeholder="email@example.com" required />

                            <x-mary-input label="Contact Phone" wire:model="contactPhone" placeholder="09XX XXX XXXX"
                                required />

                            <x-mary-select label="Preferred Contact Method" wire:model="preferredContact"
                                :options="[
                                    ['id' => 'email', 'name' => 'Email'],
                                    ['id' => 'phone', 'name' => 'Phone'],
                                    ['id' => 'both', 'name' => 'Both Email and Phone'],
                                ]" required />
                        </div>
                    </x-mary-card>

                    {{-- Urgency Level --}}
                    <x-mary-card title="Urgency Level" subtitle="How urgent is this reschedule request?">
                        <div class="space-y-4">
                            <x-mary-radio label="Normal - Standard processing time (5-7 business days)"
                                wire:model="urgencyLevel" value="normal" />

                            <x-mary-radio label="Urgent - Expedited processing needed (2-3 business days)"
                                wire:model="urgencyLevel" value="urgent" />

                            <x-mary-radio label="Emergency - Immediate attention required (within 24 hours)"
                                wire:model="urgencyLevel" value="emergency" />

                            @if ($urgencyLevel === 'urgent' || $urgencyLevel === 'emergency')
                                <x-mary-textarea label="Justification for Urgency" wire:model="urgencyJustification"
                                    placeholder="Please explain why this request requires urgent or emergency processing."
                                    rows="3" required />
                            @endif
                        </div>
                    </x-mary-card>

                    {{-- Agreement --}}
                    <x-mary-card title="Agreement and Submission" subtitle="Please review and agree to the terms">
                        <div class="space-y-4">
                            <div class="bg-base-200 p-4 rounded-lg">
                                <h4 class="font-semibold mb-2">Terms and Conditions:</h4>
                                <div class="text-sm text-gray-600 space-y-1">
                                    <p>• I understand that this reschedule request is subject to approval</p>
                                    <p>• I certify that all information provided is accurate and complete</p>
                                    <p>• I understand that venue availability will be confirmed before approval</p>
                                    <p>• I agree to notify all participants if the reschedule is approved</p>
                                    <p>• I understand that frequent reschedule requests may affect future approvals</p>
                                    <p>• I will provide additional information if requested by OSA or GSO</p>
                                </div>
                            </div>

                            <x-mary-checkbox label="I agree to the terms and conditions above"
                                wire:model="agreeToTerms" required />
                        </div>
                    </x-mary-card>

                    {{-- Form Actions --}}
                    <div class="flex justify-between items-center pt-6">
                        <x-mary-button label="Save as Draft" icon="s-document" class="btn-secondary"
                            wire:click="saveDraft" />

                        <div class="space-x-3">
                            <x-mary-button label="Preview Request" icon="s-eye" class="btn-accent" />

                            <x-mary-button label="Submit Reschedule Request" icon="s-paper-airplane"
                                class="btn-primary" type="submit" />
                        </div>
                    </div>
                </x-mary-form>
            @endif

            {{-- Recent Reschedule Requests --}}
            <x-mary-card title="Recent Reschedule Requests" subtitle="Your previous reschedule request history">
                <div class="space-y-4">
                    <div class="flex items-start space-x-4 p-4 bg-green-50 rounded-lg border-l-4 border-green-400">
                        <div class="flex-shrink-0">
                            <x-mary-icon name="s-check-circle" class="w-6 h-6 text-green-500 mt-0.5" />
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-green-900">Workshop Series Reschedule</h4>
                                    <p class="text-sm text-green-700 mt-1">Venue changed from Room 301 to Library Hall
                                    </p>
                                    <p class="text-xs text-green-600 mt-2">Requested: Sep 20, 2025 • Approved: Sep 22,
                                        2025</p>
                                </div>
                                <x-mary-badge value="Approved" class="badge-success" />
                            </div>
                            <p class="text-sm text-green-600 mt-2">Reason: Original venue had technical issues with
                                audio equipment</p>
                        </div>
                    </div>

                    <div class="flex items-start space-x-4 p-4 bg-yellow-50 rounded-lg border-l-4 border-yellow-400">
                        <div class="flex-shrink-0">
                            <x-mary-icon name="s-clock" class="w-6 h-6 text-yellow-500 mt-0.5" />
                        </div>
                        <div class="flex-1">
                            <div class="flex justify-between items-start">
                                <div>
                                    <h4 class="font-semibold text-yellow-900">Cultural Night Reschedule</h4>
                                    <p class="text-sm text-yellow-700 mt-1">Date change from Oct 24 to Oct 26, 2025</p>
                                    <p class="text-xs text-yellow-600 mt-2">Requested: Oct 1, 2025 • Under Review</p>
                                </div>
                                <x-mary-badge value="Under Review" class="badge-warning" />
                            </div>
                            <p class="text-sm text-yellow-600 mt-2">Reason: Conflict with another major university
                                event</p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 text-center">
                    <x-mary-button label="View All Reschedule History" icon="s-clock" class="btn-sm btn-ghost" />
                </div>
            </x-mary-card>
        </div>
    </div>
</div>
