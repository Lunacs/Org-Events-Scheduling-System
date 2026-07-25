<div>
    <x-ui.card shadow class="border-none bg-slate-50/50 dark:bg-base-100/50">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-lg sm:text-xl font-bold text-slate-800 dark:text-slate-100">Student Organizations</h2>
                <p class="text-[10px] sm:text-xs text-slate-500 dark:text-slate-400 mt-0.5">Authorized campus groups and
                    clubs</p>
            </div>
            <x-ui.button icon="o-plus" label="Register Org" class="btn-primary btn-sm shadow-sm w-full sm:w-auto"
                wire:click="$set('addOrgModalOpen', true)" />
        </div>

        <div class="space-y-6">
            {{-- Academic Organizations Section --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <x-ui.icon name="o-academic-cap" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                    <h3 class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">Academic
                        Organizations</h3>
                    {{-- <span
                        class="text-[10px] sm:text-xs px-1.5 py-0.5 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-300 rounded-full font-medium">{{ count($academicOrgs) }}</span> --}}
                    <span
                        class="text-[10px] sm:text-xs px-1.5 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full font-medium">{{ count($academicOrgs) }}</span>
                </div>

                @if (count($academicOrgs) > 0)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                        @foreach ($academicOrgs as $organization)
                            @include('livewire.superadmin.system-settings.partials.organization-card', [
                                'organization' => $organization,
                                'showCourse' => true,
                            ])
                        @endforeach
                    </div>
                @else
                    <div
                        class="text-center py-8 bg-white dark:bg-base-200 border border-dashed border-slate-300 dark:border-base-300 rounded-xl">
                        <x-ui.icon name="o-academic-cap"
                            class="w-10 h-10 mx-auto mb-2 text-slate-300 dark:text-slate-600" />
                        <p class="text-slate-500 dark:text-slate-400 font-medium text-sm">No academic organizations</p>
                        <p class="text-slate-400 dark:text-slate-500 text-xs mt-1">Organizations linked to specific
                            courses will appear here.</p>
                    </div>
                @endif
            </div>

            {{-- Non-Academic Organizations Section --}}
            <div>
                <div class="flex items-center gap-2 mb-3">
                    <x-ui.icon name="o-globe-alt" class="w-5 h-5 text-indigo-600 dark:text-indigo-400" />
                    <h3 class="text-sm sm:text-base font-semibold text-slate-700 dark:text-slate-200">Non-Academic
                        Organizations</h3>
                    <span
                        class="text-[10px] sm:text-xs px-1.5 py-0.5 bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-full font-medium">{{ count($nonAcademicOrgs) }}</span>
                </div>

                @if (count($nonAcademicOrgs) > 0)
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 sm:gap-4">
                        @foreach ($nonAcademicOrgs as $organization)
                            @include('livewire.superadmin.system-settings.partials.organization-card', [
                                'organization' => $organization,
                                'showCourse' => false,
                            ])
                        @endforeach
                    </div>
                @else
                    <div
                        class="text-center py-8 bg-white dark:bg-base-200 border border-dashed border-slate-300 dark:border-base-300 rounded-xl">
                        <x-ui.icon name="o-globe-alt"
                            class="w-10 h-10 mx-auto mb-2 text-slate-300 dark:text-slate-600" />
                        <p class="text-slate-500 dark:text-slate-400 font-medium text-sm">No non-academic organizations
                        </p>
                        <p class="text-slate-400 dark:text-slate-500 text-xs mt-1">Institutional organizations (e.g.,
                            Red Cross Youth) will appear here.</p>
                    </div>
                @endif
            </div>
        </div>
    </x-ui.card>

    {{-- Add Student Organization Modal --}}
    <x-ui.modal-dialog wire:model="addOrgModalOpen" title="Add Student Organization"
        subtitle="Create a new student organization" separator with-close-button close-on-escape>
        <form wire:submit.prevent="addOrganization" class="space-y-4">
            <x-ui.input wire:model="newOrgCode" label="Organization Code" placeholder="e.g., VITS" icon="o-hashtag" />
            <x-ui.input wire:model="newOrgName" label="Organization Name" placeholder="Enter organization name"
                icon="o-user-group" />
            <x-ui.select wire:model="newCourseId" :options="$allCourses
                ->map(function ($course) {
                    return ['id' => $course->course_id, 'name' => $course->course_name];
                })
                ->toArray()" option-value="id" option-label="name"
                label="Course (Optional)" placeholder="Select course (optional)" icon="o-academic-cap" />
            <x-ui.input wire:model="newAdviserName" label="Adviser Name" placeholder="Enter adviser name"
                icon="o-user" />
            <x-ui.select wire:model="newOrgStatus" :options="[
                ['id' => 'active', 'name' => 'Active'],
                ['id' => 'inactive', 'name' => 'Inactive'],
                ['id' => 'suspended', 'name' => 'Suspended'],
            ]" option-value="id" option-label="name"
                label="Status" placeholder="Select status" icon="o-shield-check" />

            {{-- Logo Upload with Preview --}}
            <div class="space-y-2">
                <x-ui.file wire:model="newOrgLogo" label="Organization Logo (Optional)"
                    hint="Max 10MB. Images auto-compressed to WebP format." accept=".jpg,.jpeg,.png,.gif,.svg,.webp" />

                {{-- Loading indicator --}}
                <div wire:loading wire:target="newOrgLogo" class="flex items-center gap-2 text-sm text-base-content/70">
                    <span class="loading loading-spinner loading-sm"></span>
                    <span>Processing image...</span>
                </div>

                {{-- Preview --}}
                @if ($newOrgLogo)
                    <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                        <img src="{{ $newOrgLogo->temporaryUrl() }}" alt="Logo preview"
                            class="w-16 h-16 object-cover rounded-lg border border-base-300" />
                        <div class="flex-1">
                            <p class="text-sm font-medium text-success">Logo ready to upload</p>
                            <p class="text-xs text-base-content/60">Will be compressed to WebP format</p>
                        </div>
                        <x-ui.button icon="o-x-mark" class="btn-ghost btn-sm" wire:click="$set('newOrgLogo', null)" />
                    </div>
                @endif
            </div>
        </form>

        <x-slot:actions>
            <x-ui.button label="Cancel" @click="$wire.addOrgModalOpen = false; $wire.resetAddOrgForm()" />
            <x-ui.button label="Create Organization" wire:click="addOrganization" class="btn-primary"
                spinner="addOrganization" />
        </x-slot:actions>
    </x-ui.modal-dialog>

    {{-- Edit Student Organization Modal --}}
    @if ($editingOrgId)
        <x-ui.modal-dialog wire:model="editOrgModalOpen" title="Edit Student Organization"
            subtitle="Update organization information" separator with-close-button close-on-escape>
            <form wire:submit.prevent="editOrganization" class="space-y-4">
                <x-ui.input wire:model="orgCode" label="Organization Code" placeholder="e.g., CITE-CSC"
                    icon="o-hashtag" />
                <x-ui.input wire:model="orgName" label="Organization Name" placeholder="Enter organization name"
                    icon="o-user-group" />
                <x-ui.select wire:model="courseId" :options="$allCourses
                    ->map(function ($course) {
                        return ['id' => $course->course_id, 'name' => $course->course_name];
                    })
                    ->toArray()" option-value="id" option-label="name"
                    label="Course (Optional)" placeholder="Select course (optional)" icon="o-academic-cap" />
                <x-ui.input wire:model="adviserName" label="Adviser Name" placeholder="Enter adviser name"
                    icon="o-user" />
                <x-ui.select wire:model="orgStatus" :options="[
                    ['id' => 'active', 'name' => 'Active'],
                    ['id' => 'inactive', 'name' => 'Inactive'],
                    ['id' => 'suspended', 'name' => 'Suspended'],
                ]" option-value="id" option-label="name"
                    label="Status" placeholder="Select status" icon="o-shield-check" />

                {{-- Current Logo Section --}}
                @if ($currentOrgLogo)
                    <div class="space-y-2">
                        <label class="text-sm font-medium text-base-content/70">Current Logo</label>
                        <div class="flex items-center gap-3 p-3 bg-base-200 rounded-lg">
                            <img src="{{ $currentOrgLogoUrl }}" alt="Current Logo"
                                class="w-16 h-16 object-cover rounded-lg border border-base-300" />
                            <div class="flex-1">
                                <p class="text-sm font-medium">Organization Logo</p>
                                <p class="text-xs text-base-content/60">Click delete to remove</p>
                            </div>
                            <x-ui.button icon="o-trash" class="btn-ghost btn-error btn-sm hover:"
                                wire:click="deleteCurrentOrgLogo" spinner="deleteCurrentOrgLogo" />
                        </div>
                    </div>
                @endif

                {{-- Logo Upload with Preview --}}
                <div class="space-y-2">
                    <x-ui.file wire:model="orgLogo"
                        label="{{ $currentOrgLogo ? 'Replace Logo' : 'Upload Logo (Optional)' }}"
                        hint="Max 10MB. Images auto-compressed to WebP format."
                        accept=".jpg,.jpeg,.png,.gif,.svg,.webp" />

                    {{-- Loading indicator --}}
                    <div wire:loading wire:target="orgLogo"
                        class="flex items-center gap-2 text-sm text-base-content/70">
                        <span class="loading loading-spinner loading-sm"></span>
                        <span>Processing image...</span>
                    </div>

                    {{-- New Logo Preview --}}
                    @if ($orgLogo)
                        <div class="flex items-center gap-3 p-3 bg-success/10 border border-success/30 rounded-lg">
                            <img src="{{ $orgLogo->temporaryUrl() }}" alt="New logo preview"
                                class="w-16 h-16 object-cover rounded-lg border border-success/50" />
                            <div class="flex-1">
                                <p class="text-sm font-medium text-success">New logo ready</p>
                                <p class="text-xs text-base-content/60">Will replace current logo when saved</p>
                            </div>
                            <x-ui.button icon="o-x-mark" class="btn-ghost btn-sm"
                                wire:click="$set('orgLogo', null)" />
                        </div>
                    @endif
                </div>
            </form>

            <x-slot:actions>
                <x-ui.button label="Cancel" @click="$wire.editOrgModalOpen = false; $wire.resetEditOrgForm()" />
                <x-ui.button label="Update" wire:click="editOrganization" class="btn-primary"
                    spinner="editOrganization" />
            </x-slot:actions>
        </x-ui.modal-dialog>
    @endif

    {{-- Delete Student Organization Confirmation Modal --}}
    @if ($deletingOrgName)
        <x-ui.modal-dialog wire:model="deleteOrgModalOpen" title="Delete Student Organization Confirmation"
            subtitle="This action cannot be undone" separator with-close-button close-on-escape>
            <div class="space-y-4">
                <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 dark:text-red-400 mr-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        <p class="text-red-800 dark:text-red-200 font-medium">Warning: This action is permanent</p>
                    </div>
                </div>

                <p class="text-gray-700 dark:text-slate-300">
                    You are about to delete <strong class="dark:text-white">{{ $deletingOrgName }}</strong>. This will
                    permanently remove all
                    data
                    related to this organization.
                </p>

                @if ($hasAssociatedUsers)
                    <div class="alert alert-error">
                        <x-ui.icon name="o-x-circle" class="w-6 h-6" />
                        <span>
                            This organization cannot be deleted because it has associated users.
                        </span>
                    </div>
                @endif
            </div>

            <x-slot:actions>
                <x-ui.button label="Cancel"
                    @click="$wire.deleteOrgModalOpen = false; $wire.resetDeleteOrgModal()" />
                <x-ui.button label="Delete Organization" wire:click="confirmDeleteOrg" class="btn-error"
                    :disabled="$hasAssociatedUsers" spinner="confirmDeleteOrg" />
            </x-slot:actions>
        </x-ui.modal-dialog>
    @endif
</div>
