<x-layouts.superadmin>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Submit Event Ticket (Digital Proposal)') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">

            {{-- Instructions Card --}}
            <x-mary-card title="Event Request Guidelines" subtitle="Please read before submitting your proposal"
                class="mb-6">
                <div class="bg-info/10 p-4 rounded-lg border-l-4 border-info mb-4">
                    <div class="flex items-start space-x-2">
                        <x-mary-icon name="s-information-circle" class="w-5 h-5 text-info mt-0.5" />
                        <div class="text-sm">
                            <p class="font-medium mb-2">Important Guidelines:</p>
                            <ul class="list-disc list-inside space-y-1 text-gray-600">
                                <li>Submit your request at least 14 days before your event date</li>
                                <li>All required fields must be completed</li>
                                <li>Upload all necessary attachments (permit forms, venue reservations, etc.)</li>
                                <li>Events must comply with university policies and guidelines</li>
                                <li>You will receive notifications about approval status via email</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </x-mary-card>

            {{-- Main Form --}}
            <x-mary-form wire:submit="save">
                {{-- Organization Information --}}
                <x-mary-card title="Organization Information" subtitle="Details about your student organization">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-mary-input label="Organization Name" wire:model="organizationName"
                            placeholder="Enter your organization name" required />

                        <x-mary-input label="Organization Type" wire:model="organizationType"
                            placeholder="e.g., Academic, Cultural, Sports" required />

                        <x-mary-input label="Contact Person" wire:model="contactPerson"
                            placeholder="Name of primary contact" required />

                        <x-mary-input label="Contact Email" type="email" wire:model="contactEmail"
                            placeholder="contact@example.com" required />

                        <x-mary-input label="Contact Phone" wire:model="contactPhone" placeholder="09XX XXX XXXX"
                            required />

                        <x-mary-input label="Organization Adviser" wire:model="adviser"
                            placeholder="Name of faculty adviser" required />
                    </div>
                </x-mary-card>

                {{-- Event Details --}}
                <x-mary-card title="Event Details" subtitle="Information about your proposed event">
                    <div class="space-y-4">
                        <x-mary-input label="Event Title" wire:model="eventTitle" placeholder="Enter your event title"
                            required />

                        <x-mary-textarea label="Event Description" wire:model="eventDescription"
                            placeholder="Provide a detailed description of your event, including objectives and activities"
                            rows="4" required />

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <x-mary-select label="Event Type" wire:model="eventType" :options="[
                                ['id' => 'academic', 'name' => 'Academic'],
                                ['id' => 'cultural', 'name' => 'Cultural'],
                                ['id' => 'sports', 'name' => 'Sports'],
                                ['id' => 'fundraising', 'name' => 'Fundraising'],
                                ['id' => 'meeting', 'name' => 'Meeting'],
                                ['id' => 'workshop', 'name' => 'Workshop/Seminar'],
                                ['id' => 'competition', 'name' => 'Competition'],
                                ['id' => 'social', 'name' => 'Social Event'],
                            ]"
                                placeholder="Select event type" required />

                            <x-mary-input label="Expected Participants" type="number" wire:model="expectedParticipants"
                                placeholder="Number of attendees" required />

                            <x-mary-select label="Target Audience" wire:model="targetAudience" :options="[
                                ['id' => 'students', 'name' => 'Students Only'],
                                ['id' => 'faculty', 'name' => 'Faculty Only'],
                                ['id' => 'both', 'name' => 'Students & Faculty'],
                                ['id' => 'public', 'name' => 'General Public'],
                                ['id' => 'members', 'name' => 'Organization Members'],
                            ]"
                                placeholder="Select target audience" required />
                        </div>
                    </div>
                </x-mary-card>

                {{-- Schedule & Venue --}}
                <x-mary-card title="Schedule & Venue" subtitle="When and where your event will take place">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-mary-datetime label="Event Start Date & Time" wire:model="eventStartDateTime" required />

                        <x-mary-datetime label="Event End Date & Time" wire:model="eventEndDateTime" required />

                        <x-mary-input label="Preferred Venue" wire:model="preferredVenue"
                            placeholder="e.g., Student Center Auditorium" required />

                        <x-mary-input label="Alternative Venue" wire:model="alternativeVenue"
                            placeholder="Backup venue option" />
                    </div>

                    <div class="mt-4">
                        <x-mary-textarea label="Special Requirements" wire:model="specialRequirements"
                            placeholder="Audio/visual equipment, seating arrangement, catering, etc." rows="3" />
                    </div>
                </x-mary-card>

                {{-- Budget Information --}}
                <x-mary-card title="Budget Information" subtitle="Financial details of your event">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-mary-input label="Estimated Total Budget" type="number" step="0.01"
                            wire:model="totalBudget" placeholder="0.00" prefix="₱" />

                        <x-mary-select label="Funding Source" wire:model="fundingSource" :options="[
                            ['id' => 'org_funds', 'name' => 'Organization Funds'],
                            ['id' => 'fundraising', 'name' => 'Fundraising'],
                            ['id' => 'sponsorship', 'name' => 'Sponsorship'],
                            ['id' => 'university_grant', 'name' => 'University Grant'],
                            ['id' => 'mixed', 'name' => 'Mixed Sources'],
                        ]"
                            placeholder="Select funding source" />
                    </div>

                    <div class="mt-4">
                        <x-mary-textarea label="Budget Breakdown" wire:model="budgetBreakdown"
                            placeholder="Itemized list of expenses (venue, equipment, materials, etc.)"
                            rows="4" />
                    </div>
                </x-mary-card>

                {{-- File Attachments --}}
                <x-mary-card title="Attachments" subtitle="Upload required documents and supporting files">
                    <div class="space-y-4">
                        <div class="bg-warning/10 p-4 rounded-lg border-l-4 border-warning">
                            <div class="flex items-start space-x-2">
                                <x-mary-icon name="s-exclamation-triangle" class="w-5 h-5 text-warning mt-0.5" />
                                <div class="text-sm">
                                    <p class="font-medium mb-1">Required Documents:</p>
                                    <ul class="list-disc list-inside space-y-1 text-gray-600">
                                        <li>Event proposal document (PDF format preferred)</li>
                                        <li>Organization registration certificate</li>
                                        <li>Venue reservation form (if applicable)</li>
                                        <li>Budget worksheet or financial plan</li>
                                        <li>List of event organizers and volunteers</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <x-mary-file wire:model="attachments" multiple
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx"
                            hint="Upload multiple files (PDF, DOC, JPG, PNG, XLS). Max 10MB per file." />
                    </div>
                </x-mary-card>

                {{-- Additional Information --}}
                <x-mary-card title="Additional Information" subtitle="Any other relevant details">
                    <div class="space-y-4">
                        <x-mary-checkbox label="This event involves external participants or guests"
                            wire:model="hasExternalGuests" />

                        <x-mary-checkbox label="This event requires security arrangements"
                            wire:model="requiresSecurity" />

                        <x-mary-checkbox label="This event involves food service or catering"
                            wire:model="involvesCatering" />

                        <x-mary-checkbox label="This event requires parking arrangements"
                            wire:model="requiresParking" />

                        <x-mary-textarea label="Additional Notes" wire:model="additionalNotes"
                            placeholder="Any other information you'd like to share about your event" rows="3" />
                    </div>
                </x-mary-card>

                {{-- Agreement & Submission --}}
                <x-mary-card title="Agreement & Submission" subtitle="Please review and agree to the terms">
                    <div class="space-y-4">
                        <div class="bg-base-200 p-4 rounded-lg">
                            <h4 class="font-semibold mb-2">Terms and Conditions:</h4>
                            <div class="text-sm text-gray-600 space-y-1">
                                <p>• I certify that all information provided is accurate and complete</p>
                                <p>• The event will comply with all university policies and guidelines</p>
                                <p>• I understand that approval is subject to availability and review</p>
                                <p>• I agree to follow all safety protocols and requirements</p>
                                <p>• Changes to approved events must be requested through proper channels</p>
                            </div>
                        </div>

                        <x-mary-checkbox label="I agree to the terms and conditions above" wire:model="agreeToTerms"
                            required />
                    </div>
                </x-mary-card>

                {{-- Form Actions --}}
                <div class="flex justify-between items-center pt-6">
                    <x-mary-button label="Save as Draft" icon="s-document" class="btn-secondary"
                        wire:click="saveDraft" />

                    <div class="space-x-3">
                        <x-mary-button label="Preview" icon="s-eye" class="btn-accent" />

                        <x-mary-button label="Submit Ticket" icon="s-paper-airplane" class="btn-primary"
                            type="submit" />
                    </div>
                </div>
            </x-mary-form>
        </div>
    </div>
</x-layouts.superadmin>
