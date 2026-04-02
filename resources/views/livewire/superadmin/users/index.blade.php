<div x-data="{
    openCreateUserDrawer() {
            $wire.resetCreateForm();
            document.getElementById('create-user-drawer-toggle').checked = true;
        },
        closeCreateUserDrawer() {
            document.getElementById('create-user-drawer-toggle').checked = false;
            $wire.resetCreateForm();
        },
        openEditUserDrawer(userId) {
            $wire.loadEditForm(userId);
            document.getElementById('edit-user-drawer-toggle').checked = true;
        },
        closeEditUserDrawer() {
            document.getElementById('edit-user-drawer-toggle').checked = false;
            $wire.resetEditForm();
        }
}"
    @user-drawer-close.window="document.getElementById('create-user-drawer-toggle').checked = false; document.getElementById('edit-user-drawer-toggle').checked = false">
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold font-heading">User Management</h1>
            <x-mary-button icon="o-plus" class="btn-accent" @click="openCreateUserDrawer()">
                Create User
            </x-mary-button>
        </div>

        <!-- Search and Filter Section -->
        <x-mary-card>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <x-mary-input label="Search Users" wire:model.live.debounce.300ms="search"
                    placeholder="Search by name or email..." icon="o-magnifying-glass" />

                <x-mary-select label="Filter by Role" wire:model.live="roleFilter" :options="[
                    ['id' => 'all', 'name' => 'All Roles'],
                    ['id' => 'superadmin', 'name' => 'Super Admin'],
                    ['id' => 'osa', 'name' => 'OSA Staff'],
                    ['id' => 'gso', 'name' => 'GSO Staff'],
                    ['id' => 'student-org', 'name' => 'Student Organization'],
                ]" option-value="id"
                    option-label="name" />

                <div class="flex items-end">
                    @if ($this->hasActiveFilters())
                        <x-mary-button icon="o-x-mark" class="btn-outline" wire:click="clearFilters" wire:transition>
                            Clear Filters
                        </x-mary-button>
                    @endif

                </div>
            </div>
        </x-mary-card>

        <x-mary-card shadow class="relative">
            <div wire:loading.class="opacity-50 pointer-events-none" class="transition-opacity duration-200">
                <x-mary-table :headers="$headers" :rows="$users" :sort-by="$sortBy" :per-page-values="[10, 25, 50]"
                    class="rounded-lg">
                    @scope('cell_role_id', $user)
                        @php
                            $roleString = $user->role?->role_name ?? 'unknown';
                        @endphp
                        <x-mary-badge :value="$this->getRoleDisplayName($roleString)" :class="match ($roleString) {
                            'superadmin' => 'badge-error text-base-200 text-md whitespace-nowrap dark:text-white',
                            'osa' => 'badge-primary text-base-200 dark:text-white',
                            'gso' => 'badge-info text-base-200 dark:text-white',
                            'student-org' => 'badge-success text-base-200 whitespace-nowrap dark:text-white',
                            default => 'badge-ghost text-base-200 dark:text-white',
                        }" />
                    @endscope

                    @scope('cell_email_verified_at', $user)
                        @if ($user->email_verified_at)
                            <x-mary-badge value="Verified" class="badge-success text-base-200 dark:text-white" />
                        @else
                            <x-mary-badge value="Unverified" class="badge-warning text-base-200 dark:text-white" />
                        @endif
                    @endscope

                    @scope('cell_organization', $user)
                        @if ($user->studentOrganization)
                            <span class="text-sm">{{ $user->studentOrganization->org_name }}</span>
                        @elseif($user->office)
                            <span class="text-sm">{{ $user->office->office_name }}</span>
                        @else
                            <span class="text-sm text-gray-500">N/A</span>
                        @endif
                    @endscope

                    @scope('cell_actions', $user)
                        <div class="flex space-x-1">
                            <x-mary-button size="xs" icon="o-pencil-square" class="btn-ghost"
                                @click="openEditUserDrawer({{ $user->user_id }})">
                                Edit
                            </x-mary-button>
                            @if (!$user->isSuperAdmin())
                                <x-mary-button size="xs" icon="o-trash" class="btn-ghost text-red-600"
                                    wire:click="openDeleteModal({{ $user->user_id }}, '{{ addslashes($user->name) }}')">
                                </x-mary-button>
                            @endif
                        </div>
                    @endscope

                    <x-slot:empty>
                        <div class="flex flex-col items-center justify-center py-12 text-center">
                            <x-mary-icon name="o-users" class="w-16 h-16 text-base-content/20 mb-4" />
                            <h3 class="text-xl font-bold text-base-content/70">No users found</h3>
                            <p class="text-base-content/50 max-w-sm mx-auto mt-2">
                                @if ($this->hasActiveFilters())
                                    We couldn't find any users matching "<span
                                        class="font-semibold text-base-content/80">{{ $search }}</span>" or your
                                    selected
                                    role.
                                @else
                                    There are no users registered in the system yet.
                                @endif
                            </p>
                            @if ($this->hasActiveFilters())
                                <x-mary-button label="Clear all filters" icon="o-x-mark" wire:click="clearFilters"
                                    class="btn-ghost btn-sm mt-6 text-accent" wire:transition />
                            @endif
                        </div>
                    </x-slot:empty>
                </x-mary-table>
            </div>

            {{-- Custom Pagination --}}
            @if ($users->hasPages())
                <x-tickets.ticket-pagination :tickets="$users" label="users" />
            @endif
        </x-mary-card>
    </div>

    {{-- Create User Drawer --}}
    <div class="drawer drawer-end z-50 rounded-l-2xl">
        <input id="create-user-drawer-toggle" type="checkbox" class="drawer-toggle" />
        <div class="drawer-side">
            <label for="create-user-drawer-toggle" aria-label="close sidebar" class="drawer-overlay"></label>
            <div class="bg-base-100 min-h-full w-11/12 lg:w-1/2 p-6 flex flex-col rounded-l-2xl">
                {{-- Header --}}
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-base-300">
                    <div>
                        <h2 class="text-2xl font-bold text-base-content">Create New User</h2>
                        <p class="text-sm text-base-content/60 mt-1">Add a new user to the system</p>
                    </div>
                    <button @click="closeCreateUserDrawer()" class="btn btn-sm btn-circle btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Form Content --}}
                <div class="flex-1 overflow-y-auto">
                    <form wire:submit="createUser" class="space-y-4 p-1">
                        <x-mary-input label="Full Name" wire:model.live.debounce.300ms="createForm.name"
                            placeholder="John Dela Cruz" icon="o-user" required />

                        <x-mary-input label="Email Address" wire:model.live.blur="createForm.email" type="email"
                            placeholder="user@plv.edu.ph" icon="o-envelope" required />

                        {{-- Password Field with Strength Indicator --}}
                        <x-forms.password-with-strength label="Password" model="createForm.password"
                            placeholder="Enter password" :required="true" />

                        {{-- In Create User Form - Confirm Password Field --}}
                        <div x-data="{ showConfirmPassword: false }" class="relative">
                            <x-mary-input label="Confirm Password"
                                wire:model.live.debounce.300ms="createForm.password_confirmation" ::type="showConfirmPassword ? 'text' : 'password'"
                                placeholder="Confirm password" icon="o-lock-closed" required />
                            <button type="button" @click="showConfirmPassword = !showConfirmPassword"
                                class="absolute right-3 top-9 h-10 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                                tabindex="-1">
                                <x-mary-icon name="o-eye" class="w-5 h-5" x-show="showConfirmPassword" />
                                <x-mary-icon name="o-eye-slash" class="w-5 h-5" x-show="!showConfirmPassword" />
                            </button>
                        </div>

                        <x-mary-select label="Role" wire:model.live="createForm.role" :options="$roles"
                            option-value="role_name" option-label="role_name" placeholder="Select user role"
                            icon="o-shield-check" required />

                        @if ($createForm->role === 'student-org')
                            <x-mary-select label="Organization Name"
                                wire:model.live.debounce.300ms="createForm.org_name" :options="$organizations"
                                option-value="org_id" option-label="org_name" placeholder="Select organization"
                                required />

                            <x-mary-select label="Org Position" wire:model.live.debounce.300ms="createForm.position"
                                :options="$positions" option-value="position_id" option-label="position_name"
                                placeholder="Select organization position" required />
                        @endif

                        @if (in_array($createForm->role, ['osa', 'gso']))
                            <div class="alert alert-info">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                    class="stroke-current shrink-0 w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span>Office will be automatically assigned based on the selected role.</span>
                            </div>
                        @endif

                        <x-mary-input label="Contact Number" type="text"
                            wire:model.live.debounce.700ms="createForm.phone" placeholder="09123456789"
                            icon="o-phone" required />
                    </form>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-2 pt-4 mt-4 border-t border-base-300">
                    <x-mary-button label="Cancel" @click="closeCreateUserDrawer()" />
                    <x-mary-button label="Create User" wire:click="createUser" class="btn-primary" :disabled="!$this->isCreateFormValid()"
                        spinner="createUser" />
                </div>
            </div>
        </div>
    </div>

    {{-- Edit User Drawer --}}
    <div class="drawer drawer-end z-50 rounded-l-2xl">
        <input id="edit-user-drawer-toggle" type="checkbox" class="drawer-toggle" />
        <div class="drawer-side">
            <label for="edit-user-drawer-toggle" aria-label="close sidebar" class="drawer-overlay"></label>
            <div class="bg-base-100 min-h-full w-11/12 lg:w-1/2 p-6 flex flex-col">
                {{-- Header --}}
                <div class="flex items-center justify-between mb-6 pb-4 border-b border-base-300">
                    <div>
                        <h2 class="text-2xl font-bold text-base-content">Edit User</h2>
                        <p class="text-sm text-base-content/60 mt-1">Update user information</p>
                    </div>
                    <button @click="closeEditUserDrawer()" class="btn btn-sm btn-circle btn-ghost">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                {{-- Form Content --}}
                <div class="flex-1 overflow-y-auto">
                    <form wire:submit="updateUser" class="space-y-4">
                        <x-mary-input label="Full Name" wire:model.live.debounce.300ms="editForm.name"
                            placeholder="John Dela Cruz" icon="o-user" required />

                        <x-mary-input label="Email Address" wire:model.live.blur="editForm.email" type="email"
                            placeholder="user@plv.edu.ph" icon="o-envelope" required />

                        {{-- Password Field with Strength Indicator --}}
                        <x-forms.password-with-strength label="New Password (leave blank to keep current)"
                            model="editForm.password" placeholder="Enter new password"
                            hint="Only fill if you want to change the password" />

                        {{-- In Edit User Form - Confirm Password Field --}}
                        <div x-data="{ showEditConfirmPassword: false }" class="relative">
                            <x-mary-input label="Confirm New Password"
                                wire:model.live.blur="editForm.password_confirmation" ::type="showEditConfirmPassword ? 'text' : 'password'"
                                placeholder="Confirm new password" icon="o-lock-closed" />
                            <button type="button" @click="showEditConfirmPassword = !showEditConfirmPassword"
                                class="absolute right-3 top-9 h-10 flex items-center text-gray-400 hover:text-gray-600 transition-colors"
                                tabindex="-1">
                                <i class="fas fa-eye-slash text-sm" x-show="!showEditConfirmPassword"></i>
                                <i class="fas fa-eye text-sm" x-show="showEditConfirmPassword"
                                    style="display: none;"></i>
                            </button>
                        </div>

                        @if ($editForm->is_superadmin ?? false)
                            <x-mary-input label="Role" value="Super Admin" readonly icon="o-shield-check"
                                hint="Superadmin role cannot be changed" />
                        @else
                            <x-mary-select label="Role" wire:model.live="editForm.role" :options="$roles"
                                option-value="role_name" option-label="role_name" placeholder="Select user role"
                                icon="o-shield-check" required />

                            @if ($editForm->role === 'student-org')
                                <x-mary-select label="Organization Name"
                                    wire:model.live.debounce.300ms="editForm.org_name" :options="$organizations"
                                    option-value="org_id" option-label="org_name" placeholder="Select organization"
                                    required />

                                <x-mary-select label="Org Position" wire:model.live.debounce.300ms="editForm.position"
                                    :options="$positions" option-value="position_id" option-label="position_name"
                                    placeholder="Select organization position" required />
                            @endif

                            @if (in_array($editForm->role, ['osa', 'gso']))
                                <div class="alert alert-info">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        class="stroke-current shrink-0 w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span>Office will be automatically assigned based on the selected role.</span>
                                </div>
                            @endif
                        @endif

                        <x-mary-input label="Contact Number" type="text" wire:model.live.blur="editForm.phone"
                            placeholder="09123456789" icon="o-phone" required />
                    </form>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end gap-2 pt-4 mt-4 border-t border-base-300">
                    <x-mary-button label="Cancel" @click="closeEditUserDrawer()" />
                    <x-mary-button label="Update User" wire:click="updateUser" class="btn-primary" :disabled="!$this->isEditFormValid()"
                        spinner="updateUser" />
                </div>
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal --}}
    @if ($deletingUserName)
        <x-mary-modal wire:model="showDeleteModal" title="Delete User Confirmation"
            subtitle="This action cannot be undone">
            <div class="space-y-4">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-center">
                        <svg class="w-5 h-5 text-red-600 mr-2" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z">
                            </path>
                        </svg>
                        <p class="text-red-800 font-medium">Warning: This action is permanent</p>
                    </div>
                </div>

                <p class="text-gray-700">
                    Are you sure you want to delete the user
                    <strong class="text-gray-900">{{ $deletingUserName }}</strong>?
                    <br><br>
                    This will permanently remove:
                </p>

                <ul class="list-disc list-inside text-sm text-gray-600 ml-4">
                    <li>User account and login access</li>
                    <li>All associated data and permissions</li>
                    <li>Event submissions and history</li>
                </ul>
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" wire:click="closeDeleteModal()" />
                <x-mary-button label="Delete User" wire:click="confirmDelete" class="btn-error"
                    spinner="confirmDelete" />
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>
