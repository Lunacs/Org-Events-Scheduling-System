<?php

namespace App\Livewire\Superadmin\Users;

use App\Models\User;
use App\Services\TransactionLogService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule as ValidationRule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithPagination;

    #[Title('Superadmin - User Management')]
    #[Layout('components.layouts.superadmin')]

    // Search and filter properties
    public $search = '';

    public $roleFilter = 'all';

    public array $sortBy = ['column' => 'email_verified_at', 'direction' => 'desc'];


    // Form data using arrays for better organization
    public $createForm = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'role' => '',
    ];

    public $editForm = [
        'user_id' => null,
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'role' => '',
        'is_superadmin' => false,
    ];

    // Delete data
    public $deletingUserId = null;

    public $deletingUser = null;

    public $deletingUserName = '';

    // Cache users query for better performance
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->with(['studentOrganization:org_id,org_name', 'office:office_id,office_name'])
            ->select(['user_id', 'name', 'email', 'role_id', 'email_verified_at', 'org_id', 'office_id'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->roleFilter !== 'all', function ($query) {
                $query->where('role_id', $this->roleFilter);
            })
            ->orderBy(...array_values($this->sortBy))
            ->paginate(10);
    }

    #[Computed]
    public function headers(): array
    {
        return [
            ['key' => 'name', 'label' => 'Name', 'sortable' => true],
            ['key' => 'email', 'label' => 'Email', 'sortable' => true],
            ['key' => 'role', 'label' => 'Role', 'sortable' => true],
            ['key' => 'status', 'label' => 'Status'],
            ['key' => 'organization', 'label' => 'Organization/Office'],
            ['key' => 'actions', 'label' => 'Actions', 'sortable' => false, 'class' => 'text-center'],
        ];
    }

    public function loadEditForm($userId)
    {
        $user = User::select(['user_id', 'name', 'email', 'role_id'])->findOrFail($userId);

        $this->editForm = [
            'user_id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'role' => $user->role_id,
            'is_superadmin' => $user->role_id === User::ROLE_SUPERADMIN,
        ];
    }

    public function createUser()
    {
        $rules = [
            'createForm.name' => 'required|string|max:255',
            'createForm.email' => 'required|email|unique:users,email|ends_with:plv.edu.ph',
            'createForm.password' => 'required|string|min:8',
            'createForm.password_confirmation' => 'required|same:createForm.password',
            'createForm.role' => 'required|in:osa,gso,student_org',
        ];

        $this->validate($rules, [
            'createForm.email.ends_with' => 'Email must end with @plv.edu.ph',
            'createForm.password_confirmation.same' => 'Password confirmation does not match.',
            'createForm.password.min' => 'Password must be at least 8 characters.',
        ]);

        // Create user
        $user = User::create([
            'name' => $this->createForm['name'],
            'email' => $this->createForm['email'],
            'password' => Hash::make($this->createForm['password']),
            'role_id' => $this->createForm['role'],
        ]);

        // Log the user creation
        TransactionLogService::logUserOperation('created', $user);

        // Reset and close
        $this->resetCreateForm();
        $this->dispatch('user-drawer-close');

        // Clear computed cache and refresh
        unset($this->users);
        $this->resetPage();

        $this->success('User created successfully!', position: 'toast-top');
    }

    public function updateUser()
    {
        $user = User::findOrFail($this->editForm['user_id']);

        // Store original values for change tracking
        $originalUser = $user->toArray();
        $changes = [];

        // Dynamic validation rules
        $rules = [
            'editForm.name' => 'required|string|max:255',
            'editForm.email' => [
                'required',
                'email',
                'ends_with:plv.edu.ph',
                ValidationRule::unique('users', 'email')->ignore($user->user_id, 'user_id'),
            ],
        ];

        // Only add role validation if not editing a superadmin
        if ($user->role_id !== User::ROLE_SUPERADMIN) {
            $rules['editForm.role'] = 'required|in:osa,gso,student_org';
        }

        // Only validate password if it's provided
        if (! empty($this->editForm['password'])) {
            $rules['editForm.password'] = 'required|string|min:8';
            $rules['editForm.password_confirmation'] = 'required|same:editForm.password';
        }

        $this->validate($rules, [
            'editForm.email.ends_with' => 'Email must end with @plv.edu.ph',
            'editForm.password_confirmation.same' => 'Password confirmation does not match.',
            'editForm.password.min' => 'Password must be at least 8 characters.',
        ]);

        // Track changes
        if ($originalUser['name'] !== $this->editForm['name']) {
            $changes[] = "Name: {$originalUser['name']} → {$this->editForm['name']}";
        }
        if ($originalUser['email'] !== $this->editForm['email']) {
            $changes[] = "Email: {$originalUser['email']} → {$this->editForm['email']}";
        }
        if ($user->role_id !== User::ROLE_SUPERADMIN && $originalUser['role_id'] !== $this->editForm['role']) {
            $changes[] = "Role: {$originalUser['role_id']} → {$this->editForm['role']}";
        }

        // Update user data
        $user->name = $this->editForm['name'];
        $user->email = $this->editForm['email'];

        // Only update role if not superadmin
        if ($user->role_id !== User::ROLE_SUPERADMIN) {
            $user->role_id = $this->editForm['role'];
        }

        // Only update password if provided
        if (! empty($this->editForm['password'])) {
            $user->password = Hash::make($this->editForm['password']);
            $changes[] = 'Password updated';
            // Log password change
            TransactionLogService::logAuthEvent('password_changed', $user, 'Password changed via admin interface');
        }

        $user->save();

        // Log the user update with changes
        TransactionLogService::logUserOperation('updated', $user, $changes);

        // Reset and close
        $this->resetEditForm();
        $this->dispatch('user-drawer-close');

        // Clear computed cache and refresh
        unset($this->users);
        $this->resetPage();

        $this->success('User updated successfully!', position: 'toast-top');
    }

    public function resetCreateForm()
    {
        $this->createForm = [
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'role' => '',
        ];
        $this->resetErrorBag();
    }

    public function resetEditForm()
    {
        $this->editForm = [
            'user_id' => null,
            'name' => '',
            'email' => '',
            'password' => '',
            'password_confirmation' => '',
            'role' => '',
            'is_superadmin' => false,
        ];
        $this->resetErrorBag();
    }

    public function loadUserForDeletion($userId)
    {
        // Optimize query by only selecting needed fields
        $this->deletingUser = User::select(['user_id', 'name', 'role_id'])->find($userId);
        if ($this->deletingUser) {
            $this->deletingUserId = $userId;
            $this->deletingUserName = $this->deletingUser->name;
        }
    }

    public function resetDeleteModal()
    {
        // Only reset if there's actually data to reset
        $hasData = $this->deletingUserId || $this->deletingUser || ! empty($this->deletingUserName);

        if ($hasData) {
            $this->reset(['deletingUserId', 'deletingUser', 'deletingUserName']);
        }
    }

    public function confirmDelete()
    {
        if ($this->deletingUser && $this->deletingUser->role_id !== User::ROLE_SUPERADMIN) {
            // Log the user deletion before deleting
            TransactionLogService::logUserOperation('deleted', $this->deletingUser);

            $this->deletingUser->delete();

            // Reset and notify
            $this->reset(['deletingUserId', 'deletingUser', 'deletingUserName']);
            $this->dispatch('delete-modal-close');

            // Clear computed cache and refresh
            unset($this->users);
            $this->resetPage();

            $this->success('User deleted successfully!', position: 'toast-top');
        } else {
            $this->error('Cannot delete superadmin user!', position: 'toast-top');
            $this->dispatch('delete-modal-close');
        }
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
    }

    public function getRoleDisplayName($role)
    {
        return match ($role) {
            'osa' => 'OSA Staff',
            'gso' => 'GSO Staff',
            'student_org' => 'Student Org',
            'superadmin' => 'Super Admin',
            default => ucfirst($role)
        };
    }

    public function render()
    {
        return view('livewire.superadmin.users.index')
            ->with([
                'users' => $this->users(),
                'headers' => $this->headers(),
            ]);
    }
}
