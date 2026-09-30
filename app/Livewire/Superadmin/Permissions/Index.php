<?php

namespace App\Livewire\Superadmin\Permissions;

use App\Models\Permission;
use App\Services\TransactionLogService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;

#[Title('Superadmin - Roles & Permissions')]
#[Layout('components.layouts.superadmin')]
class Index extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Validate]
    public string $createName = '';

    public string $createLabel = '';

    public string $createGroup = '';

    public ?int $editingPermissionId = null;

    #[Validate]
    public string $editingName = '';

    public string $editingLabel = '';

    public string $editingGroup = '';

    public ?int $deletingPermissionId = null;

    public ?string $deletingPermissionName = null;

    public bool $deletingAssignedToSuperadmin = false;

    protected function rules(): array
    {
        return [
            'createName' => ['required', 'string', 'regex:'.Permission::NAME_PATTERN, 'unique:permissions,name'],
            'editingName' => [
                'required',
                'string',
                'regex:'.Permission::NAME_PATTERN,
                Rule::unique('permissions', 'name')->ignore($this->editingPermissionId, 'permission_id'),
            ],
        ];
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->permissions);
    }

    public function updatedPage(int $page): void
    {
        unset($this->permissions);
    }

    public function createPermission(): void
    {
        Gate::authorize('manage-rbac');

        $validated = $this->validateOnly('createName');
        $details = $this->validate($this->detailRules('createLabel', 'createGroup'));

        DB::transaction(function () use ($validated, $details): void {
            $permission = Permission::query()->create([
                'name' => $validated['createName'],
                'label' => $details['createLabel'] !== '' ? $details['createLabel'] : null,
                'group' => $details['createGroup'] !== '' ? $details['createGroup'] : null,
            ]);

            TransactionLogService::logRbacOperation('RBAC_PERMISSION_CREATED', [
                'record_id' => $permission->permission_id,
                'before' => null,
                'after' => ['name' => $permission->name],
                'timestamp' => now()->toISOString(),
            ]);
        });

        $this->reset('createName', 'createLabel', 'createGroup');
        unset($this->permissions);
    }

    /**
     * Validation rules for a permission's optional display label and group.
     *
     * @return array<string, list<string>>
     */
    private function detailRules(string $labelProperty, string $groupProperty): array
    {
        return [
            $labelProperty => ['nullable', 'string', 'max:255'],
            $groupProperty => ['nullable', 'string', 'max:64'],
        ];
    }

    public function startRename(int $permissionId): void
    {
        $permission = Permission::query()->findOrFail($permissionId);

        $this->editingPermissionId = $permission->permission_id;
        $this->editingName = $permission->name;
        $this->editingLabel = (string) $permission->label;
        $this->editingGroup = (string) $permission->group;
        $this->resetValidation(['editingName', 'editingLabel', 'editingGroup']);
    }

    public function saveRename(): void
    {
        Gate::authorize('manage-rbac');

        $validated = $this->validate([
            'editingName' => [
                'required',
                'string',
                'regex:'.Permission::NAME_PATTERN,
                Rule::unique('permissions', 'name')->ignore($this->editingPermissionId, 'permission_id'),
            ],
            ...$this->detailRules('editingLabel', 'editingGroup'),
        ]);

        $permission = Permission::query()->findOrFail($this->editingPermissionId);
        $oldName = $permission->name;

        $affectedRoleIds = DB::transaction(function () use ($permission, $validated, $oldName): array {
            $affectedRoleIds = $permission->roles()
                ->pluck('roles.role_id')
                ->all();

            $permission->update([
                'name' => $validated['editingName'],
                'label' => $validated['editingLabel'] !== '' ? $validated['editingLabel'] : null,
                'group' => $validated['editingGroup'] !== '' ? $validated['editingGroup'] : null,
            ]);

            TransactionLogService::logRbacOperation('RBAC_PERMISSION_RENAMED', [
                'record_id' => $permission->permission_id,
                'before' => ['name' => $oldName],
                'after' => ['name' => $permission->name],
                'timestamp' => now()->utc()->toISOString(),
            ]);

            return $affectedRoleIds;
        });

        DB::afterCommit(static function () use ($affectedRoleIds): void {
            foreach ($affectedRoleIds as $roleId) {
                TransactionLogService::invalidateRolePermissionCache((int) $roleId);
            }
        });

        $this->reset('editingPermissionId', 'editingName', 'editingLabel', 'editingGroup');
        $this->resetValidation('editingName');
        unset($this->permissions);
    }

    public function openDeleteModal(int $permissionId): void
    {
        $permission = Permission::query()->findOrFail($permissionId);

        $this->deletingPermissionId = $permission->permission_id;
        $this->deletingPermissionName = $permission->name;
        $this->deletingAssignedToSuperadmin = $permission->roles()
            ->where('roles.role_name', 'superadmin')
            ->exists();
    }

    public function confirmDelete(): void
    {
        Gate::authorize('manage-rbac');

        if ($this->deletingPermissionId === null) {
            $this->reset('deletingPermissionId', 'deletingPermissionName', 'deletingAssignedToSuperadmin');

            return;
        }

        $permission = Permission::query()->findOrFail($this->deletingPermissionId);
        $permissionId = $permission->permission_id;
        $permissionName = $permission->name;

        $affectedRoleIds = DB::transaction(function () use ($permission, $permissionId, $permissionName): array {
            $affectedRoleIds = $permission->roles()
                ->pluck('roles.role_id')
                ->all();

            $permission->delete();

            TransactionLogService::logRbacOperation('RBAC_PERMISSION_DELETED', [
                'record_id' => $permissionId,
                'before' => ['name' => $permissionName],
                'after' => null,
                'timestamp' => now()->utc()->toISOString(),
            ]);

            return $affectedRoleIds;
        });

        DB::afterCommit(static function () use ($affectedRoleIds): void {
            foreach ($affectedRoleIds as $roleId) {
                TransactionLogService::invalidateRolePermissionCache((int) $roleId);
            }
        });

        $this->reset('deletingPermissionId', 'deletingPermissionName', 'deletingAssignedToSuperadmin');
        unset($this->permissions);
    }

    #[Computed(persist: true, seconds: 300)]
    public function permissions(): LengthAwarePaginator
    {
        return Permission::query()
            ->withCount('roles')
            ->when($this->search, fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->orderBy('name')
            ->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.superadmin.permissions.index', [
            'permissions' => $this->permissions,
        ]);
    }
}
