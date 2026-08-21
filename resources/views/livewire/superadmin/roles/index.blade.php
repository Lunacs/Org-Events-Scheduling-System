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
}"
    @role-drawer-close.window="showRoleDrawer = false" @delete-modal-close.window="showDeleteModal = false">
    <div class="p-6 space-y-6">
        <section
            class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
            <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-heading font-bold text-base-content">Roles & Permissions</h1>
                        <p class="text-sm text-base-content/70 mt-1">Manage system security roles, descriptions, and user
                            counts</p>
                    </div>
                    <div class="flex items-center gap-2 relative z-10 w-full sm:w-auto">
                        <x-ui.button icon="o-arrow-path" class="btn-outline bg-base-100" wire:click="refreshRoles">
                            Refresh
                        </x-ui.button>
                        <x-ui.button icon="o-plus" class="btn-accent" @click="openRoleDrawer()">
                            Create Role
                        </x-ui.button>
                    </div>
                </div>
            </div>
        </section>

        <x-ui.card title="System Roles">
            <x-ui.table :headers="[
                ['key' => 'name', 'label' => 'Role Name'],
                ['key' => 'description', 'label' => 'Description'],
                ['key' => 'user_count', 'label' => 'Users'],
                ['key' => 'permissions', 'label' => 'Permissions'],
                ['key' => 'actions', 'label' => 'Actions'],
            ]" :rows="$roles" :paginate="false">
                @foreach ($roles as $role)
                    <tr wire:key="role-{{ $role['id'] }}">
                        <x-ui.table-column>
                            <div class="font-medium">{{ $role['name'] }}</div>
                        </x-ui.table-column>
                        <x-ui.table-column>
                            <div class="text-sm text-gray-600">{{ $role['description'] }}</div>
                        </x-ui.table-column>
                        <x-ui.table-column>
                            <x-ui.badge :value="$role['user_count']" :class="match ($role['user_count']) {
                                0 => 'badge-ghost',
                                default => 'badge-info',
                            }" />
                        </x-ui.table-column>
                        <x-ui.table-column>
                            <div class="flex flex-wrap gap-1">
                                @foreach ($role['permissions'] as $permission)
                                    <x-ui.badge :value="$permission" class="badge-success text-xs" />
                                @endforeach
                            </div>
                        </x-ui.table-column>
                        <x-ui.table-column>
                            <div class="flex space-x-1">
                                <x-ui.button size="xs" icon="o-pencil-square" class="btn-ghost"
                                    @click="openEditRoleDrawer('{{ $role['id'] }}')">
                                    Edit
                                </x-ui.button>
                                @if ($role['id'] !== 'superadmin')
                                    <x-ui.button size="xs" icon="o-trash" class="btn-ghost text-red-600"
                                        @click="openDeleteModal('{{ $role['id'] }}')">
                                    </x-ui.button>
                                @endif
                            </div>
                        </x-ui.table-column>
                    </tr>
                @endforeach
            </x-ui.table>
        </x-ui.card>

        <x-ui.alert icon="o-information-circle" class="alert-info text-base-200">
            <div class="space-y-2">
                <p><strong>Role Management:</strong></p>
                <ul class="list-disc list-inside text-sm space-y-1">
                    <li>Super Admin role cannot be deleted or modified</li>
                    <li>Roles with assigned users cannot be deleted</li>
                    <li>Permission changes take effect immediately</li>
                    <li>Use the refresh button to update user counts</li>
                </ul>
            </div>
        </x-ui.alert>
    </div>

    {{-- Role Form Drawer (inline DaisyUI slide-over, driven by Alpine showRoleDrawer) --}}
    <div x-cloak x-show="showRoleDrawer" x-transition.opacity class="fixed inset-0 z-50">
        <div class="absolute inset-0 bg-black/40" @click="closeRoleDrawer()"></div>
        <div class="absolute right-0 top-0 h-full w-11/12 lg:w-1/2 bg-base-100 shadow-xl border-l border-base-300 flex flex-col rounded-l-2xl"
            x-transition:enter="transition ease-out duration-300 transform" x-transition:enter-start="translate-x-full"
            x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200 transform"
            x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
            <div class="px-6 py-4 border-b border-base-300 flex items-start justify-between">
                <div>
                    <h3 class="text-base font-semibold">{{ $roleForm['id'] ? 'Edit Role' : 'Create New Role' }}</h3>
                    <p class="text-sm opacity-70">
                        {{ $roleForm['id'] ? 'Update role information' : 'Add a new role to the system' }}</p>
                </div>
                <button type="button" class="btn btn-sm btn-circle btn-ghost" @click="closeRoleDrawer()"
                    aria-label="Close">✕</button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                <form wire:submit="saveRole" class="space-y-4">
                    <x-ui.input wire:model="roleForm.name" label="Role Name" placeholder="Enter role name"
                        icon="o-identification" hint="Choose a descriptive name for the role" />

                    <x-ui.textarea wire:model="roleForm.description" label="Description"
                        placeholder="Enter role description" rows="3"
                        hint="Describe the purpose and responsibilities" />

                    <div>
                        <label class="block text-sm font-medium mb-2">Permissions</label>
                        <div class="space-y-2">
                            @foreach ($allPermissions as $key => $permission)
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" class="checkbox checkbox-sm" value="{{ $key }}"
                                        wire:model="roleForm.permissions" />
                                    <span class="text-sm">{{ $permission }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('roleForm.permissions')
                            <span class="text-error text-sm mt-1">{{ $message }}</span>
                        @enderror
                    </div>
                </form>
            </div>

            <div class="px-6 py-4 border-t border-base-300 flex justify-end gap-2">
                <x-ui.button label="Cancel" @click="closeRoleDrawer()" />
                <x-ui.button label="{{ $roleForm['id'] ? 'Update Role' : 'Create Role' }}" wire:click="saveRole"
                    class="btn-primary" spinner="saveRole" />
            </div>
        </div>
    </div>

    {{-- Delete Confirmation Modal (inline DaisyUI, driven by Alpine showDeleteModal) --}}
    @if ($deletingRoleId)
        <div x-cloak x-show="showDeleteModal" x-transition.opacity.duration.200ms class="modal"
            :class="{ 'modal-open': showDeleteModal }" @keydown.escape.window="showDeleteModal = false" role="dialog"
            aria-modal="true">
            <div class="modal-box">
                <h3 class="text-lg font-bold text-base-content">Delete Role Confirmation</h3>
                <p class="text-sm text-base-content/60 mt-1">This action cannot be undone</p>

                <div class="py-2 space-y-4">
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

                <div class="modal-action">
                    <x-ui.button label="Cancel" @click="closeDeleteModal()" />
                    <x-ui.button label="Delete Role" wire:click="confirmDeleteRole" class="btn-error"
                        spinner="confirmDeleteRole" />
                </div>
            </div>
            <div class="modal-backdrop" @click="closeDeleteModal()"></div>
        </div> @endif
    </div>
