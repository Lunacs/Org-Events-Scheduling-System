<div x-data="{
    showCreateUserDrawer: false,
    showEditUserDrawer: false,
    showDeleteModal: false,
    openCreateUserDrawer() {
        $wire.resetCreateForm();
        this.showCreateUserDrawer = true;
    },
    closeCreateUserDrawer() {
        this.showCreateUserDrawer = false;
        $wire.resetCreateForm();
    },
    openEditUserDrawer(userId) {
        $wire.loadEditForm(userId);
        this.showEditUserDrawer = true;
    },
    closeEditUserDrawer() {
        this.showEditUserDrawer = false;
        $wire.resetEditForm();
    },
    openDeleteModal(userId) {
        $wire.loadUserForDeletion(userId);
        this.showDeleteModal = true;
    },
    closeDeleteModal() {
        this.showDeleteModal = false;
        $wire.resetDeleteModal();
    }
}" @user-drawer-close.window="showCreateUserDrawer = false; showEditUserDrawer = false"
    @delete-modal-close.window="showDeleteModal = false">
    <div class="p-6 space-y-6">
        <div class="flex items-center justify-between">
            <h1 class="text-2xl font-bold font-heading">User Management</h1>
            <x-mary-button icon="o-plus" class="btn-accent font-body" @click="openCreateUserDrawer()">
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
                    ['id' => 'student_org', 'name' => 'Student Organization'],
                ]" option-value="id"
                    option-label="name" />

                <div class="flex items-end">
                    <x-mary-button icon="o-arrow-path" class="btn-outline" wire:click="$refresh">
                        Refresh
                    </x-mary-button>
                </div>
            </div>
        </x-mary-card>

        <x-mary-card shadow>
            <x-mary-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination :per-page-values="[10, 25, 50]"
                class="rounded-lg">
                @scope('cell_role', $user)
                    @php
                        $roleString = $user->role?->role_name ?? 'unknown';
                    @endphp
                    <x-mary-badge :value="$this->getRoleDisplayName($roleString)" :class="match ($roleString) {
                        'superadmin' => 'badge-error text-base-200 text-md',
                        'osa' => 'badge-primary text-base-200',
                        'gso' => 'badge-info text-base-200',
                        'student-org' => 'badge-success text-base-200 whitespace-nowrap',
                        default => 'badge-ghost text-base-200',
                    }" />
                @endscope

                @scope('cell_status', $user)
                    @if ($user->email_verified_at)
                        <x-mary-badge value="Verified" class="badge-success text-base-200" />
                    @else
                        <x-mary-badge value="Unverified" class="badge-warning text-base-200" />
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
                                @click="openDeleteModal({{ $user->user_id }})">
                            </x-mary-button>
                        @endif
                    </div>
                @endscope
            </x-mary-table>
        </x-mary-card>
    </div>

    {{-- Create User Drawer --}}
    <x-mary-drawer x-model="showCreateUserDrawer" title="Create New User" subtitle="Add a new user to the system"
        separator close-on-escape with-close-button class="w-11/12 lg:w-1/2" right @close="closeCreateUserDrawer()">

        <form wire:submit="createUser" class="space-y-4">
            <x-mary-input label="Full Name" wire:model="createForm.name" placeholder="John Dela Cruz" icon="o-user"
                hint="Enter the user's complete name" />

            <x-mary-input label="Email Address" wire:model="createForm.email" type="email"
                placeholder="user@plv.edu.ph" icon="o-envelope" hint="Must be a valid PLV email address" />

            <x-mary-input label="Password" wire:model="createForm.password" type="password" placeholder="Enter password"
                icon="o-lock-closed" hint="Minimum 8 characters" />

            <x-mary-input label="Confirm Password" wire:model="createForm.password_confirmation" type="password"
                placeholder="Confirm password" icon="o-lock-closed" />

            <x-mary-select label="Role" wire:model="createForm.role" :options="[
                ['value' => 'osa', 'label' => 'OSA Staff'],
                ['value' => 'gso', 'label' => 'GSO Staff'],
                ['value' => 'student_org', 'label' => 'Student Organization'],
            ]" option-value="value"
                option-label="label" placeholder="Select user role" icon="o-shield-check"
                hint="Assign the appropriate role for this user" />
        </form>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="closeCreateUserDrawer()" />
            <x-mary-button label="Create User" wire:click="createUser" class="btn-primary" spinner="createUser" />
        </x-slot:actions>
    </x-mary-drawer>

    {{-- Edit User Drawer --}}
    <x-mary-drawer x-model="showEditUserDrawer" title="Edit User" subtitle="Update user information" separator
        close-on-escape with-close-button class="w-11/12 lg:w-1/2" right @close="closeEditUserDrawer()">

        <form wire:submit="updateUser" class="space-y-4">
            <x-mary-input label="Full Name" wire:model="editForm.name" placeholder="John Dela Cruz" icon="o-user"
                hint="Enter the user's complete name" />

            <x-mary-input label="Email Address" wire:model="editForm.email" type="email" placeholder="user@plv.edu.ph"
                icon="o-envelope" hint="Must be a valid PLV email address" />

            <x-mary-input label="New Password (leave blank to keep current)" wire:model="editForm.password"
                type="password" placeholder="Enter new password" icon="o-lock-closed"
                hint="Only fill if you want to change the password (min 8 characters)" />

            <x-mary-input label="Confirm New Password" wire:model="editForm.password_confirmation" type="password"
                placeholder="Confirm new password" icon="o-lock-closed" />

            @if ($editForm['is_superadmin'] ?? false)
                <x-mary-input label="Role" value="Super Admin" readonly icon="o-shield-check"
                    hint="Superadmin role cannot be changed" />
            @else
                <x-mary-select label="Role" wire:model="editForm.role" :options="[
                    ['value' => 'osa', 'label' => 'OSA Staff'],
                    ['value' => 'gso', 'label' => 'GSO Staff'],
                    ['value' => 'student_org', 'label' => 'Student Organization'],
                ]" option-value="value"
                    option-label="label" placeholder="Select user role" icon="o-shield-check"
                    hint="Assign the appropriate role for this user" />
            @endif
        </form>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="closeEditUserDrawer()" />
            <x-mary-button label="Update User" wire:click="updateUser" class="btn-primary" spinner="updateUser" />
        </x-slot:actions>
    </x-mary-drawer>

    {{-- Delete Confirmation Modal --}}
    @if ($deletingUserName)
        <x-mary-modal x-model="showDeleteModal" title="Delete User Confirmation"
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
                <x-mary-button label="Cancel" @click="closeDeleteModal()" />
                <x-mary-button label="Delete User" wire:click="confirmDelete" class="btn-error"
                    spinner="confirmDelete" />
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>
