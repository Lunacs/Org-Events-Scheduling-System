<div x-data="{ firstLoad: true }" x-init="$nextTick(() => firstLoad = false)">

    {{-- Skeleton Loading State (First Load Only) --}}
    <div x-show="firstLoad" x-cloak>
        @include('livewire.osa.placeholders.communication')
    </div>

    {{-- Actual Content --}}
    <div x-show="!firstLoad" x-cloak x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100">

        {{-- Header --}}
        <div class="mb-8">
            <x-ui.card>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-base-content">Communication & Notifications</h1>
                        <p class="text-base-content/70 mt-1">Send messages and notifications to Student Organizations
                        </p>
                    </div>
                    <x-ui.button wire:click="openComposeModal" class="btn-primary" icon="o-plus">
                        Compose Message
                    </x-ui.button>
                </div>
            </x-ui.card>
        </div>

        {{-- Quick Actions --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-base-100 border border-base-300 rounded-box shadow-sm hover:shadow-md transition-shadow p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-primary/10 p-3 rounded-full">
                        <x-ui.icon name="o-megaphone" class="w-6 h-6 text-primary" />
                    </div>
                    <div>
                        <h3 class="font-semibold">Broadcast Announcement</h3>
                        <p class="text-sm text-base-content/70">Send to all organizations</p>
                    </div>
                </div>
            </div>

            <div class="bg-base-100 border border-base-300 rounded-box shadow-sm hover:shadow-md transition-shadow p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-warning/10 p-3 rounded-full">
                        <x-ui.icon name="o-exclamation-triangle" class="w-6 h-6 text-warning" />
                    </div>
                    <div>
                        <h3 class="font-semibold">Urgent Notice</h3>
                        <p class="text-sm text-base-content/70">High priority message</p>
                    </div>
                </div>
            </div>

            <div class="bg-base-100 border border-base-300 rounded-box shadow-sm hover:shadow-md transition-shadow p-6">
                <div class="flex items-center gap-4">
                    <div class="bg-info/10 p-3 rounded-full">
                        <x-ui.icon name="o-information-circle" class="w-6 h-6 text-info" />
                    </div>
                    <div>
                        <h3 class="font-semibold">Event Clarification</h3>
                        <p class="text-sm text-base-content/70">Request additional info</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Communication History --}}
        <x-ui.card title="Recent Communications">
            <x-slot:menu>
                <x-ui.input wire:model.live.debounce.300ms="search" placeholder="Search messages..."
                    icon="o-magnifying-glass" class="input-sm" />
                <x-ui.select wire:model.live="typeFilter" placeholder="Type" :options="[
                    ['id' => '', 'name' => 'All Types'],
                    ['id' => 'announcement', 'name' => 'Announcement'],
                    ['id' => 'clarification', 'name' => 'Clarification'],
                    ['id' => 'urgent', 'name' => 'Urgent'],
                    ['id' => 'reminder', 'name' => 'Reminder'],
                ]" option-value="id"
                    option-label="name" class="select-sm" />
            </x-slot:menu>

            {{-- Communication List --}}
            <div class="space-y-4">
                {{-- Sample communication items (replace with actual data) --}}
                <div class="border border-base-300 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="bg-primary/10 p-2 rounded-full">
                                <x-ui.icon name="o-megaphone" class="w-4 h-4 text-primary" />
                            </div>
                            <div>
                                <h3 class="font-semibold">Event Guidelines Update</h3>
                                <p class="text-sm text-base-content/70">Sent to all organizations • 2 hours ago</p>
                            </div>
                        </div>
                        <x-ui.badge value="Broadcast" class="badge-primary" />
                    </div>
                    <p class="text-sm text-base-content/80 mb-3">Updated guidelines for event submission
                        requirements
                        and approval process...</p>
                    <div class="flex items-center gap-4 text-xs text-base-content/60">
                        <span>✓ Delivered to 15 organizations</span>
                        <span>12 read receipts</span>
                    </div>
                </div>

                <div class="border border-base-300 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="bg-warning/10 p-2 rounded-full">
                                <x-ui.icon name="o-exclamation-triangle" class="w-4 h-4 text-warning" />
                            </div>
                            <div>
                                <h3 class="font-semibold">Missing Documents Request</h3>
                                <p class="text-sm text-base-content/70">Sent to Student Council • 1 day ago</p>
                            </div>
                        </div>
                        <x-ui.badge value="Clarification" class="badge-warning" />
                    </div>
                    <p class="text-sm text-base-content/80 mb-3">Please provide the missing safety protocol
                        documents
                        for your upcoming event...</p>
                    <div class="flex items-center gap-4 text-xs text-base-content/60">
                        <span>✓ Delivered</span>
                        <span>✓ Read</span>
                        <span class="text-success">✓ Responded</span>
                    </div>
                </div>

                {{-- Empty state when no communications --}}
                <div class="text-center py-8">
                    <x-ui.icon name="o-chat-bubble-left-right"
                        class="w-12 h-12 text-base-content/30 mx-auto mb-2" />
                    <p class="text-base-content/70">No recent communications</p>
                </div>
            </div>
        </x-ui.card>

        {{-- Compose Message Modal --}}
        @php
            $recipientTypeOptions = [
                ['id' => 'organization', 'name' => 'Specific Organization'],
                ['id' => 'individual', 'name' => 'Individual User'],
                ['id' => 'all', 'name' => 'All Organizations (Broadcast)'],
            ];
        @endphp
        <div x-data="{ show: @entangle('showComposeModal') }" class="modal" :class="{ 'modal-open': show }" role="dialog" aria-modal="true"
            aria-label="Compose Message">
            <div class="modal-box w-11/12 max-w-3xl">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold">Compose Message</h3>
                    <button type="button" class="btn btn-sm btn-circle btn-ghost" wire:click="closeComposeModal"
                        aria-label="Close">
                        <x-ui.icon name="o-x-mark" class="w-4 h-4" />
                    </button>
                </div>

                <div class="space-y-6">
                    {{-- Recipient Selection --}}
                    <fieldset>
                        <legend class="block text-sm font-medium mb-2">Send to:</legend>
                        <div class="flex flex-col sm:flex-row sm:flex-wrap gap-3">
                            @foreach ($recipientTypeOptions as $option)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="radio" class="radio radio-primary" wire:model.live="recipientType"
                                        value="{{ $option['id'] }}" name="recipientType" />
                                    <span>{{ $option['name'] }}</span>
                                </label>
                            @endforeach
                        </div>
                    </fieldset>

                    {{-- Organization Selection --}}
                    @if ($recipientType === 'organization')
                        <x-ui.select wire:model="selectedOrganization" label="Select Organization"
                            placeholder="Choose an organization..." :options="$organizations" option-value="id"
                            option-label="name" />
                    @endif

                    {{-- Individual User Selection --}}
                    @if ($recipientType === 'individual')
                        <x-ui.select wire:model="selectedUser" label="Select User" placeholder="Choose a user..."
                            :options="$users" option-value="id" option-label="name" />
                    @endif

                    {{-- Message Details --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <x-ui.input wire:model="subject" label="Subject" placeholder="Enter message subject..." />

                        <x-ui.select wire:model="priority" label="Priority" :options="[
                            ['id' => 'low', 'name' => 'Low'],
                            ['id' => 'normal', 'name' => 'Normal'],
                            ['id' => 'high', 'name' => 'High'],
                            ['id' => 'urgent', 'name' => 'Urgent'],
                        ]" option-value="id"
                            option-label="name" />
                    </div>

                    <div>
                        <label for="compose-message" class="block text-sm font-medium mb-1">Message</label>
                        <textarea id="compose-message" wire:model="message" rows="8" placeholder="Type your message here..."
                            class="textarea textarea-bordered w-full"></textarea>
                        @error('message')
                            <x-ui.input-error :messages="$message" class="mt-1" />
                        @enderror
                    </div>

                    {{-- Message Preview --}}
                    @if ($subject || $message)
                        <div class="bg-base-200 rounded-lg p-4">
                            <h4 class="font-semibold mb-2">Preview:</h4>
                            @if ($subject)
                                <div class="font-medium mb-2">{{ $subject }}</div>
                            @endif
                            @if ($message)
                                <div class="text-sm text-base-content/80">{{ $message }}</div>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="modal-action">
                    <x-ui.button wire:click="closeComposeModal" class="btn-ghost">Cancel</x-ui.button>
                    <x-ui.button wire:click="sendMessage" class="btn-primary" :disabled="!$subject || !$message || ($recipientType === 'organization' && !$selectedOrganization) || ($recipientType === 'individual' && !$selectedUser)">
                        <x-ui.icon name="o-paper-airplane" class="w-4 h-4 mr-2" />
                        Send Message
                    </x-ui.button>
                </div>
            </div>

            {{-- Backdrop: click outside to close --}}
            <button type="button" class="modal-backdrop" wire:click="closeComposeModal"
                aria-label="Close">close</button>
        </div>

        {{-- Success messages surface through the layout-level <x-ui.toast /> host. --}}
    </div>
</div>
