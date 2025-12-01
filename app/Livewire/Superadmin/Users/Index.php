<?php

namespace App\Livewire\Superadmin\Users;

use App\Models\Office;
use App\Models\Positions;
use App\Models\Roles;
use App\Models\Student_Organization;
use App\Models\User;
use App\Services\TransactionLogService;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule as ValidationRule;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithPagination;

    #[Title('Superadmin - User Management')]
    #[Layout('components.layouts.superadmin')]

    // Search and filter properties with URL state
    #[Url(except: '')]
    public $search = '';

    #[Url(except: 'all')]
    public $roleFilter = 'all';

    public array $sortBy = ['column' => 'email_verified_at', 'direction' => 'desc'];

    // Form data using arrays for better organization
    public $createForm = [
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'role' => '',
        'org_name' => '',
        'position' => '',
        'phone' => '',
    ];

    public $editForm = [
        'user_id' => null,
        'name' => '',
        'email' => '',
        'password' => '',
        'password_confirmation' => '',
        'role' => '',
        'org_name' => '',
        'position' => '',
        'phone' => '',
        'is_superadmin' => false,
    ];

    // Delete modal
    public $showDeleteModal = false;

    public $deletingUserId = null;

    public $deletingUserName = '';

    // Optimized users query with proper select and eager loading
    #[Computed]
    public function users(): LengthAwarePaginator
    {
        return User::query()
            ->with([
                'studentOrganization:org_id,org_name,logo',
                'office:office_id,office_name',
                'role:role_id,role_name',
            ])
            ->select(['user_id', 'name', 'email', 'role_id', 'email_verified_at', 'org_id', 'office_id'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%')
                        ->orWhere('email', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->roleFilter !== 'all', function ($query) {
                $query->where('role_id', User::getRoleId($this->roleFilter));
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
            ['key' => 'role_id', 'label' => 'Role', 'sortable' => true],
            ['key' => 'email_verified_at', 'label' => 'Status', 'sortable' => true],
            ['key' => 'organization', 'label' => 'Organization/Office', 'sortable' => false],
            ['key' => 'actions', 'label' => 'Actions', 'sortable' => false, 'class' => 'text-center'],
        ];
    }

    public function loadEditForm($userId)
    {
        $user = User::with(['role', 'studentOrganization', 'position'])
            ->select(['user_id', 'name', 'email', 'role_id', 'org_id', 'position_id', 'phone', 'office_id'])
            ->findOrFail($userId);

        $this->editForm = [
            'user_id' => $user->user_id,
            'name' => $user->name,
            'email' => $user->email,
            'password' => '',
            'password_confirmation' => '',
            'role' => $user->role?->role_name ?? '',
            'org_name' => $user->org_id ?? '',
            'position' => $user->position_id ?? '',
            'phone' => $user->phone ?? '',
            'is_superadmin' => $user->isSuperAdmin(),
        ];
    }

    protected function rules()
    {
        return [
            'createForm.name' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'createForm.email' => 'required|email|unique:users,email|ends_with:plv.edu.ph',
            'createForm.password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()],
            'createForm.password_confirmation' => 'required',
            'createForm.role' => 'required|exists:roles,role_name',
            'createForm.org_name' => 'required_if:createForm.role,student-org|exists:student__organizations,org_id',
            'createForm.position' => 'required_if:createForm.role,student-org|exists:positions,position_id',
            'createForm.phone' => 'required|numeric|digits:11',
        ];
    }

    protected function messages()
    {
        return [
            'createForm.name.required' => 'Name is required.',
            'createForm.name.string' => 'Name must be a string.',
            'createForm.name.regex' => 'Name must only contain letters and spaces.',
            'createForm.name.min' => 'Name must be at least 3 characters.',
            'createForm.name.max' => 'Name cannot exceed 100 characters.',
            'createForm.email.required' => 'Email is required.',
            'createForm.email.email' => 'Please provide a valid email address.',
            'createForm.email.unique' => 'This email is already registered.',
            'createForm.email.ends_with' => 'Email must end with @plv.edu.ph',
            'createForm.password.required' => 'Password is required.',
            'createForm.password_confirmation.required' => 'Password confirmation is required.',
            'createForm.password_confirmation.same' => 'Password confirmation does not match.',
            'createForm.role.required' => 'Role is required.',
            'createForm.role.exists' => 'Selected role is invalid.',
            'createForm.org_name.required_if' => 'Organization is required for student org role.',
            'createForm.org_name.exists' => 'Selected organization is invalid.',
            'createForm.position.required_if' => 'Position is required for student org role.',
            'createForm.position.exists' => 'Selected position is invalid.',
            'createForm.phone.required' => 'Contact number is required.',
            'createForm.phone.numeric' => 'Contact number must contain only numbers.',
            'createForm.phone.digits' => 'Contact number must be exactly 11 digits.',
        ];
    }

    public function createUser()
    {
        // Validate with the rules() method
        $this->validate();

        // Get the role ID from role name
        $roleId = User::getRoleId($this->createForm['role']);

        // Define available avatar seeds (same as AvatarSelector)
        $avatarSeeds = [
            'felix',
            'aneka',
            'bob',
            'charlie',
            'david',
            'emma',
            'frank',
            'grace',
            'hannah',
            'ivan',
            'julia',
            'kevin',
            'laura',
            'mike',
            'nina',
            'oliver',
            'peter',
            'quinn',
            'rachel',
            'sam',
            'tina',
            'uma',
            'victor',
            'wendy',
        ];

        // Prepare user data
        $userData = [
            'name' => $this->createForm['name'],
            'email' => $this->createForm['email'],
            'password' => Hash::make($this->createForm['password']),
            'role_id' => $roleId,
            'phone' => $this->createForm['phone'],
            'avatar_style' => 'big-ears',
            'avatar_seed' => $avatarSeeds[array_rand($avatarSeeds)],
        ];

        // Add role-specific data
        if ($this->createForm['role'] === 'student-org') {
            $userData['org_id'] = $this->createForm['org_name'];
            $userData['position_id'] = $this->createForm['position'];
            $userData['office_id'] = null;
        } elseif ($this->createForm['role'] === 'osa') {
            // Automatically assign OSA office
            $osaOffice = Office::where('office_code', 'OSA')->first();
            $userData['office_id'] = $osaOffice?->office_id;
            $userData['org_id'] = null;
            $userData['position_id'] = null;
        } elseif ($this->createForm['role'] === 'gso') {
            // Automatically assign GSO office
            $gsoOffice = Office::where('office_code', 'GSO')->first();
            $userData['office_id'] = $gsoOffice?->office_id;
            $userData['org_id'] = null;
            $userData['position_id'] = null;
        } else {
            $userData['org_id'] = null;
            $userData['office_id'] = null;
            $userData['position_id'] = null;
        }

        // Create user
        $user = User::create($userData);

        // Log the user creation
        TransactionLogService::logUserOperation('created', $user);

        // Send notification to all superadmins
        $superadmins = User::where('role_id', User::getRoleId('superadmin'))->get();
        foreach ($superadmins as $admin) {
            $admin->notify(new \App\Notifications\UserCreatedNotification($user, auth()->user()));
        }

        // Reset and close
        $this->resetCreateForm();
        $this->dispatch('user-drawer-close');
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
            'editForm.name' => ['required', 'string', 'min:3', 'max:100', 'regex:/^[a-zA-Z\s]+$/'],
            'editForm.email' => [
                'required',
                'email',
                'ends_with:plv.edu.ph',
                ValidationRule::unique('users', 'email')->ignore($user->user_id, 'user_id'),
            ],
            'editForm.phone' => 'required|numeric|digits:11',
        ];

        // Only add role validation if not editing a superadmin
        if (! $user->isSuperAdmin()) {
            $rules['editForm.role'] = 'required|exists:roles,role_name';
            $rules['editForm.org_name'] = 'required_if:editForm.role,student-org|exists:student__organizations,org_id';
            $rules['editForm.position'] = 'required_if:editForm.role,student-org|exists:positions,position_id';
        }

        // Only validate password if it's provided
        if (! empty($this->editForm['password'])) {
            $rules['editForm.password'] = ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()];
            $rules['editForm.password_confirmation'] = 'required';
        }

        $this->validate($rules, [
            'editForm.name.required' => 'Name is required.',
            'editForm.name.regex' => 'Name must only contain letters and spaces.',
            'editForm.name.min' => 'Name must be at least 3 characters.',
            'editForm.name.max' => 'Name cannot exceed 100 characters.',
            'editForm.email.required' => 'Email is required.',
            'editForm.email.email' => 'Please provide a valid email address.',
            'editForm.email.ends_with' => 'Email must end with @plv.edu.ph',
            'editForm.password_confirmation.same' => 'Password confirmation does not match.',
            'editForm.role.exists' => 'Selected role is invalid.',
            'editForm.org_name.required_if' => 'Organization is required for student org role.',
            'editForm.org_name.exists' => 'Selected organization is invalid.',
            'editForm.position.required_if' => 'Position is required for student org role.',
            'editForm.position.exists' => 'Selected position is invalid.',
            'editForm.phone.required' => 'Contact number is required.',
            'editForm.phone.numeric' => 'Contact number must contain only numbers.',
            'editForm.phone.digits' => 'Contact number must be exactly 11 digits.',
        ]);

        // Track changes
        if ($originalUser['name'] !== $this->editForm['name']) {
            $changes[] = "Name: {$originalUser['name']} → {$this->editForm['name']}";
        }
        if ($originalUser['email'] !== $this->editForm['email']) {
            $changes[] = "Email: {$originalUser['email']} → {$this->editForm['email']}";
        }
        if ($originalUser['phone'] !== $this->editForm['phone']) {
            $changes[] = "Phone: {$originalUser['phone']} → {$this->editForm['phone']}";
        }

        // Update user data
        $user->name = $this->editForm['name'];
        $user->email = $this->editForm['email'];
        $user->phone = $this->editForm['phone'];

        // Only update role if not superadmin
        if (! $user->isSuperAdmin()) {
            $newRoleId = User::getRoleId($this->editForm['role']);
            if ($originalUser['role_id'] !== $newRoleId) {
                $changes[] = 'Role changed';
            }
            $user->role_id = $newRoleId;

            // Update role-specific data
            if ($this->editForm['role'] === 'student-org') {
                $user->org_id = $this->editForm['org_name'];
                $user->position_id = $this->editForm['position'];
                $user->office_id = null;
                if ($originalUser['org_id'] !== $this->editForm['org_name']) {
                    $changes[] = 'Organization changed';
                }
                if ($originalUser['position_id'] !== $this->editForm['position']) {
                    $changes[] = 'Position changed';
                }
            } elseif ($this->editForm['role'] === 'osa') {
                // Automatically assign OSA office
                $osaOffice = Office::where('office_code', 'OSA')->first();
                $user->office_id = $osaOffice?->office_id;
                $user->org_id = null;
                $user->position_id = null;
                if ($originalUser['office_id'] !== $user->office_id) {
                    $changes[] = 'Office automatically assigned to OSA';
                }
            } elseif ($this->editForm['role'] === 'gso') {
                // Automatically assign GSO office
                $gsoOffice = Office::where('office_code', 'GSO')->first();
                $user->office_id = $gsoOffice?->office_id;
                $user->org_id = null;
                $user->position_id = null;
                if ($originalUser['office_id'] !== $user->office_id) {
                    $changes[] = 'Office automatically assigned to GSO';
                }
            } else {
                $user->org_id = null;
                $user->office_id = null;
                $user->position_id = null;
            }
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
            'org_name' => '',
            'position' => '',
            'phone' => '',
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
            'org_name' => '',
            'position' => '',
            'phone' => '',
            'is_superadmin' => false,
        ];
        $this->resetErrorBag();
    }

    public function loadUserForDeletion($userId)
    {
        // This method is no longer needed - removed to follow Livewire mental model
        // Modal state is now managed purely client-side with Alpine.js
    }

    public function resetDeleteModal()
    {
        // This method is no longer needed - removed to follow Livewire mental model
        // Modal state is now managed purely client-side with Alpine.js
    }

    public function openDeleteModal($userId, $userName)
    {
        $this->deletingUserId = $userId;
        $this->deletingUserName = $userName;
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal()
    {
        $this->showDeleteModal = false;
        $this->reset(['deletingUserId', 'deletingUserName']);
    }

    public function confirmDelete()
    {
        // Fetch the user to delete
        $user = User::select(['user_id', 'name', 'role_id'])->find($this->deletingUserId);

        if (! $user) {
            $this->error('User not found!', position: 'toast-top');
            $this->closeDeleteModal();

            return;
        }

        if ($user->isSuperAdmin()) {
            $this->error('Cannot delete superadmin user!', position: 'toast-top');
            $this->closeDeleteModal();

            return;
        }

        // Log the user deletion before deleting
        TransactionLogService::logUserOperation('deleted', $user);

        $user->delete();

        // Close modal and refresh
        $this->closeDeleteModal();

        // Clear computed cache and refresh
        unset($this->users);
        $this->resetPage();

        $this->success('User deleted successfully!', position: 'toast-top');
    }

    public function updatedSearch()
    {
        $this->resetPage();
        unset($this->users); // Clear computed cache
    }

    public function updatedRoleFilter()
    {
        $this->resetPage();
        unset($this->users); // Clear computed cache
    }

    public function clearFilters()
    {
        $this->search = '';
        $this->roleFilter = 'all';
        $this->resetPage();
        unset($this->users); // Clear computed cache
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
                'users' => $this->users,
                'headers' => $this->headers,
                'roles' => $this->roles,
                'positions' => $this->positions,
                'organizations' => $this->organizations,
            ]);
    }

    #[Computed(persist: true, seconds: 1800)] // Cache for 30 minutes
    public function roles()
    {
        return Roles::select(['role_id', 'role_name'])
            ->where('role_name', '!=', 'superadmin')
            ->get();
    }

    #[Computed(persist: true, seconds: 1800)] // Cache for 30 minutes
    public function positions()
    {
        return Positions::select(['position_id', 'position_name'])
            ->orderBy('position_name')
            ->get();
    }

    #[Computed(persist: true, seconds: 1800)] // Cache for 30 minutes
    public function organizations()
    {
        return Student_Organization::select(['org_id', 'org_name', 'org_code'])
            ->orderBy('org_name')
            ->get();
    }
}
