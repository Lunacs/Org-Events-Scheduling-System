<div class="space-y-6 p-4">
    @if ($ticket)
        {{-- Display validation errors --}}
        @if ($errors->any())
            <x-mary-alert title="Please fix the following errors:" icon="o-exclamation-triangle" class="alert-error">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-mary-alert>
        @endif

        <x-mary-form wire:submit="updateTicket">
            {{-- Show status-specific notice --}}
            <div class="alert {{ 'alert-info' }} mb-4">
                <x-mary-icon name="s-information-circle" class="w-5 h-5" />
                <span>
                    <strong>Revision Required:</strong> Please update the requested information and resubmit.
                </span>
            </div>

            {{-- Copy the same form structure from submit-ticket.blade.php --}}
            {{-- But add conditional readonly based on isFieldEditable() --}}

            <x-mary-card title="Organization Information" subtitle="Details about your student organization">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-mary-input label="Organization Name" wire:model="organizationName" readonly />
                    <x-mary-input label="Organization Course" wire:model="organizationCourse" readonly />
                    <x-mary-input label="Name of Proponent" wire:model="proponentName" readonly />
                    <x-mary-input label="Contact Email" wire:model="contactEmail" readonly />
                    <x-mary-input label="Proponent Position" wire:model="proponentPosition" readonly />
                    <x-mary-input label="Organization Adviser" wire:model="adviser" readonly />
                    <x-mary-input label="Contact of Proponent" wire:model="form.proponent_contact" :readonly="!$this->isFieldEditable('proponent_contact')" />
                    <x-mary-input label="Contact of Adviser" wire:model="form.adviser_contact" :readonly="!$this->isFieldEditable('adviser_contact')" />
                </div>
            </x-mary-card>

            {{-- Event Details --}}
            <x-mary-card title="Event Details" subtitle="Information about your proposed event">
                <div class="space-y-4">
                    <x-mary-input label="Event Title" wire:model="form.eventTitle" placeholder="Enter your event title"
                        :readonly="!$this->isFieldEditable('eventTitle')" />

                    <x-mary-textarea label="Event Description" wire:model="form.eventDescription"
                        placeholder="Provide a detailed description of your event, including objectives and activities, or your rationale."
                        rows="4" :readonly="!$this->isFieldEditable('eventDescription')" />

                    <div class="grid grid-cols-1 gap-4">
                        <x-mary-select label="Event Type" wire:model.live="form.eventType" :options="$eventTypes"
                            option-value="event_type_id" option-label="type_name" placeholder="Select event type"
                            :readonly="!$this->isFieldEditable('eventType')" />

                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <x-mary-input label="Number of PLV Participants" type="number"
                            wire:model.live="form.expectedPLVParticipants" placeholder="Number of attendees"
                            :readonly="!$this->isFieldEditable('expectedPLVParticipants')" />

                        <x-mary-input label="Number of non PLV Participants" type="number"
                            wire:model.live="form.expectedNonPLVParticipants" placeholder="Number of attendees"
                            :readonly="!$this->isFieldEditable('expectedNonPLVParticipants')" />

                        <x-mary-input label="Expected Participants" type="number"
                            value="{{ $this->expectedParticipants }}" placeholder="Number of attendees" readonly />
                    </div>
                </div>
            </x-mary-card>

            {{-- Schedule & Venue --}}
            <x-mary-card title="Schedule & Venue">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-mary-datetime label="Event Start Date" wire:model.live="form.eventStartDate" :readonly="!$this->isFieldEditable('eventStartDate')" />
                    <x-mary-datetime label="Event End Date" wire:model.live="form.eventEndDate" :readonly="!$this->isFieldEditable('eventEndDate')" />
                    <x-mary-datetime label="Event Start Time" wire:model.live="form.eventStartTime" type="time"
                        :readonly="!$this->isFieldEditable('eventStartTime')" />
                    <x-mary-datetime label="Event End Time" wire:model.live="form.eventEndTime" type="time"
                        :readonly="!$this->isFieldEditable('eventEndTime')" />
                    <x-mary-input label="Preferred Venue" wire:model="form.preferredVenue" :readonly="!$this->isFieldEditable('preferredVenue')" />
                    <x-mary-input label="Alternative Venue" wire:model="form.alternativeVenue" :readonly="!$this->isFieldEditable('alternativeVenue')" />
                </div>
            </x-mary-card>

            {{-- Budget Information --}}
            <x-mary-card title="Budget Information" subtitle="Financial details of your event">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-mary-input label="Estimated Total Proposed Budget" type="number" step="0.01"
                        wire:model.live="form.totalBudget" placeholder="0.00" prefix="₱" :readonly="!$this->isFieldEditable('totalBudget')" />

                    <x-mary-select label="Funding Source" wire:model="form.fundingSource" :options="$fundSources"
                        option-value="source_id" option-label="source_name" placeholder="Select funding source"
                        :readonly="!$this->isFieldEditable('fundingSource')" />
                </div>

                <div class="mt-4">
                    <x-mary-textarea label="Budget Breakdown" wire:model="form.budgetBreakdown"
                        placeholder="Itemized list of expenses (venue, equipment, materials, etc.)" rows="4"
                        :readonly="!$this->isFieldEditable('budgetBreakdown')" />
                </div>

                <div class="mt-4">
                    <x-mary-radio label="IGP Request" wire:model.live="form.igp_requested" :options="[['id' => 'true', 'name' => 'Requested'], ['id' => 'false', 'name' => 'Not Requested']]" inline />
                </div>

                @if ($form->igp_requested === 'true')
                    <div class="mt-4">
                        <x-mary-textarea label="IGP Brief Description" wire:model="form.igp_details"
                            placeholder="List all descriptions for IGP requested items" rows="4"
                            :readonly="!$this->isFieldEditable('igp_details')" />
                    </div>
                @endif
            </x-mary-card>

            {{-- File Attachments --}}
            <x-mary-card title="Attachments" subtitle="Upload required documents and supporting files">
                <div class="space-y-4">
                    <x-documentary-requirements :event-type-id="$eventType" />

                    <div class="space-y-2">
                        <x-mary-file wire:model="form.newAttachments"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx"
                            hint="Upload one file at a time (PDF, DOC, JPG, PNG, XLS). Max 10MB per file." />

                        @if ($attachments)
                            <div class="mt-4 space-y-2">
                                <p class="text-sm font-medium">Existing Attachments:</p>
                                @foreach ($attachments as $index => $attachment)
                                    <div class="flex items-center justify-between bg-base-200 p-2 rounded">
                                        <div class="flex items-center space-x-2">
                                            <x-mary-icon name="o-document" class="w-4 h-4" />
                                            <span class="text-sm">{{ $attachment['file_name'] }}</span>
                                        </div>
                                        <x-mary-button icon="o-trash"
                                            wire:click="removeAttachment({{ $index }})"
                                            class="btn-ghost btn-sm btn-error" spinner />
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        @if ($newAttachments)
                            <div class="mt-4 space-y-2">
                                <p class="text-sm font-medium">New Attachments:</p>
                                @foreach ($newAttachments as $index => $file)
                                    <div class="flex items-center justify-between bg-base-200 p-2 rounded">
                                        <span class="text-sm">{{ $file->getClientOriginalName() }}</span>
                                        <x-mary-button icon="o-x-mark"
                                            wire:click="removeNewAttachment({{ $index }})"
                                            class="btn-ghost btn-sm" spinner />
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </x-mary-card>


            {{-- Additional Information --}}
            <x-mary-card title="Additional Information" subtitle="Any other relevant details">
                <div class="space-y-4">
                    <x-mary-textarea label="Additional Notes" wire:model="form.additionalNotes"
                        placeholder="Any other information you'd like to share about your event (security, food service, parking etc.)"
                        rows="3" :readonly="!$this->isFieldEditable('additionalNotes')" />
                </div>
            </x-mary-card>

            {{-- Form Actions --}}
            <x-slot:actions>
                <x-mary-button label="Cancel" @click="$wire.dispatch('close-edit-drawer')" />
                <x-mary-button label="Preview Changes" icon="s-eye" class="btn-accent"
                    wire:click="openPreviewModal" spinner="openPreviewModal" />
                <x-mary-button label="Submit Revision" icon="s-paper-airplane" class="btn-primary" type="submit"
                    spinner="updateTicket" />
            </x-slot:actions>
        </x-mary-form>
    @endif

    {{-- Modal rendered at body level using Alpine teleport --}}
    <template x-teleport="body">
        <x-mary-modal wire:model="showPreviewModal" title="Review Changes" class="backdrop-blur !fixed !z-[9999]"
            box-class="max-w-5xl max-h-[85vh] overflow-y-auto relative z-[10000]">
            @if ($this->previewTicket)
                <x-tickets.ticket-preview :ticket="$this->previewTicket" />
            @endif
        </x-mary-modal>
    </template>
</div>
