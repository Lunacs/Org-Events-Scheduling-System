<div class="space-y-6 py-6">
    <section class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-base-content">Role management</h1>
            <p class="text-sm text-base-content/70">Create roles, manage access, and keep protected portal roles intact.</p>
        </div>

        <label class="input input-bordered flex w-full items-center gap-2 sm:w-72" for="role-search">
            <svg aria-hidden="true" class="size-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35m1.35-5.65a7 7 0 1 1-14 0 7 7 0 0 1 14 0Z" />
            </svg>
            <span class="sr-only">Search roles</span>
            <input id="role-search" type="search" class="grow" placeholder="Search roles"
                wire:model.live.debounce.300ms="search" />
        </label>
    </section>

    <section class="card border border-base-300 bg-base-100">
        <div class="card-body gap-5 p-5 sm:p-6">
            <div>
                <h2 class="card-title text-lg">Create a role</h2>
                <p class="text-sm text-base-content/70">Role names must be unique and no longer than 100 characters. The
                    base portal decides which workflow the role's users take part in; its permissions decide which pages and
                    actions they see there.</p>
            </div>

            <form wire:submit="createRole" class="grid grid-cols-1 gap-3 sm:grid-cols-[minmax(0,1fr)_minmax(0,16rem)_auto] sm:items-start">
                <div class="space-y-1">
                    <label class="label px-0 py-0" for="create-role-name">
                        <span class="label-text">New role name</span>
                    </label>
                    <input id="create-role-name" name="role_name" type="text" class="input input-bordered w-full" maxlength="100"
                        autocomplete="off" wire:model.live.blur="createName"
                        @error('createName') aria-invalid="true" aria-describedby="create-role-name-error" @enderror />
                    @error('createName')
                        <p id="create-role-name-error" class="text-sm text-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-1">
                    <label class="label px-0 py-0" for="create-role-portal">
                        <span class="label-text">Base portal</span>
                    </label>
                    <select id="create-role-portal" class="select select-bordered w-full" wire:model="createPortal"
                        @error('createPortal') aria-invalid="true" aria-describedby="create-role-portal-error" @enderror>
                        @foreach (\App\Models\Roles::SELECTABLE_PORTALS as $portalValue => $portalLabel)
                            <option value="{{ $portalValue }}">{{ $portalLabel }}</option>
                        @endforeach
                    </select>
                    @error('createPortal')
                        <p id="create-role-portal-error" class="text-sm text-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary sm:mt-6" wire:loading.attr="disabled" wire:target="createRole">
                    Create role
                </button>
            </form>
        </div>
    </section>

    <section class="card border border-base-300 bg-base-100">
        <div class="card-body gap-4 p-0">
            <div class="flex flex-col gap-1 px-5 pt-5 sm:px-6 sm:pt-6">
                <h2 class="card-title text-lg">Roles</h2>
                <p class="text-sm text-base-content/70">Protected roles keep the portal routes that the application depends on.</p>
            </div>

            <div class="overflow-x-auto">
                <table class="table table-zebra">
                    <thead>
                        <tr>
                            <th scope="col">Role</th>
                            <th scope="col">Portal</th>
                            <th scope="col">Status</th>
                            <th scope="col">Users</th>
                            <th scope="col" class="text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                            <tr wire:key="role-{{ $role->role_id }}">
                                <td class="min-w-72">
                                    @if ($editingRoleId === $role->role_id)
                                        <form wire:submit="saveRename" class="max-w-md space-y-2">
                                            <label class="label px-0 py-0" for="rename-role-{{ $role->role_id }}">
                                                <span class="label-text">Rename {{ $role->role_name }}</span>
                                            </label>
                                            <div class="flex flex-wrap items-start gap-2">
                                                <input id="rename-role-{{ $role->role_id }}" name="role_name" type="text"
                                                    class="input input-bordered input-sm min-w-52 grow" maxlength="100"
                                                    autocomplete="off" wire:model.live.blur="editingName"
                                                    @error('editingName') aria-invalid="true" aria-describedby="rename-role-error-{{ $role->role_id }}" @enderror />
                                                <button type="submit" class="btn btn-primary btn-sm" wire:loading.attr="disabled"
                                                    wire:target="saveRename">
                                                    Save
                                                </button>
                                                <button type="button" class="btn btn-ghost btn-sm"
                                                    wire:click="$set('editingRoleId', null)">
                                                    Cancel
                                                </button>
                                            </div>
                                            @error('editingName')
                                                <p id="rename-role-error-{{ $role->role_id }}" class="text-sm text-error" role="alert">
                                                    {{ $message }}
                                                </p>
                                            @enderror
                                        </form>
                                    @else
                                        <span class="font-medium text-base-content">{{ $role->role_name }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-outline">
                                        {{ \App\Models\Roles::SELECTABLE_PORTALS[$role->portal()] ?? 'Superadmin' }}
                                    </span>
                                </td>
                                <td>
                                    @if ($role->isProtected())
                                        <span class="badge badge-warning badge-outline">Protected</span>
                                    @else
                                        <span class="badge badge-ghost">Custom</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-medium tabular-nums">{{ $role->users_count }}</span>
                                    <span class="text-sm text-base-content/70">{{ $role->users_count === 1 ? 'user' : 'users' }}</span>
                                </td>
                                <td>
                                    <div class="flex min-w-max justify-end gap-2">
                                        <a href="{{ route('superadmin.roles.permissions', $role->role_id) }}"
                                            class="btn btn-outline btn-sm" wire:navigate>
                                            Edit permissions
                                        </a>

                                        @if ($role->isProtected())
                                            <span class="tooltip" data-tip="Protected roles cannot be renamed or deleted">
                                                <button type="button" class="btn btn-ghost btn-sm" disabled aria-disabled="true">
                                                    Rename
                                                </button>
                                            </span>
                                            <span class="tooltip" data-tip="Protected roles cannot be renamed or deleted">
                                                <button type="button" class="btn btn-ghost btn-sm" disabled aria-disabled="true">
                                                    Delete
                                                </button>
                                            </span>
                                        @else
                                            <button type="button" class="btn btn-ghost btn-sm"
                                                wire:click="openPortalModal({{ $role->role_id }})">
                                                Change portal
                                            </button>
                                            <button type="button" class="btn btn-ghost btn-sm"
                                                wire:click="startRename({{ $role->role_id }})">
                                                Rename
                                            </button>
                                            <button type="button" class="btn btn-error btn-outline btn-sm"
                                                wire:click="openDeleteModal({{ $role->role_id }})">
                                                Delete
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">
                                    <div class="py-8 text-center">
                                        <p class="font-medium text-base-content">No roles found</p>
                                        <p class="mt-1 text-sm text-base-content/70">Create a role or adjust the search to continue.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($roles->hasPages())
                <div class="border-t border-base-300 px-5 py-4 sm:px-6">
                    {{ $roles->links() }}
                </div>
            @endif
        </div>
    </section>

    <dialog
        x-data="{
            deletionId: $wire.entangle('deletingRoleId').live,
            previousFocus: null,
            dismiss() {
                $wire.deletingRoleId = null;
                $wire.deletingRoleName = null;
            },
            restoreFocus() {
                this.$nextTick(() => this.previousFocus?.focus());
            },
        }"
        x-effect="
            if (deletionId !== null && ! $el.open) {
                previousFocus = document.activeElement;
                $el.showModal();
                $nextTick(() => $refs.cancelButton.focus());
            }

            if (deletionId === null && $el.open) {
                $el.close();
            }
        "
        x-on:cancel.prevent="dismiss()"
        x-on:close="dismiss(); restoreFocus()"
        class="modal"
        aria-labelledby="delete-role-title"
    >
        <div class="modal-box">
            <h2 id="delete-role-title" class="text-lg font-semibold">Delete role</h2>
            <p class="mt-2 text-sm text-base-content/70">
                You are about to delete <span class="font-semibold text-base-content">{{ $deletingRoleName }}</span>.
                This action cannot be undone.
            </p>

            <div class="alert alert-warning mt-5" role="alert">
                <svg aria-hidden="true" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.949 3.374H4.646c-1.732 0-2.815-1.874-1.949-3.374L10.051 3.374c.866-1.5 3.032-1.5 3.898 0l7.354 12.752ZM12 16.5h.008v.008H12V16.5Z" />
                </svg>
                <span>Users assigned to this role will be moved to the configured fallback role. Its permission mappings will also be removed.</span>
            </div>

            @error('deletingRoleId')
                <div class="alert alert-error mt-4" role="alert">
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <div class="modal-action">
                <button type="button" class="btn btn-ghost" x-ref="cancelButton" @click="dismiss()">Cancel</button>
                <button type="button" class="btn btn-error" wire:click="confirmDelete" wire:loading.attr="disabled"
                    wire:target="confirmDelete">
                    Delete role
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button type="submit" @click="dismiss()" aria-label="Close delete confirmation">Close</button>
        </form>
    </dialog>
    <dialog
        x-data="{
            changingId: $wire.entangle('changingPortalRoleId').live,
            previousFocus: null,
            dismiss() {
                $wire.closePortalModal();
            },
            restoreFocus() {
                this.$nextTick(() => this.previousFocus?.focus());
            },
        }"
        x-effect="
            if (changingId !== null && ! $el.open) {
                previousFocus = document.activeElement;
                $el.showModal();
                $nextTick(() => $refs.portalSelect.focus());
            }

            if (changingId === null && $el.open) {
                $el.close();
            }
        "
        x-on:cancel.prevent="dismiss()"
        x-on:close="restoreFocus()"
        class="modal"
        aria-labelledby="change-portal-title"
    >
        <div class="modal-box">
            <h2 id="change-portal-title" class="text-lg font-semibold">Change base portal</h2>
            <p class="mt-2 text-sm text-base-content/70">
                Choose the portal that users with the <span class="font-semibold text-base-content">{{ $changingPortalRoleName }}</span>
                role work in.
            </p>

            <div class="mt-4 space-y-1">
                <label class="label px-0 py-0" for="change-role-portal">
                    <span class="label-text">Base portal</span>
                </label>
                <select id="change-role-portal" class="select select-bordered w-full" x-ref="portalSelect"
                    wire:model="changingPortal">
                    @foreach (\App\Models\Roles::SELECTABLE_PORTALS as $portalValue => $portalLabel)
                        <option value="{{ $portalValue }}">{{ $portalLabel }}</option>
                    @endforeach
                </select>
            </div>

            <div class="alert alert-warning mt-5" role="alert">
                <svg aria-hidden="true" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.949 3.374H4.646c-1.732 0-2.815-1.874-1.949-3.374L10.051 3.374c.866-1.5 3.032-1.5 3.898 0l7.354 12.752ZM12 16.5h.008v.008H12V16.5Z" />
                </svg>
                <span>Everyone assigned to this role is signed out and will use the new portal's pages the next time they sign in.</span>
            </div>

            @error('changingPortal')
                <div class="alert alert-error mt-4" role="alert">
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <div class="modal-action">
                <button type="button" class="btn btn-ghost" @click="dismiss()">Cancel</button>
                <button type="button" class="btn btn-primary" wire:click="confirmPortalChange" wire:loading.attr="disabled"
                    wire:target="confirmPortalChange">
                    Change portal
                </button>
            </div>
        </div>
        <form method="dialog" class="modal-backdrop">
            <button type="submit" @click="dismiss()" aria-label="Close portal change dialog">Close</button>
        </form>
    </dialog>
</div>
