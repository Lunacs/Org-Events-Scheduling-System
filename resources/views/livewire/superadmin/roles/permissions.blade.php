<div class="space-y-6 py-6">
    <section class="flex flex-col gap-4 border-b border-base-300 pb-6 sm:flex-row sm:items-end sm:justify-between">
        <div class="space-y-1">
            <h1 class="text-2xl font-semibold text-base-content">Permissions for {{ $this->role->role_name }}</h1>
            <p id="role-permissions-description" class="max-w-2xl text-sm text-base-content/70">
                Choose the pages and actions available to this role, then save your changes to apply them.
            </p>
            <p class="text-sm text-base-content/70">
                Base portal:
                <span class="badge badge-outline">{{ \App\Models\Roles::SELECTABLE_PORTALS[$this->portal] ?? 'Superadmin' }}</span>
            </p>
        </div>

        <span class="badge badge-primary badge-outline self-start sm:self-auto">
            {{ $this->allPermissions->count() }} {{ str('permission')->plural($this->allPermissions->count()) }}
        </span>
    </section>

    @if ($isSuperadminRole)
        <div class="alert alert-warning" role="alert">
            <svg aria-hidden="true" class="size-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m9.303 3.376c.866 1.5-.217 3.374-1.949 3.374H4.646c-1.732 0-2.815-1.874-1.949-3.374L10.051 3.374c.866-1.5 3.032-1.5 3.898 0l7.354 12.752ZM12 16.5h.008v.008H12V16.5Z" />
            </svg>
            <span>Superadmin permissions are locked to preserve unrestricted system access.</span>
        </div>
    @endif

    @if ($this->allPermissions->count() > 200)
        <div class="alert alert-info" role="status">
            <span>Showing the first 200 permissions. Refine the configured permissions before assigning more.</span>
        </div>
    @endif

    <form wire:submit="savePermissions" class="card border border-base-300 bg-base-100">
        <div class="card-body gap-5 p-5 sm:p-6">
            <div class="space-y-1">
                <h2 class="text-lg font-semibold text-base-content">Permission assignments</h2>
                <p class="text-sm text-base-content/70">
                    Toggle a permission to update the pending selection. Changes are not applied until you save.
                </p>
            </div>

            <div class="space-y-5" aria-describedby="role-permissions-description">
                @forelse ($this->allPermissions->take(200)->groupBy(fn ($permission) => $permission->group ?? 'Other') as $groupName => $groupPermissions)
                    <section wire:key="permission-group-{{ \Illuminate\Support\Str::slug($groupName) }}" class="space-y-2"
                        aria-labelledby="permission-group-{{ \Illuminate\Support\Str::slug($groupName) }}">
                        <h3 id="permission-group-{{ \Illuminate\Support\Str::slug($groupName) }}"
                            class="text-sm font-semibold uppercase tracking-wide text-base-content/60">{{ $groupName }}</h3>

                        <div class="divide-y divide-base-300 rounded-box border border-base-300" role="list">
                        @foreach ($groupPermissions as $permission)
                            @php
                                $appliesToPortal = \App\Support\PermissionCatalog::appliesToPortal($permission->name, $this->portal);
                            @endphp
                            <div wire:key="permission-toggle-{{ $permission->permission_id }}" class="flex items-center justify-between gap-4 p-4"
                                role="listitem">
                                <label for="permission-toggle-{{ $permission->permission_id }}" class="min-w-0 cursor-pointer space-y-0.5">
                                    <span class="block text-sm font-medium text-base-content">{{ $permission->label ?? $permission->name }}</span>
                                    <span class="block break-all text-xs text-base-content/60">{{ $permission->name }}</span>
                                    @unless ($appliesToPortal)
                                        <span class="badge badge-ghost badge-sm mt-1">Not used in this portal</span>
                                    @endunless
                                </label>

                                <input id="permission-toggle-{{ $permission->permission_id }}" type="checkbox" class="toggle toggle-primary"
                                    @checked(in_array($permission->permission_id, $selectedPermissionIds, true)) @disabled($isSuperadminRole)
                                    aria-disabled="{{ $isSuperadminRole ? 'true' : 'false' }}"
                                    wire:click="togglePermission({{ $permission->permission_id }})"
                                    wire:loading.attr="disabled" wire:target="togglePermission({{ $permission->permission_id }})">
                            </div>
                        @endforeach
                        </div>
                    </section>
                @empty
                    <div class="p-8 text-center" role="status">
                        <p class="font-medium text-base-content">No permissions configured</p>
                        <p class="mt-1 text-sm text-base-content/70">
                            Create a permission before assigning access to this role.
                        </p>
                    </div>
                @endforelse
            </div>

            <div class="flex flex-col-reverse gap-3 border-t border-base-300 pt-5 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-sm text-base-content/70">
                    {{ $isSuperadminRole ? 'Superadmin permissions cannot be changed from this screen.' : 'Saving applies the selected permissions to this role.' }}
                </p>

                <button type="submit" class="btn btn-primary w-full sm:w-auto" wire:target="savePermissions"
                    wire:loading.attr="disabled" @disabled($isSuperadminRole)
                    aria-disabled="{{ $isSuperadminRole ? 'true' : 'false' }}">
                    <span wire:loading.remove wire:target="savePermissions">Save permissions</span>
                    <span wire:loading wire:target="savePermissions" class="loading loading-spinner loading-sm"
                        aria-label="Saving permissions"></span>
                </button>
            </div>
        </div>
    </form>
</div>
