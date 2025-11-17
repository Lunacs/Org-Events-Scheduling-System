<div>
    <x-mary-card>
        <div class="flex justify-between mb-5">
            <h2 class="font-bold text-xl">Student Organizations</h2>
            <x-mary-button icon="o-plus" class="btn-accent"
                wire:click="$set('addOrgModalOpen', true)">Add</x-mary-button>
        </div>

        @if (count($organizations) > 0)
            <ul class="space-y-2">
                @foreach ($organizations as $organization)
                    <li class="flex items-center justify-between p-2 border rounded-lg">
                        <div>
                            <p class="font-medium">{{ $organization->org_name }}</p>
                            <p class="text-xs text-gray-500">{{ $organization->org_code }} •
                                {{ $organization->course->course_name ?? 'N/A' }}</p>
                        </div>
                        <div class="flex gap-1">
                            <x-mary-button size="xs" icon="o-pencil-square" class="btn-ghost"
                                wire:click="openEditOrgModal({{ $organization->org_id }})"
                                wire:loading.attr="disabled">
                            </x-mary-button>
                            <x-mary-button size="xs" icon="o-trash" class="btn-ghost text-red-600"
                                wire:click="openDeleteOrgModal({{ $organization->org_id }})"
                                wire:loading.attr="disabled">
                            </x-mary-button>
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="text-center py-4 text-gray-500">
                <x-mary-icon name="o-user-group" class="w-8 h-8 mx-auto mb-2" />
                <p>No student organizations found</p>
            </div>
        @endif
    </x-mary-card>

    {{-- Add Student Organization Modal --}}
    <x-mary-modal wire:model="addOrgModalOpen" title="Add Student Organization"
        subtitle="Create a new student organization" separator with-close-button close-on-escape>
        <form wire:submit.prevent="addOrganization" class="space-y-4">
            <x-mary-input wire:model="newOrgCode" label="Organization Code" placeholder="e.g., VITS"
                icon="o-hashtag" />
            <x-mary-input wire:model="newOrgName" label="Organization Name" placeholder="Enter organization name"
                icon="o-user-group" />
            <x-mary-select wire:model="newCourseId" :options="$allCourses
                ->map(function ($course) {
                    return ['id' => $course->course_id, 'name' => $course->course_name];
                })
                ->toArray()" option-value="id" option-label="name"
                label="Course (Optional)" placeholder="Select course (optional)" icon="o-academic-cap" />
            <x-mary-input wire:model="newAdviserName" label="Adviser Name" placeholder="Enter adviser name"
                icon="o-user" />
            <x-mary-select wire:model="newOrgStatus" :options="[
                ['id' => 'active', 'name' => 'Active'],
                ['id' => 'inactive', 'name' => 'Inactive'],
                ['id' => 'suspended', 'name' => 'Suspended'],
            ]" option-value="id" option-label="name"
                label="Status" placeholder="Select status" icon="o-shield-check" />
            <x-mary-file wire:model="newOrgLogo" label="Organization Logo (Optional)"
                hint="Max 2MB. Accepted: JPG, PNG, GIF, SVG, WebP" accept="image/*" />
        </form>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="$wire.addOrgModalOpen = false; $wire.resetAddOrgForm()" />
            <x-mary-button label="Create Organization" wire:click="addOrganization" class="btn-primary"
                spinner="addOrganization" />
        </x-slot:actions>
    </x-mary-modal>

    {{-- Edit Student Organization Modal --}}
    @if ($editingOrgId)
        <x-mary-modal wire:model="editOrgModalOpen" title="Edit Student Organization"
            subtitle="Update organization information" separator with-close-button close-on-escape>
            <form wire:submit.prevent="editOrganization" class="space-y-4">
                <x-mary-input wire:model="orgCode" label="Organization Code" placeholder="e.g., CITE-CSC"
                    icon="o-hashtag" />
                <x-mary-input wire:model="orgName" label="Organization Name" placeholder="Enter organization name"
                    icon="o-user-group" />
                <x-mary-select wire:model="courseId" :options="$allCourses
                    ->map(function ($course) {
                        return ['id' => $course->course_id, 'name' => $course->course_name];
                    })
                    ->toArray()" option-value="id" option-label="name"
                    label="Course (Optional)" placeholder="Select course (optional)" icon="o-academic-cap" />
                <x-mary-input wire:model="adviserName" label="Adviser Name" placeholder="Enter adviser name"
                    icon="o-user" />
                <x-mary-select wire:model="orgStatus" :options="[
                    ['id' => 'active', 'name' => 'Active'],
                    ['id' => 'inactive', 'name' => 'Inactive'],
                    ['id' => 'suspended', 'name' => 'Suspended'],
                ]" option-value="id" option-label="name"
                    label="Status" placeholder="Select status" icon="o-shield-check" />
                @if ($currentOrgLogo)
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-base-content/70">Current Logo</label>
                        <div class="flex items-center">
                            <img src="{{ asset('storage/' . $currentOrgLogo) }}" alt="Current Logo"
                                class="w-20 h-20 object-cover rounded-lg border border-base-300">
                            <x-mary-button icon="o-trash" class="btn-ghost text-red-600 ml-2"
                                wire:click="deleteCurrentOrgLogo" spinner />
                        </div>
                    </div>
                @endif
                <x-mary-file wire:model="orgLogo"
                    label="{{ $currentOrgLogo ? 'Replace Logo' : 'Upload Logo (Optional)' }}"
                    hint="Max 2MB. Accepted: JPG, PNG, GIF, SVG, WebP" accept="image/*" />
            </form>

            <x-slot:actions>
                <x-mary-button label="Cancel" @click="$wire.editOrgModalOpen = false; $wire.resetEditOrgForm()" />
                <x-mary-button label="Update" wire:click="editOrganization" class="btn-primary"
                    spinner="editOrganization" />
            </x-slot:actions>
        </x-mary-modal>
    @endif

    {{-- Delete Student Organization Confirmation Modal --}}
    @if ($deletingOrgName)
        <x-mary-modal wire:model="deleteOrgModalOpen" title="Delete Student Organization Confirmation"
            subtitle="This action cannot be undone" separator with-close-button close-on-escape>
            <div class="space-y-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        <p class="text-red-800 font-medium">Warning: This action is permanent</p>
                    </div>
                </div>

                <p class="text-gray-700">
                    You are about to delete <strong>{{ $deletingOrgName }}</strong>. This will permanently remove all data
                    related to this organization.
                </p>

                @if ($hasAssociatedUsers)
                    <div class="alert alert-error">
                        <x-mary-icon name="o-x-circle" class="w-6 h-6" />
                        <span>
                            This organization cannot be deleted because it has associated users.
                        </span>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" @click="$wire.deleteOrgModalOpen = false; $wire.resetDeleteOrgModal()" />
                <x-mary-button label="Delete Organization" wire:click="confirmDeleteOrg" class="btn-error"
                    :disabled="$hasAssociatedUsers" spinner="confirmDeleteOrg" />
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>

