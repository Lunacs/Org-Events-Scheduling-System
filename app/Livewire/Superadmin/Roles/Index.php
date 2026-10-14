<?php

namespace App\Livewire\Superadmin\Roles;

use App\Models\Roles;
use App\Models\User;
use App\Services\TransactionLogService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
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

    #[Validate('required|string|max:100|unique:roles,role_name')]
    public string $createName = '';

    #[Validate('required|in:viewer,osa,gso,student-org')]
    public string $createPortal = Roles::VIEWER_PORTAL;

    public ?int $changingPortalRoleId = null;

    public ?string $changingPortalRoleName = null;

    public string $changingPortal = Roles::VIEWER_PORTAL;

    public ?int $deletingRoleId = null;

    public ?string $deletingRoleName = null;

    public string $editingName = '';

    public ?int $editingRoleId = null;

    #[Computed(persist: true, seconds: 300)]
    public function roles(): LengthAwarePaginator
    {
        return Roles::query()
            ->withCount('users')
            ->when($this->search, function ($query): void {
                $query->where('role_name', 'like', '%'.$this->search.'%');
            })
            ->orderBy('role_name')
            ->paginate(15);
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
        unset($this->roles);
    }

    public function updatedPage(int $page): void
    {
        unset($this->roles);
    }

    public function createRole(): void
    {
        Gate::authorize('manage-rbac');

        $validated = $this->validate();

        $role = DB::transaction(function () use ($validated): Roles {
            $role = Roles::query()->create([
                'role_name' => $validated['createName'],
                'base_portal' => $validated['createPortal'],
            ]);

            TransactionLogService::logRbacOperation('RBAC_ROLE_CREATED', [
                'record_id' => $role->role_id,
                'before' => null,
                'after' => ['role_name' => $role->role_name, 'base_portal' => $role->base_portal],
                'timestamp' => now()->utc()->toISOString(),
            ]);

            return $role;
        });

        $this->reset('createName', 'createPortal');
        $this->resetValidation(['createName', 'createPortal']);
        unset($this->roles);
    }

    public function openPortalModal(int $roleId): void
    {
        $role = Roles::query()->findOrFail($roleId);

        $this->changingPortalRoleId = $role->role_id;
        $this->changingPortalRoleName = $role->role_name;
        $this->changingPortal = $role->portal();
        $this->resetValidation('changingPortal');
    }

    public function closePortalModal(): void
    {
        $this->reset('changingPortalRoleId', 'changingPortalRoleName', 'changingPortal');
        $this->resetValidation('changingPortal');
    }

    public function confirmPortalChange(): void
    {
        Gate::authorize('manage-rbac');

        $role = Roles::query()->findOrFail($this->changingPortalRoleId);

        if ($role->isProtected()) {
            $this->addError('changingPortal', "The {$role->role_name} role is protected and its portal cannot be changed.");

            return;
        }

        $validated = $this->validate([
            'changingPortal' => ['required', Rule::in(array_keys(Roles::SELECTABLE_PORTALS))],
        ]);
        $previousPortal = $role->portal();
        $newPortal = $validated['changingPortal'];

        if ($previousPortal === $newPortal) {
            $this->closePortalModal();

            return;
        }

        DB::transaction(function () use ($role, $previousPortal, $newPortal): void {
            $role->update(['base_portal' => $newPortal]);

            $assignedUsers = User::query()
                ->where('role_id', $role->role_id)
                ->lockForUpdate()
                ->get();

            foreach ($assignedUsers as $assignedUser) {
                $this->revokeUserCredentials($assignedUser);
            }

            TransactionLogService::logRbacOperation('RBAC_ROLE_PORTAL_CHANGED', [
                'record_id' => $role->role_id,
                'before' => ['base_portal' => $previousPortal],
                'after' => ['base_portal' => $newPortal],
                'timestamp' => now()->utc()->toISOString(),
            ]);
        });

        DB::afterCommit(static function () use ($role): void {
            TransactionLogService::invalidateRolePermissionCache($role->role_id);
        });

        $this->closePortalModal();
        unset($this->roles);
    }

    public function startRename(int $roleId): void
    {
        $role = Roles::query()->findOrFail($roleId);

        $this->editingRoleId = $role->role_id;
        $this->editingName = $role->role_name;
        $this->resetValidation('editingName');
    }

    public function saveRename(): void
    {
        Gate::authorize('manage-rbac');

        $role = Roles::query()->findOrFail($this->editingRoleId);

        if ($role->isProtected()) {
            $this->addError('editingName', "The {$role->role_name} role is protected and cannot be renamed.");

            return;
        }

        $validated = $this->validate([
            'editingName' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'role_name')->ignore($this->editingRoleId, 'role_id'),
            ],
        ]);
        $previousRoleName = $role->role_name;
        $newRoleName = $validated['editingName'];

        DB::transaction(function () use ($role, $previousRoleName, $newRoleName): void {
            $role->update(['role_name' => $newRoleName]);

            TransactionLogService::logRbacOperation('RBAC_ROLE_RENAMED', [
                'record_id' => $role->role_id,
                'before' => ['role_name' => $previousRoleName],
                'after' => ['role_name' => $newRoleName],
                'timestamp' => now()->utc()->toISOString(),
            ]);
        });

        DB::afterCommit(static function () use ($previousRoleName, $newRoleName): void {
            User::clearRoleIdCache();
            Cache::forget("role_id_{$previousRoleName}");
            Cache::forget("role_id_{$newRoleName}");
        });

        $this->reset('editingName', 'editingRoleId');
        $this->resetValidation('editingName');
        unset($this->roles);
    }

    public function openDeleteModal(int $roleId): void
    {
        $role = Roles::query()->findOrFail($roleId);

        $this->deletingRoleId = $role->role_id;
        $this->deletingRoleName = $role->role_name;
        $this->resetValidation('deletingRoleId');
    }

    public function confirmDelete(): void
    {
        Gate::authorize('manage-rbac');

        $role = Roles::query()->findOrFail($this->deletingRoleId);

        if ($role->isProtected()) {
            $this->addError('deletingRoleId', "The {$role->role_name} role is protected and cannot be deleted.");

            return;
        }

        $fallbackRoleName = config('rbac.fallback_role', 'student-org');
        $fallbackRole = Roles::query()
            ->where('role_name', $fallbackRoleName)
            ->first();

        if (! $fallbackRole) {
            $this->addError('deletingRoleId', "Fallback role '{$fallbackRoleName}' not found. Cannot delete role.");

            return;
        }

        if ($fallbackRole->is($role)) {
            $this->addError('deletingRoleId', "The {$role->role_name} role cannot be its own fallback role.");

            return;
        }

        // Users moved to the fallback role must be able to work in its portal
        // (an organization for student-org, an office for GSO).
        $stranded = User::withTrashed()
            ->where('role_id', $role->role_id)
            ->get(['user_id', 'org_id', 'office_id'])
            ->filter(fn (User $user): bool => $fallbackRole->missingAffiliationFor($user) !== null)
            ->count();

        if ($stranded > 0) {
            $this->addError('deletingRoleId', "{$stranded} user(s) in this role have no {$fallbackRole->missingAffiliationFor(new User)} for the fallback role '{$fallbackRole->role_name}'. Reassign them first.");

            return;
        }

        $roleId = $role->role_id;
        $roleName = $role->role_name;

        DB::transaction(function () use ($fallbackRole, $role, $roleId, $roleName): void {
            $activeAssignedUsers = User::query()
                ->where('role_id', $roleId)
                ->lockForUpdate()
                ->get();

            User::withTrashed()
                ->where('role_id', $roleId)
                ->update(['role_id' => $fallbackRole->role_id]);

            foreach ($activeAssignedUsers as $activeAssignedUser) {
                $this->revokeUserCredentials($activeAssignedUser);
            }

            $role->delete();

            TransactionLogService::logRbacOperation('RBAC_ROLE_DELETED', [
                'record_id' => $roleId,
                'before' => ['role_name' => $roleName],
                'after' => null,
                'timestamp' => now()->utc()->toISOString(),
            ]);
        });

        DB::afterCommit(static function () use ($roleId, $roleName): void {
            TransactionLogService::invalidateRolePermissionCache($roleId);
            User::clearRoleIdCache();
            Cache::forget("role_id_{$roleName}");
        });

        $this->reset('deletingRoleId', 'deletingRoleName');
        $this->resetValidation('deletingRoleId');
        unset($this->roles);
    }

    private function revokeUserCredentials(User $user): void
    {
        $user->forceFill([
            'remember_token' => Str::random(60),
        ])->save();

        if (config('session.driver') === 'database') {
            DB::table(config('session.table', 'sessions'))
                ->where('user_id', $user->user_id)
                ->delete();
        }
    }

    public function render(): View
    {
        return view('livewire.superadmin.roles.index', [
            'roles' => $this->roles,
        ]);
    }
}
