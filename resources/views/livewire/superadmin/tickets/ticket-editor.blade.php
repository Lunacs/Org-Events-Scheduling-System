<div class="p-4 sm:p-6 max-w-4xl mx-auto">
    {{-- Header with Breadcrumb --}}
    <section
        class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm mb-6">
        <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
        <div class="relative p-6 sm:p-8">
            {{-- Breadcrumb --}}
            <nav class="flex items-center text-sm text-base-content/60 mb-4 relative z-10">
                <a href="{{ route('superadmin.tickets') }}" wire:navigate
                    class="hover:text-primary transition-colors">
                    Ticket Management
                </a>
                <x-mary-icon name="o-chevron-right" class="w-4 h-4 mx-2" />
                <span class="text-base-content font-medium">
                    {{ $isEditing ? 'Edit Ticket' : 'Create New Ticket' }}
                </span>
            </nav>

            <div class="flex items-center gap-4 relative z-10">
                <span class="inline-flex h-12 w-12 items-center justify-center rounded-2xl bg-primary/20">
                    <x-mary-icon name="{{ $isEditing ? 's-pencil-square' : 's-plus' }}"
                        class="w-6 h-6 text-primary" />
                </span>
                <div>
                    <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-base-content">
                        {{ $isEditing ? 'Edit Ticket' : 'Create New Ticket' }}
                    </h1>
                    <p class="text-sm text-base-content/70 mt-1">
                        {{ $isEditing ? 'Update the ticket details below.' : 'Create a ticket on behalf of an organization.' }}
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Main Form --}}
    <form wire:submit.prevent="save" class="space-y-6">

        {{-- Section: Organization & Event Info --}}
        <x-mary-card shadow class="bg-white dark:bg-base-200 border-none">
            <div class="space-y-5">
                <div class="pb-3 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-information-circle" class="w-4 h-4" />
                            Organization & Event Info
                        </span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label for="user_id" class="block text-sm font-medium text-base-content/70 mb-1">Organization User <span class="text-red-500">*</span></label>
                        <select id="user_id" wire:model="user_id" class="select select-bordered w-full" required>
                            <option value="">Select organization user...</option>
                            @foreach ($this->orgUsers as $user)
                                <option value="{{ $user['id'] }}">{{ $user['name'] }}</option>
                            @endforeach
                        </select>
                        @error('user_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="event_type_id" class="block text-sm font-medium text-base-content/70 mb-1">Event Type <span class="text-red-500">*</span></label>
                        <select id="event_type_id" wire:model="event_type_id" class="select select-bordered w-full" required>
                            <option value="">Select event type...</option>
                            @foreach ($this->eventTypes as $et)
                                <option value="{{ $et->event_type_id }}">{{ $et->type_name }}</option>
                            @endforeach
                        </select>
                        @error('event_type_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-base-content/70 mb-1">Status <span class="text-red-500">*</span></label>
                        <select id="status" wire:model="status" class="select select-bordered w-full" required>
                            <option value="received">Received</option>
                            <option value="gso_review">GSO Review</option>
                            <option value="pending_osa_approval">Pending OSA Approval</option>
                            <option value="for_revision">For Revision</option>
                            <option value="approved">Approved</option>
                            <option value="amended">Amended</option>
                            <option value="completed">Completed</option>
                        </select>
                        @error('status') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="title" class="block text-sm font-medium text-base-content/70 mb-1">Event Title <span class="text-red-500">*</span></label>
                        <input id="title" type="text" wire:model="title" class="input input-bordered w-full"
                            placeholder="Enter event title..." required />
                        @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-base-content/70 mb-1">Description <span class="text-red-500">*</span></label>
                        <textarea id="description" wire:model="description" class="textarea textarea-bordered w-full" rows="3"
                            placeholder="Describe the event (min 20 characters)..." required></textarea>
                        @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </x-mary-card>

        {{-- Section: Schedule --}}
        <x-mary-card shadow class="bg-white dark:bg-base-200 border-none">
            <div class="space-y-5">
                <div class="pb-3 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-calendar" class="w-4 h-4" />
                            Schedule
                        </span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="date_from" class="block text-sm font-medium text-base-content/70 mb-1">Start Date <span class="text-red-500">*</span></label>
                        <input id="date_from" type="date" wire:model="date_from" class="input input-bordered w-full" required />
                        @error('date_from') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="date_to" class="block text-sm font-medium text-base-content/70 mb-1">End Date <span class="text-red-500">*</span></label>
                        <input id="date_to" type="date" wire:model="date_to" class="input input-bordered w-full" required />
                        @error('date_to') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="time_from" class="block text-sm font-medium text-base-content/70 mb-1">Start Time <span class="text-red-500">*</span></label>
                        <input id="time_from" type="time" wire:model="time_from" class="input input-bordered w-full" required />
                        @error('time_from') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="time_to" class="block text-sm font-medium text-base-content/70 mb-1">End Time <span class="text-red-500">*</span></label>
                        <input id="time_to" type="time" wire:model="time_to" class="input input-bordered w-full" required />
                        @error('time_to') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </x-mary-card>

        {{-- Section: Venue --}}
        <x-mary-card shadow class="bg-white dark:bg-base-200 border-none">
            <div class="space-y-5">
                <div class="pb-3 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-map-pin" class="w-4 h-4" />
                            Venue
                        </span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="venue_requested" class="block text-sm font-medium text-base-content/70 mb-1">Preferred Venue <span class="text-red-500">*</span></label>
                        <select id="venue_requested" wire:model.live="venue_requested" class="select select-bordered w-full" required>
                            <option value="">Select venue...</option>
                            @foreach ($this->venuesList as $v)
                                <option value="{{ $v->venue_id }}">{{ $v->venue_name }}</option>
                            @endforeach
                            <option value="other">Other (specify below)</option>
                        </select>
                        @error('venue_requested') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    @if ($venue_requested === 'other')
                        <div>
                            <label for="venue_other" class="block text-sm font-medium text-base-content/70 mb-1">Specify Venue <span class="text-red-500">*</span></label>
                            <input id="venue_other" type="text" wire:model="venue_other" class="input input-bordered w-full"
                                placeholder="Enter venue name..." required />
                            @error('venue_other') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif

                    <div>
                        <label for="alternate_venue" class="block text-sm font-medium text-base-content/70 mb-1">Alternate Venue</label>
                        <select id="alternate_venue" wire:model.live="alternate_venue" class="select select-bordered w-full">
                            <option value="">None</option>
                            @foreach ($this->venuesList as $v)
                                <option value="{{ $v->venue_id }}">{{ $v->venue_name }}</option>
                            @endforeach
                            <option value="other">Other (specify below)</option>
                        </select>
                    </div>

                    @if ($alternate_venue === 'other')
                        <div>
                            <label for="alternate_venue_other" class="block text-sm font-medium text-base-content/70 mb-1">Specify Alternate Venue <span class="text-red-500">*</span></label>
                            <input id="alternate_venue_other" type="text" wire:model="alternate_venue_other" class="input input-bordered w-full"
                                placeholder="Enter alternate venue name..." required />
                            @error('alternate_venue_other') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                    @endif
                </div>
            </div>
        </x-mary-card>

        {{-- Section: Participants --}}
        <x-mary-card shadow class="bg-white dark:bg-base-200 border-none">
            <div class="space-y-5">
                <div class="pb-3 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-user-group" class="w-4 h-4" />
                            Participants
                        </span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="plv_participants" class="block text-sm font-medium text-base-content/70 mb-1">PLV Participants <span class="text-red-500">*</span></label>
                        <input id="plv_participants" type="number" wire:model="plv_participants" class="input input-bordered w-full"
                            min="1" placeholder="0" required />
                        @error('plv_participants') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="external_participants" class="block text-sm font-medium text-base-content/70 mb-1">External Participants</label>
                        <input id="external_participants" type="number" wire:model="external_participants" class="input input-bordered w-full"
                            min="0" placeholder="0" />
                        @error('external_participants') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </x-mary-card>

        {{-- Section: Budget --}}
        <x-mary-card shadow class="bg-white dark:bg-base-200 border-none">
            <div class="space-y-5">
                <div class="pb-3 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-banknotes" class="w-4 h-4" />
                            Budget
                        </span>
                    </label>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="estimated_budget" class="block text-sm font-medium text-base-content/70 mb-1">Estimated Budget (PHP)</label>
                        <input id="estimated_budget" type="number" wire:model="estimated_budget" class="input input-bordered w-full"
                            step="0.01" min="0" placeholder="0.00" />
                        @error('estimated_budget') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="fund_source_id" class="block text-sm font-medium text-base-content/70 mb-1">Fund Source</label>
                        <select id="fund_source_id" wire:model="fund_source_id" class="select select-bordered w-full">
                            <option value="">Select fund source...</option>
                            @foreach ($this->fundSources as $fs)
                                <option value="{{ $fs->source_id }}">{{ $fs->source_name }}</option>
                            @endforeach
                        </select>
                        @error('fund_source_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="budget_breakdown" class="block text-sm font-medium text-base-content/70 mb-1">{{ (int) $fund_source_id === 1 ? 'Budget Proposal Breakdown' : 'Request Details' }}</label>
                        <textarea id="budget_breakdown" wire:model="budget_breakdown" class="textarea textarea-bordered w-full" rows="2"
                            placeholder="Itemize the budget..."></textarea>
                        @error('budget_breakdown') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </x-mary-card>

        {{-- Section: Additional Information --}}
        <x-mary-card shadow class="bg-white dark:bg-base-200 border-none">
            <div class="space-y-5">
                <div class="pb-3 border-b border-slate-200 dark:border-base-300">
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200">
                        <span class="flex items-center gap-2">
                            <x-mary-icon name="o-clipboard-document-list" class="w-4 h-4" />
                            Additional Information
                        </span>
                    </label>
                </div>

                <div class="space-y-4">
                    <div>
                        <label for="special_requirements" class="block text-sm font-medium text-base-content/70 mb-1">Special Requirements</label>
                        <textarea id="special_requirements" wire:model="special_requirements" class="textarea textarea-bordered w-full" rows="2"
                            placeholder="Any special requirements..."></textarea>
                        @error('special_requirements') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="additional_notes" class="block text-sm font-medium text-base-content/70 mb-1">Additional Notes</label>
                        <textarea id="additional_notes" wire:model="additional_notes" class="textarea textarea-bordered w-full" rows="2"
                            placeholder="Any additional notes..."></textarea>
                        @error('additional_notes') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        </x-mary-card>

        {{-- Form Actions --}}
        <div class="flex flex-col sm:flex-row justify-end gap-3 pt-2">
            <x-mary-button label="Cancel" wire:click="cancel" class="btn-ghost" icon="o-x-mark" />
            <x-mary-button type="submit" label="{{ $isEditing ? 'Save Changes' : 'Create Ticket' }}"
                class="btn-primary" spinner="save"
                icon="{{ $isEditing ? 'o-check' : 'o-plus' }}" />
        </div>
    </form>
</div>
