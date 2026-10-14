<div class="space-y-6 py-6">
    <section class="flex flex-col gap-4 border-b border-base-300 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-base-content">Permission management</h1>
            <p class="max-w-2xl text-sm text-base-content/70">
                Define the capabilities that roles can use throughout the event scheduling system.
            </p>
        </div>

        <span class="badge badge-primary badge-outline self-start sm:self-auto">
            {{ $permissions->total() }} {{ str('permission')->plural($permissions->total()) }}
        </span>
    </section>

    <section class="card border border-base-300 bg-base-100">
        <div class="card-body gap-5 p-5 sm:p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-base-content">Create permission</h2>
                <p class="text-sm text-base-content/70">
                    Use lowercase dot notation, such as <span class="font-medium text-base-content">tickets.view</span>.
                </p>
            </div>

            <form wire:submit="createPermission" class="grid gap-3 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_minmax(0,12rem)_auto] lg:items-start">
                <div>
                    <label for="create-permission-name" class="label">
                        <span class="label-text font-medium">Permission name</span>
                    </label>
                    <input id="create-permission-name" type="text" wire:model.live.debounce.300ms="createName"
                        class="input input-bordered w-full @error('createName') input-error @enderror"
                        placeholder="reports.export" autocomplete="off" spellcheck="false"
                        aria-invalid="@error('createName') true @else false @enderror"
                        @error('createName') aria-describedby="create-permission-name-error" @enderror>
                    @error('createName')
                        <p id="create-permission-name-error" class="mt-2 text-sm text-error" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div>
                    <label for="create-permission-label" class="label">
                        <span class="label-text font-medium">Label <span class="text-base-content/60">(optional)</span></span>
                    </label>
                    <input id="create-permission-label" type="text" wire:model="createLabel" maxlength="255"
                        class="input input-bordered w-full @error('createLabel') input-error @enderror"
                        placeholder="Export reports" autocomplete="off">
                    @error('createLabel')
                        <p class="mt-2 text-sm text-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="create-permission-group" class="label">
                        <span class="label-text font-medium">Group <span class="text-base-content/60">(optional)</span></span>
                    </label>
                    <input id="create-permission-group" type="text" wire:model="createGroup" maxlength="64"
                        class="input input-bordered w-full @error('createGroup') input-error @enderror"
                        placeholder="Reports" autocomplete="off">
                    @error('createGroup')
                        <p class="mt-2 text-sm text-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-full lg:mt-9 lg:w-auto" wire:target="createPermission"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="createPermission">Create permission</span>
                    <span wire:loading wire:target="createPermission" class="loading loading-spinner loading-sm"
                        aria-label="Creating permission"></span>
                </button>
            </form>
        </div>
    </section>

    <section class="card border border-base-300 bg-base-100">
        <div class="card-body gap-5 p-0">
            <div class="flex flex-col gap-1 px-5 pt-5 sm:px-6 sm:pt-6">
                <h2 class="text-lg font-semibold text-base-content">Configured permissions</h2>
                <p class="text-sm text-base-content/70">
                    Rename or remove a permission. Changes take effect on subsequent requests.
                </p>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th scope="col">Permission name</th>
                            <th scope="col">Assigned roles</th>
                            <th scope="col" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($permissions as $permission)
                            <tr wire:key="permission-{{ $permission->permission_id }}">
                                @if ($editingPermissionId === $permission->permission_id)
                                    <td class="min-w-72">
                                        <form id="rename-permission-{{ $permission->permission_id }}"
                                            wire:submit="saveRename">
                                            <label for="rename-permission-name-{{ $permission->permission_id }}"
                                                class="sr-only">
                                                New permission name
                                            </label>
                                            <input id="rename-permission-name-{{ $permission->permission_id }}"
                                                type="text" wire:model.live.debounce.300ms="editingName"
                                                class="input input-bordered input-sm w-full @error('editingName') input-error @enderror"
                                                autocomplete="off" spellcheck="false"
                                                aria-invalid="@error('editingName') true @else false @enderror"
                                                @error('editingName') aria-describedby="rename-permission-name-error" @enderror>
                                            @error('editingName')
                                                <p id="rename-permission-name-error" class="mt-2 text-sm text-error"
                                                    role="alert">
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                            <div class="mt-2 grid gap-2 sm:grid-cols-2">
                                                <div>
                                                    <label for="rename-permission-label-{{ $permission->permission_id }}"
                                                        class="sr-only">Permission label</label>
                                                    <input id="rename-permission-label-{{ $permission->permission_id }}"
                                                        type="text" wire:model="editingLabel" maxlength="255"
                                                        class="input input-bordered input-sm w-full @error('editingLabel') input-error @enderror"
                                                        placeholder="Label (optional)" autocomplete="off">
                                                    @error('editingLabel')
                                                        <p class="mt-1 text-sm text-error" role="alert">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                                <div>
                                                    <label for="rename-permission-group-{{ $permission->permission_id }}"
                                                        class="sr-only">Permission group</label>
                                                    <input id="rename-permission-group-{{ $permission->permission_id }}"
                                                        type="text" wire:model="editingGroup" maxlength="64"
                                                        class="input input-bordered input-sm w-full @error('editingGroup') input-error @enderror"
                                                        placeholder="Group (optional)" autocomplete="off">
                                                    @error('editingGroup')
                                                        <p class="mt-1 text-sm text-error" role="alert">{{ $message }}</p>
                                                    @enderror
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                    <td>
                                        <span class="badge badge-neutral badge-sm">
                                            {{ $permission->roles_count }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <button type="submit"
                                                form="rename-permission-{{ $permission->permission_id }}"
                                                class="btn btn-primary btn-sm" wire:target="saveRename"
                                                wire:loading.attr="disabled">
                                                Save
                                            </button>
                                            <button type="button" class="btn btn-ghost btn-sm"
                                                wire:click="$set('editingPermissionId', null)">
                                                Cancel
                                            </button>
                                        </div>
                                    </td>
                                @else
                                    <td>
                                        <span class="block font-medium text-base-content">{{ $permission->name }}</span>
                                        @if ($permission->label || $permission->group)
                                            <span class="block text-xs text-base-content/60">
                                                {{ $permission->label }}@if ($permission->label && $permission->group) · @endif{{ $permission->group }}
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge badge-neutral badge-sm">
                                            {{ $permission->roles_count }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex flex-wrap justify-end gap-2">
                                            <button type="button" class="btn btn-ghost btn-sm"
                                                wire:click="startRename({{ $permission->permission_id }})">
                                                Rename
                                            </button>
                                            <button type="button" class="btn btn-error btn-outline btn-sm"
                                                wire:click="openDeleteModal({{ $permission->permission_id }})">
                                                Delete
                                            </button>
                                        </div>
                                    </td>
                                @endif
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">
                                    <div class="py-8 text-center">
                                        <p class="font-medium text-base-content">No permissions configured</p>
                                        <p class="mt-1 text-sm text-base-content/70">
                                            Create the first permission above to define role capabilities.
                                        </p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($permissions->hasPages())
                <div class="border-t border-base-300 px-5 py-4 sm:px-6">
                    {{ $permissions->links() }}
                </div>
            @endif
        </div>
    </section>

    <dialog x-data="{ deletionId: @entangle('deletingPermissionId') }"
        x-effect="
        if (deletionId !== null && ! $el.open) {
            $el.showModal()
            $nextTick(() => $refs.cancelButton.focus())
        }

        if (deletionId === null && $el.open) {
            $el.close()
        }
    "
        x-on:cancel.prevent="deletionId = null" x-on:close="deletionId = null" class="modal"
        aria-labelledby="delete-permission-title">
        <div class="modal-box max-w-lg">
            <h2 id="delete-permission-title" class="text-lg font-semibold text-base-content">Delete permission?</h2>
            <p class="mt-2 text-sm text-base-content/70">
                This permanently deletes <span
                    class="font-medium text-base-content">{{ $deletingPermissionName }}</span>
                and removes it from every assigned role.
            </p>

            @if ($deletingAssignedToSuperadmin)
                <div class="alert alert-warning mt-5" role="alert">
                    <div>
                        <p class="font-medium">This permission is assigned to the superadmin role.</p>
                        <p class="mt-1 text-sm">
                            Deleting it also removes the configured assignment from the superadmin role.
                        </p>
                    </div>
                </div>
            @endif

            <div class="modal-action">
                <button type="button" class="btn btn-ghost" x-ref="cancelButton" x-on:click="deletionId = null">
                    Cancel
                </button>
                <button type="button" class="btn btn-error" wire:click="confirmDelete" wire:target="confirmDelete"
                    wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="confirmDelete">Delete permission</span>
                    <span wire:loading wire:target="confirmDelete" class="loading loading-spinner loading-sm"
                        aria-label="Deleting permission"></span>
                </button>
            </div>
        </div>

        <form method="dialog" class="modal-backdrop">
            <button type="submit" aria-label="Close delete confirmation">Close</button>
        </form>
    </dialog>
</div>
