<div class="space-y-6 p-4">
    @if ($ticket)
        {{-- Display validation errors --}}
        @if ($errors->any())
            <x-ui.alert title="Please fix the following errors:" icon="o-exclamation-triangle" class="alert-error">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </x-ui.alert>
        @endif

        <form wire:submit="updateTicket" class="space-y-6">
            {{-- Show status-specific notice --}}
            <div class="alert {{ 'alert-info' }} mb-4">
                <x-ui.icon name="s-information-circle" class="w-5 h-5" />
                <span>
                    <strong>Revision Required:</strong> Please update the requested information and resubmit.
                </span>
            </div>

            {{-- Copy the same form structure from submit-ticket.blade.php --}}
            {{-- But add conditional readonly based on isFieldEditable() --}}

            <x-ui.card title="Organization Information" subtitle="Details about your student organization">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.input label="Organization Name" wire:model="organizationName" readonly />
                    <x-ui.input label="Organization Course" wire:model="organizationCourse" readonly />
                    <x-ui.input label="Name of Proponent" wire:model="proponentName" readonly />
                    <x-ui.input label="Contact Email" wire:model="contactEmail" readonly />
                    <x-ui.input label="Proponent Position" wire:model="proponentPosition" readonly />
                    <x-ui.input label="Organization Adviser" wire:model="adviser" readonly />
                    <x-ui.input label="Contact of Proponent" wire:model="form.proponent_contact" :readonly="!$this->isFieldEditable('proponent_contact')" />
                    <x-ui.input label="Contact of Adviser" wire:model="form.adviser_contact" :readonly="!$this->isFieldEditable('adviser_contact')" />
                </div>
            </x-ui.card>

            {{-- Event Details --}}
            <x-ui.card title="Event Details" subtitle="Information about your proposed event">
                <div class="space-y-4">
                    <x-ui.input label="Event Title" wire:model="form.eventTitle" placeholder="Enter your event title"
                        :readonly="!$this->isFieldEditable('eventTitle')" />

                    <x-ui.textarea label="Event Description" wire:model="form.eventDescription"
                        placeholder="Provide a detailed description of your event, including objectives and activities, or your rationale."
                        rows="4" :readonly="!$this->isFieldEditable('eventDescription')" />

                    <div class="grid grid-cols-1 gap-4">
                        <x-ui.select label="Event Type" wire:model.live="form.eventType" :options="$eventTypes"
                            option-value="event_type_id" option-label="type_name" placeholder="Select event type"
                            :readonly="!$this->isFieldEditable('eventType')" />

                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

                        <x-ui.input label="Number of PLV Participants" type="number"
                            wire:model.live="form.expectedPLVParticipants" placeholder="Number of attendees"
                            :readonly="!$this->isFieldEditable('expectedPLVParticipants')" />

                        <x-ui.input label="Number of non PLV Participants" type="number"
                            wire:model.live="form.expectedNonPLVParticipants" placeholder="Number of attendees"
                            :readonly="!$this->isFieldEditable('expectedNonPLVParticipants')" />

                        <x-ui.input label="Expected Participants" type="number"
                            value="{{ $this->expectedParticipants }}" placeholder="Number of attendees" readonly />
                    </div>
                </div>
            </x-ui.card>

            {{-- Schedule & Venue --}}
            <x-ui.card title="Schedule & Venue">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <x-ui.datetime label="Event Start Date" wire:model.live="form.eventStartDate" :readonly="!$this->isFieldEditable('eventStartDate')" />
                    <x-ui.datetime label="Event End Date" wire:model.live="form.eventEndDate" :readonly="!$this->isFieldEditable('eventEndDate')" />
                    <x-ui.datetime label="Event Start Time" wire:model.live="form.eventStartTime" type="time"
                        :readonly="!$this->isFieldEditable('eventStartTime')" />
                    <x-ui.datetime label="Event End Time" wire:model.live="form.eventEndTime" type="time"
                        :readonly="!$this->isFieldEditable('eventEndTime')" />
                    <x-ui.input label="Preferred Venue" wire:model="form.preferredVenue" :readonly="!$this->isFieldEditable('preferredVenue')" />
                    <x-ui.input label="Alternative Venue" wire:model="form.alternativeVenue" :readonly="!$this->isFieldEditable('alternativeVenue')" />
                </div>
            </x-ui.card>

            {{-- Budget Information --}}
            <x-ui.card title="Budget Information" subtitle="Financial details of your event">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    {{-- Inline DaisyUI input: x-ui.input has no `prefix` slot, so the ₱ prefix is rendered inline --}}
                    <div>
                        <label class="block text-sm font-medium mb-1">Estimated Total Proposed Budget</label>
                        <label @class([
                            'input flex items-center gap-2 w-full',
                            'border-dashed' => !$this->isFieldEditable('totalBudget'),
                        ])>
                            <span class="opacity-60">₱</span>
                            <input type="number" step="0.01" wire:model.live="form.totalBudget" placeholder="0.00"
                                class="grow" @readonly(!$this->isFieldEditable('totalBudget')) />
                        </label>
                        @error('form.totalBudget')
                            <x-ui.input-error :messages="$message" class="mt-1" />
                        @enderror
                    </div>

                    <x-ui.select label="Funding Source" wire:model="form.fundingSource" :options="$fundSources"
                        option-value="source_id" option-label="source_name" placeholder="Select funding source"
                        :readonly="!$this->isFieldEditable('fundingSource')" />
                </div>

                <div class="mt-4">
                    <x-ui.textarea label="Budget Breakdown" wire:model="form.budgetBreakdown"
                        placeholder="Itemized list of expenses (venue, equipment, materials, etc.)" rows="4"
                        :readonly="!$this->isFieldEditable('budgetBreakdown')" />
                </div>

                <div class="mt-4">
                    <x-ui.radio label="IGP Request" wire:model.live="form.igp_requested" :options="[['id' => 'true', 'name' => 'Requested'], ['id' => 'false', 'name' => 'Not Requested']]" inline />
                </div>

                @if ($form->igp_requested === 'true')
                    <div class="mt-4">
                        <x-ui.textarea label="IGP Brief Description" wire:model="form.igp_details"
                            placeholder="List all descriptions for IGP requested items" rows="4"
                            :readonly="!$this->isFieldEditable('igp_details')" />
                    </div>
                @endif
            </x-ui.card>

            {{-- File Attachments --}}
            <x-ui.card title="Attachments" subtitle="Upload required documents and supporting files">
                <div class="space-y-4">
                    <x-documentary-requirements :event-type-id="$eventType" />

                    <div class="space-y-2">
                        <x-ui.file wire:model="form.newAttachments"
                            accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.xls,.xlsx"
                            hint="Upload one file at a time (PDF, DOC, JPG, PNG, XLS). Max 10MB per file." />

                        @if ($attachments)
                            <div class="mt-4 space-y-2">
                                <p class="text-sm font-medium">Existing Attachments:</p>
                                @foreach ($attachments as $index => $attachment)
                                    <div class="flex items-center justify-between bg-base-200 p-2 rounded">
                                        <div class="flex items-center space-x-2">
                                            <x-ui.icon name="o-document" class="w-4 h-4" />
                                            <span class="text-sm">{{ $attachment['file_name'] }}</span>
                                        </div>
                                        <x-ui.button icon="o-trash" wire:click="removeAttachment({{ $index }})"
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
                                        <x-ui.button icon="o-x-mark"
                                            wire:click="removeNewAttachment({{ $index }})"
                                            class="btn-ghost btn-sm" spinner />
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>
            </x-ui.card>


            {{-- Additional Information --}}
            <x-ui.card title="Additional Information" subtitle="Any other relevant details">
                <div class="space-y-4">
                    <x-ui.textarea label="Additional Notes" wire:model="form.additionalNotes"
                        placeholder="Any other information you'd like to share about your event (security, food service, parking etc.)"
                        rows="3" :readonly="!$this->isFieldEditable('additionalNotes')" />
                </div>
            </x-ui.card>

            {{-- Form Actions --}}
            <div class="flex w-full flex-wrap items-center justify-end gap-3 pt-5">
                <x-ui.button label="Cancel" @click="$wire.dispatch('close-edit-drawer')" />
                <x-ui.button label="Preview Changes" icon="s-eye" class="btn-accent" wire:click="openPreviewModal"
                    spinner="openPreviewModal" />
                <x-ui.button label="Submit Revision" icon="s-paper-airplane" class="btn-primary" type="submit"
                    spinner="updateTicket" />
            </div>
        </form>
    @endif

    {{-- Modal rendered at body level using Alpine teleport --}}
    <template x-teleport="body">
        <div x-data="{ show: @entangle('showPreviewModal') }" class="modal backdrop-blur !fixed !z-[9999]" :class="{ 'modal-open': show }"
            @keydown.escape.window="show = false" role="dialog" aria-modal="true">
            <div class="modal-box max-w-5xl max-h-[85vh] overflow-y-auto relative z-[10000]">
                <button type="button" class="btn btn-sm btn-circle btn-ghost absolute right-3 top-3"
                    @click="show = false" aria-label="Close">✕</button>
                <h3 class="text-lg font-bold mb-4">Review Changes</h3>
                @if ($this->previewTicket)
                    <x-tickets.ticket-preview :ticket="$this->previewTicket" />
                @endif
            </div>
            <label class="modal-backdrop" @click="show = false">Close</label>
        </div>
    </template>
</div>
