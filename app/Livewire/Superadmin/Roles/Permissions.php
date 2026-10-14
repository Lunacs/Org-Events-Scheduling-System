<?php

namespace App\Livewire\Superadmin\Roles;

use App\Models\Permission;
use App\Models\Roles;
use App\Services\TransactionLogService;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Title('Superadmin - Roles & Permissions')]
#[Layout('components.layouts.superadmin')]
class Permissions extends Component
{
    use Toast;

    public int $roleId;

    public array $selectedPermissionIds = [];

    public bool $isSuperadminRole = false;

    public function mount(int $roleId): void
    {
        $role = Roles::query()->findOrFail($roleId);

        $this->roleId = $roleId;
        $this->isSuperadminRole = $role->role_name === 'superadmin';
        $this->selectedPermissionIds = $role->permissions()->pluck('permissions.permission_id')->toArray();
    }

    public function togglePermission(int $permissionId): void
    {
        if (in_array($permissionId, $this->selectedPermissionIds, true)) {
            $this->selectedPermissionIds = array_values(array_filter(
                $this->selectedPermissionIds,
                static fn (int $selectedPermissionId): bool => $selectedPermissionId !== $permissionId,
            ));

            return;
        }

        $this->selectedPermissionIds[] = $permissionId;
    }

    public function savePermissions(): void
    {
        Gate::authorize('manage-rbac');

        $role = Roles::query()->findOrFail($this->roleId);

        if ($role->role_name === 'superadmin' && $this->selectedPermissionIds === []) {
            $this->error('The superadmin role must retain at least one permission.', position: 'toast-top');

            return;
        }

        if ($role->role_name === 'superadmin') {
            $this->error('The superadmin role permissions cannot be modified.', position: 'toast-top');

            return;
        }

        try {
            DB::transaction(function () use ($role): void {
                $previousPermissionIds = $role->permissions()
                    ->orderBy('permissions.permission_id')
                    ->pluck('permissions.permission_id')
                    ->map(static fn (mixed $permissionId): int => (int) $permissionId)
                    ->all();

                $role->permissions()->sync($this->selectedPermissionIds);

                TransactionLogService::logRbacOperation('RBAC_PERMISSIONS_MAPPED', [
                    'record_id' => $role->role_id,
                    'before' => ['permission_ids' => $previousPermissionIds],
                    'after' => ['permission_ids' => $this->selectedPermissionIds],
                    'timestamp' => now()->utc()->toISOString(),
                ]);
            });

            DB::afterCommit(static function () use ($role): void {
                TransactionLogService::invalidateRolePermissionCache($role->role_id);
            });
        } catch (\Exception $exception) {
            Log::error('RBAC permission mapping failed.', [
                'role_id' => $this->roleId,
                'error' => $exception->getMessage(),
            ]);

            $this->error('Failed to update role permissions. Please try again.', position: 'toast-top');

            return;
        }

        $this->success('Role permissions updated successfully.', position: 'toast-top');
    }

    #[Computed]
    public function role(): Roles
    {
        return Roles::query()->findOrFail($this->roleId);
    }

    /**
     * The portal this role's users work in, used to flag permissions with no effect there.
     */
    #[Computed]
    public function portal(): string
    {
        return $this->role->portal();
    }

    #[Computed(persist: true, seconds: 60)]
    public function allPermissions(): Collection
    {
        return Permission::query()
            ->orderBy('name')
            ->get();
    }
}
