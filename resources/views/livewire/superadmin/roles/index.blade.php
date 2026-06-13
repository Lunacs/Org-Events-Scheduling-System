    <div x-data="{ 
    showRoleDrawer: false,
    showDeleteModal: false,
    openRoleDrawer() {
        $wire.resetRoleForm();
        this.showRoleDrawer = true;
    },
    openEditRoleDrawer(roleId) {
        $wire.loadRoleForm(roleId);
        this.showRoleDrawer = true;
    },
    closeRoleDrawer() {
        this.showRoleDrawer = false;
        $wire.resetRoleForm();
    },
    openDeleteModal(roleId) {
        $wire.loadRoleForDeletion(roleId);
        this.showDeleteModal = true;
    },
    closeDeleteModal() {
        this.showDeleteModal = false;
        $wire.resetDeleteModal();
    }
}" @role-drawer-close.window="showRoleDrawer = false" @delete-modal-close.window="showDeleteModal = false">
    <div class="p-6 space-y-6">
        <section
            class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
            <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-heading font-bold text-base-content">Roles & Permissions</h1>
                        <p class="text-sm text-base-content/70 mt-1">Manage system security roles, descriptions, and user counts</p>
                    </div>
                    <div class="flex items-center gap-2 relative z-10 w-full sm:w-auto">
                        <x-mary-button icon="o-arrow-path" class="btn-outline bg-base-100" wire:click="refreshRoles">
                            Refresh
                        </x-mary-button>
                        <x-mary-button icon="o-plus" class="btn-accent" @click="openRoleDrawer()">
                            Create Role
                        </x-mary-button>
                    </div>
                </div>
            </div>
        </section>

        <x-mary-card title="System Roles">
            <x-mary-table :headers="[
                ['key' => 'name', 'label' => 'Role Name', 'sortable' => true],
                ['key' => 'description', 'label' => 'Description'],
                ['key' => 'user_count', 'label' => 'Users', 'sortable' => true],
                ['key' => 'permissions', 'label' => 'Permissions'],
                ['key' => 'actions', 'label' => 'Actions', 'sortable' => false],
            ]" :rows="$roles">
                @scope('cell_name', $role)
                    <div class="font-medium">{{ $role['name'] }}</div>
                @endscope

                @scope('cell_description', $role)
                    <div class="text-sm text-gray-600">{{ $role['description'] }}</div>
                @endscope

                @scope('cell_user_count', $role)
                    <x-mary-badge :value="$role['user_count']" :class="match ($role['user_count']) {
                        0 => 'badge-ghost',
                        default => 'badge-info',
                    }" />
                @endscope

                @scope('cell_permissions', $role)
                    <div class="flex flex-wrap gap-1">
                        @foreach ($role['permissions'] as $permission)
                            <x-mary-badge :value="$permission" class="badge-success text-xs" />
                        @endforeach
                    </div>
                @endscope

                @scope('cell_actions', $role)
                    <div class="flex space-x-1">
                        <x-mary-button size="xs" icon="o-pencil-square" class="btn-ghost"
                            @click="openEditRoleDrawer('{{ $role['id'] }}')">
                            Edit
                        </x-mary-button>
                        @if ($role['id'] !== 'superadmin')
                            <x-mary-button size="xs" icon="o-trash" class="btn-ghost text-red-600"
                                @click="openDeleteModal('{{ $role['id'] }}')">
                            </x-mary-button>
                        @endif
                    </div>
                @endscope
                </x-table>
                </x-card>

                <x-mary-alert icon="o-information-circle" class="alert-info text-base-200">
                    <div class="space-y-2">
                        <p><strong>Role Management:</strong></p>
                        <ul class="list-disc list-inside text-sm space-y-1">
                            <li>Super Admin role cannot be deleted or modified</li>
                            <li>Roles with assigned users cannot be deleted</li>
                            <li>Permission changes take effect immediately</li>
                            <li>Use the refresh button to update user counts</li>
                        </ul>
                    </div>
                    </x-alert>
    </div>

    {{-- Role Form Drawer --}}
    <x-mary-drawer x-model="showRoleDrawer" title="{{ $roleForm['id'] ? 'Edit Role' : 'Create New Role' }}"
        subtitle="{{ $roleForm['id'] ? 'Update role information' : 'Add a new role to the system' }}" separator
        with-close-button close-on-escape class="w-11/12 lg:w-1/2" right @close="closeRoleDrawer()">

        <form wire:submit="saveRole" class="space-y-4">
            <x-mary-input wire:model="roleForm.name" label="Role Name" placeholder="Enter role name"
                icon="o-identification" hint="Choose a descriptive name for the role" />

            <x-mary-textarea wire:model="roleForm.description" label="Description" placeholder="Enter role description"
                rows="3" hint="Describe the purpose and responsibilities" />

            <div>
                <label class="block text-sm font-medium mb-2">Permissions</label>
                <div class="space-y-2">
                    @foreach ($allPermissions as $key => $permission)
                        <x-mary-checkbox wire:model="roleForm.permissions" :value="$key" :label="$permission" />
                    @endforeach
                </div>
                @error('roleForm.permissions')
                    <span class="text-error text-sm mt-1">{{ $message }}</span>
                @enderror
            </div>
        </form>

        <x-slot:actions>
            <x-mary-button label="Cancel" @click="closeRoleDrawer()" />
            <x-mary-button label="{{ $roleForm['id'] ? 'Update Role' : 'Create Role' }}" wire:click="saveRole"
                class="btn-primary" spinner="saveRole" />
        </x-slot:actions>
    </x-mary-drawer>

    {{-- Delete Confirmation Modal --}}
    @if ($deletingRoleId)
        <x-mary-modal x-model="showDeleteModal" title="Delete Role Confirmation"
            subtitle="This action cannot be undone">
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
                    Are you sure you want to delete this role?
                    <br><br>
                    This will permanently remove:
                </p>

                <ul class="list-disc list-inside text-sm text-gray-600 ml-4">
                    <li>Role definition and permissions</li>
                    <li>All user assignments to this role</li>
                    <li>Related access configurations</li>
                </ul>
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" @click="closeDeleteModal()" />
                <x-mary-button label="Delete Role" wire:click="confirmDeleteRole" class="btn-error"
                    spinner="confirmDeleteRole" />
            </x-slot:actions>
        </x-mary-modal>
    @endif
</div>
