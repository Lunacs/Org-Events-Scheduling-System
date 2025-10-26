<div>
    {{-- Header --}}
    <div class="mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-3xl font-bold text-base-content">Communication & Notifications</h1>
                    <p class="text-base-content/70 mt-1">Send messages and notifications to Student Organizations</p>
                </div>
                <x-mary-button wire:click="openComposeModal" class="btn-primary" icon="o-plus">
                    Compose Message
                </x-mary-button>
            </div>
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-base-100 rounded-box shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
            <div class="flex items-center gap-4">
                <div class="bg-primary/10 p-3 rounded-full">
                    <x-mary-icon name="o-megaphone" class="w-6 h-6 text-primary" />
                </div>
                <div>
                    <h3 class="font-semibold">Broadcast Announcement</h3>
                    <p class="text-sm text-base-content/70">Send to all organizations</p>
                </div>
            </div>
        </div>

        <div class="bg-base-100 rounded-box shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
            <div class="flex items-center gap-4">
                <div class="bg-warning/10 p-3 rounded-full">
                    <x-mary-icon name="o-exclamation-triangle" class="w-6 h-6 text-warning" />
                </div>
                <div>
                    <h3 class="font-semibold">Urgent Notice</h3>
                    <p class="text-sm text-base-content/70">High priority message</p>
                </div>
            </div>
        </div>

        <div class="bg-base-100 rounded-box shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
            <div class="flex items-center gap-4">
                <div class="bg-info/10 p-3 rounded-full">
                    <x-mary-icon name="o-information-circle" class="w-6 h-6 text-info" />
                </div>
                <div>
                    <h3 class="font-semibold">Event Clarification</h3>
                    <p class="text-sm text-base-content/70">Request additional info</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Communication History --}}
    <div class="bg-base-100 rounded-box shadow-lg">
        <div class="p-6 border-b border-base-300">
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold">Recent Communications</h2>
                <div class="flex gap-2">
                    <x-mary-input wire:model.live.debounce.300ms="search" placeholder="Search messages..."
                        icon="o-magnifying-glass" class="input-sm" />
                    <x-mary-select wire:model.live="typeFilter" placeholder="Type" :options="[
                        ['id' => '', 'name' => 'All Types'],
                        ['id' => 'announcement', 'name' => 'Announcement'],
                        ['id' => 'clarification', 'name' => 'Clarification'],
                        ['id' => 'urgent', 'name' => 'Urgent'],
                        ['id' => 'reminder', 'name' => 'Reminder'],
                    ]" option-value="id"
                        option-label="name" class="select-sm" />
                </div>
            </div>
        </div>

        <div class="p-6">
            {{-- Communication List --}}
            <div class="space-y-4">
                {{-- Sample communication items (replace with actual data) --}}
                <div class="border border-base-300 rounded-lg p-4">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="bg-primary/10 p-2 rounded-full">
                                <x-mary-icon name="o-megaphone" class="w-4 h-4 text-primary" />
                            </div>
                            <div>
                                <h3 class="font-semibold">Event Guidelines Update</h3>
                                <p class="text-sm text-base-content/70">Sent to all organizations • 2 hours ago</p>
                            </div>
                        </div>
                        <x-mary-badge value="Broadcast" class="badge-primary" />
                    </div>
                    <p class="text-sm text-base-content/80 mb-3">Updated guidelines for event submission requirements
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
                                <x-mary-icon name="o-exclamation-triangle" class="w-4 h-4 text-warning" />
                            </div>
                            <div>
                                <h3 class="font-semibold">Missing Documents Request</h3>
                                <p class="text-sm text-base-content/70">Sent to Student Council • 1 day ago</p>
                            </div>
                        </div>
                        <x-mary-badge value="Clarification" class="badge-warning" />
                    </div>
                    <p class="text-sm text-base-content/80 mb-3">Please provide the missing safety protocol documents
                        for your upcoming event...</p>
                    <div class="flex items-center gap-4 text-xs text-base-content/60">
                        <span>✓ Delivered</span>
                        <span>✓ Read</span>
                        <span class="text-success">✓ Responded</span>
                    </div>
                </div>

                {{-- Empty state when no communications --}}
                <div class="text-center py-8">
                    <x-mary-icon name="o-chat-bubble-left-right" class="w-12 h-12 text-base-content/30 mx-auto mb-2" />
                    <p class="text-base-content/70">No recent communications</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Compose Message Modal --}}
    <x-mary-modal wire:model="showComposeModal" title="Compose Message" class="modal-lg">
        <div class="space-y-6">
            {{-- Recipient Selection --}}
            <div>
                <x-mary-radio wire:model.live="recipientType" label="Send to:" :options="[
                    ['id' => 'organization', 'name' => 'Specific Organization'],
                    ['id' => 'individual', 'name' => 'Individual User'],
                    ['id' => 'all', 'name' => 'All Organizations (Broadcast)'],
                ]" option-value="id"
                    option-label="name" />
            </div>

            {{-- Organization Selection --}}
            @if ($recipientType === 'organization')
                <x-mary-select wire:model="selectedOrganization" label="Select Organization"
                    placeholder="Choose an organization..." :options="$organizations" option-value="id" option-label="name"
                    searchable />
            @endif

            {{-- Individual User Selection --}}
            @if ($recipientType === 'individual')
                <x-mary-select wire:model="selectedUser" label="Select User" placeholder="Choose a user..."
                    :options="$users" option-value="id" option-label="name" searchable />
            @endif

            {{-- Message Details --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <x-mary-input wire:model="subject" label="Subject" placeholder="Enter message subject..." />

                <x-mary-select wire:model="priority" label="Priority" :options="[
                    ['id' => 'low', 'name' => 'Low'],
                    ['id' => 'normal', 'name' => 'Normal'],
                    ['id' => 'high', 'name' => 'High'],
                    ['id' => 'urgent', 'name' => 'Urgent'],
                ]" option-value="id"
                    option-label="name" />
            </div>

            <x-mary-textarea wire:model="message" label="Message" placeholder="Type your message here..."
                rows="8" />

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

        <x-slot:actions>
            <x-mary-button wire:click="closeComposeModal" class="btn-ghost">Cancel</x-mary-button>
            <x-mary-button wire:click="sendMessage" class="btn-primary" :disabled="!$subject || !$message || ($recipientType === 'organization' && !$selectedOrganization) || ($recipientType === 'individual' && !$selectedUser)">
                <x-mary-icon name="o-paper-airplane" class="w-4 h-4 mr-2" />
                Send Message
            </x-mary-button>
        </x-slot:actions>
    </x-mary-modal>

    {{-- Success Message --}}
    @if (session()->has('message'))
        <x-mary-toast type="success" title="Success!" description="{{ session('message') }}" />
    @endif
</div>
