<?php

namespace App\Livewire\Superadmin\Users;

use App\Livewire\Forms\CreateUserForm;
use App\Livewire\Forms\EditUserForm;
use App\Models\Office;
use App\Models\Positions;
use App\Models\Roles;
use App\Models\Student_Organization;
use App\Models\User;
use App\Notifications\UserCreatedNotification;
use App\Services\TransactionLogService;
use App\Support\Concerns\InteractsWithToasts as Toast;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Lazy]
class Index extends Component
{
    use Toast, WithPagination;

    #[Title('Superadmin - User Management')]
    #[Layout('components.layouts.superadmin')]
    public function placeholder()
    {
        return view('livewire.superadmin.placeholders.users');
    }

    // Search and filter properties with URL state
    #[Url(except: '')]
    public $search = '';

    #[Url(except: 'all')]
    public $roleFilter = 'all';

    public array $sortBy = ['column' => 'email_verified_at', 'direction' => 'desc'];

    public CreateUserForm $createForm;

    public EditUserForm $editForm;

    /** @var array<int, int|null> */
    public array $selectedRoleIds = [];

    public ?int $pendingSelfRoleChangeUserId = null;

    public ?int $pendingSelfRoleChangeRoleId = null;

    public ?int $pendingSelfRoleChangeOriginalRoleId = null;

    public bool $showSelfRoleChangeConfirmation = false;

    public function saveUserRole(int $userId): void
    {
        Gate::authorize('manage-rbac');

        $roleId = $this->selectedRoleId($userId);
        $user = User::query()
            ->select(['user_id', 'role_id'])
            ->find($userId);

        if (! $user) {
            $this->addError("selectedRoleIds.{$userId}", 'The selected user could not be found.');
            $this->error('User not found.', position: 'toast-top');

            return;
        }

        if ($user->role_id !== null && (int) $user->role_id === $roleId) {
            $this->info('This user already has the selected role.', position: 'toast-top');

            return;
        }

        if (Auth::id() !== null && (int) Auth::id() === (int) $user->user_id) {
            $this->pendingSelfRoleChangeUserId = $user->user_id;
            $this->pendingSelfRoleChangeRoleId = $roleId;
            $this->pendingSelfRoleChangeOriginalRoleId = $user->role_id === null ? null : (int) $user->role_id;
            $this->showSelfRoleChangeConfirmation = true;

            return;
        }

        $this->persistUserRoleChange($user->user_id, $roleId);
    }

    public function confirmSelfRoleChange(): void
    {
        Gate::authorize('manage-rbac');

        if ($this->pendingSelfRoleChangeUserId === null || $this->pendingSelfRoleChangeRoleId === null) {
            $this->error('There is no role change awaiting confirmation.', position: 'toast-top');

            return;
        }

        if (Auth::id() === null || (int) Auth::id() !== $this->pendingSelfRoleChangeUserId) {
            $this->cancelSelfRoleChange();
            $this->error('You can only confirm your own role change.', position: 'toast-top');

            return;
        }

        $this->selectedRoleIds[$this->pendingSelfRoleChangeUserId] = $this->pendingSelfRoleChangeRoleId;

        $this->persistUserRoleChange(
            $this->pendingSelfRoleChangeUserId,
            $this->selectedRoleId($this->pendingSelfRoleChangeUserId),
        );
    }

    public function cancelSelfRoleChange(): void
    {
        if ($this->pendingSelfRoleChangeUserId !== null && $this->pendingSelfRoleChangeOriginalRoleId !== null) {
            $this->selectedRoleIds[$this->pendingSelfRoleChangeUserId] = $this->pendingSelfRoleChangeOriginalRoleId;
        }

        $this->clearSelfRoleChangeConfirmation();
    }

    private function selectedRoleId(int $userId): int
    {
        $validated = $this->validate([
            "selectedRoleIds.{$userId}" => [
                'required',
                'integer',
                Rule::exists('roles', 'role_id'),
            ],
        ]);

        return (int) $validated['selectedRoleIds'][$userId];
    }

    /**
     * @return array{previousRoleId: int|null, status: 'changed'|'unchanged'|'last_superadmin'}
     */
    private function changeUserRole(int $userId, int $roleId): array
    {
        return DB::transaction(function () use ($userId, $roleId): array {
            $user = User::query()
                ->lockForUpdate()
                ->findOrFail($userId);
            $role = Roles::query()
                ->lockForUpdate()
                ->findOrFail($roleId);
            $previousRoleId = $user->role_id === null ? null : (int) $user->role_id;
            $targetRoleId = (int) $role->role_id;

            if ($previousRoleId === $targetRoleId) {
                return [
                    'previousRoleId' => $previousRoleId,
                    'status' => 'unchanged',
                ];
            }

            $superadminRoleId = Roles::query()
                ->where('role_name', 'superadmin')
                ->lockForUpdate()
                ->value('role_id');

            if (
                $superadminRoleId !== null
                && $previousRoleId === (int) $superadminRoleId
                && $targetRoleId !== (int) $superadminRoleId
                && User::query()
                    ->where('role_id', $superadminRoleId)
                    ->lockForUpdate()
                    ->get(['user_id'])
                    ->count() <= 1
            ) {
                return [
                    'previousRoleId' => $previousRoleId,
                    'status' => 'last_superadmin',
                ];
            }

            $user->role_id = $targetRoleId;
            $user->save();

            TransactionLogService::logRbacOperation('RBAC_USER_ROLE_CHANGED', [
                'record_id' => $user->user_id,
                'before' => ['role_id' => $previousRoleId],
                'after' => ['role_id' => $targetRoleId],
                'timestamp' => now()->utc()->toISOString(),
            ]);

            $this->revokeUserCredentials($user);

            return [
                'previousRoleId' => $previousRoleId,
                'status' => 'changed',
            ];
        });
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

    private function logoutCurrentUser(): void
    {
        Auth::guard('web')->logout();
        Session::invalidate();
        Session::regenerateToken();
    }

    private function persistUserRoleChange(int $userId, int $roleId): void
    {
        $isCurrentUser = Auth::id() !== null && (int) Auth::id() === $userId;

        try {
            $roleChange = $this->changeUserRole($userId, $roleId);
        } catch (\Throwable $exception) {
            $this->restoreSelectedRoleId($userId);
            $this->clearSelfRoleChangeConfirmation();

            Log::error('RBAC user role change failed.', [
                'user_id' => $userId,
                'role_id' => $roleId,
                'error' => $exception->getMessage(),
            ]);
            $this->error("Unable to update the user's role. Please try again.", position: 'toast-top');

            return;
        }

        if ($roleChange['status'] === 'unchanged') {
            $this->clearSelfRoleChangeConfirmation();
            $this->info('This user already has the selected role.', position: 'toast-top');

            return;
        }

        if ($roleChange['status'] === 'last_superadmin') {
            $this->restoreSelectedRoleId($userId);
            $this->clearSelfRoleChangeConfirmation();
            $this->error('Cannot remove the last superadmin user.', position: 'toast-top');

            return;
        }

        $this->clearSelfRoleChangeConfirmation();

        if ($isCurrentUser) {
            $this->logoutCurrentUser();
        }

        unset($this->users);
        $this->success('User role updated successfully.', position: 'toast-top');
    }

    /**
     * Reset a row's role selector to the role currently stored for the user.
     */
    private function restoreSelectedRoleId(int $userId): void
    {
        $storedRoleId = User::query()->whereKey($userId)->value('role_id');

        $this->selectedRoleIds[$userId] = $storedRoleId === null ? null : (int) $storedRoleId;
    }

    /**
     * Seed role selector state for users on the current page that have no selection yet.
     */
    private function fillSelectedRoleIds(LengthAwarePaginator $users): void
    {
        foreach ($users as $user) {
            if (! array_key_exists($user->user_id, $this->selectedRoleIds)) {
                $this->selectedRoleIds[$user->user_id] = $user->role_id === null ? null : (int) $user->role_id;
            }
        }
    }

    private function clearSelfRoleChangeConfirmation(): void
    {
        $this->reset([
            'pendingSelfRoleChangeUserId',
            'pendingSelfRoleChangeRoleId',
            'pendingSelfRoleChangeOriginalRoleId',
            'showSelfRoleChangeConfirmation',
        ]);
    }

    public function updated($property, $value)
    {
        // Validate email in real-time (rules are dynamic based on role)
        if ($property === 'createForm.email') {
            $this->resetErrorBag('createForm.email');
            $this->createForm->validateOnly('email');
        }

        if ($property === 'createForm.role') {
            // Re-validate email when role changes (for @plv.edu.ph rule)
            if (! empty($this->createForm->email)) {
                $this->resetErrorBag('createForm.email');
                $this->createForm->validateOnly('email');
            }
        }

        if ($property === 'editForm.email') {
            $this->resetErrorBag('editForm.email');
            $this->editForm->validateOnly('email');
        }

        if ($property === 'editForm.role') {
            // Re-validate email when role changes (for @plv.edu.ph rule)
            if (! empty($this->editForm->email)) {
                $this->resetErrorBag('editForm.email');
                $this->editForm->validateOnly('email');
            }
        }

        // Validate password fields on blur
        if ($property === 'createForm.password') {
            $this->resetErrorBag('createForm.password');
            $this->createForm->validateOnly('password');
        }

        if ($property === 'createForm.password_confirmation') {
            $this->resetErrorBag('createForm.password');
            $this->resetErrorBag('createForm.password_confirmation');
            $this->createForm->validateOnly('password');
        }

        if ($property === 'editForm.password') {
            if (! empty($this->editForm->password)) {
                $this->resetErrorBag('editForm.password');
                $this->editForm->validateOnly('password');
            }
        }

        if ($property === 'editForm.password_confirmation') {
            // Only validate if password is provided
            if (! empty($this->editForm->password)) {
                $this->resetErrorBag('editForm.password');
                $this->resetErrorBag('editForm.password_confirmation');
                $this->editForm->validateOnly('password');
            } else {
                // Clear password errors if both fields are empty
                $this->resetErrorBag('editForm.password');
                $this->resetErrorBag('editForm.password_confirmation');
            }
        }

        // Validate phone on blur
        if ($property === 'createForm.phone') {
            $this->resetErrorBag('createForm.phone');
            $this->createForm->validateOnly('phone');
        }

        if ($property === 'editForm.phone') {
            $this->resetErrorBag('editForm.phone');
            $this->editForm->validateOnly('phone');
        }
    }

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
                    $q->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
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

        $this->editForm->fill([
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
        ]);
    }

    public function createUser(): void
    {
        Gate::authorize('manage-rbac');

        // Validate with the rules() method
        $this->createForm->validate();

        DB::beginTransaction();
        try {
            // Get the role ID from role name
            $roleId = User::getRoleId($this->createForm->role);

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
                'name' => $this->createForm->name,
                'email' => $this->createForm->email,
                'password' => Hash::make($this->createForm->password),
                'role_id' => $roleId,
                'phone' => $this->createForm->phone,
                'avatar_style' => 'big-ears',
                'avatar_seed' => $avatarSeeds[array_rand($avatarSeeds)],
            ];

            // Add role-specific data
            if ($this->createForm->portal() === 'student-org') {
                $userData['org_id'] = $this->createForm->org_name;
                $userData['position_id'] = $this->createForm->position;
                $userData['office_id'] = null;
            } elseif ($this->createForm->portal() === 'osa') {
                // Automatically assign OSA office
                $osaOffice = Office::where('office_code', 'OSA')->first();
                $userData['office_id'] = $osaOffice?->office_id;
                $userData['org_id'] = null;
                $userData['position_id'] = null;
            } elseif ($this->createForm->portal() === 'gso') {
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

            DB::commit();

            // Send notification after commit
            $superadmins = User::where('role_id', User::getRoleId('superadmin'))->get();
            foreach ($superadmins as $admin) {
                $admin->notify(new UserCreatedNotification($user, auth()->user()));
            }

            // Reset and close
            $this->createForm->reset();
            $this->dispatch('user-drawer-close');
            $this->success('User created successfully!', position: 'toast-bottom');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('User creation failed', [
                'error' => $e->getMessage(),
            ]);
            $this->error('Failed to create user: '.$e->getMessage(), position: 'toast-bottom');
        }
    }

    public function updateUser()
    {
        Gate::authorize('manage-rbac');

        $user = User::findOrFail($this->editForm->user_id);

        // Store original values for change tracking
        $originalUser = $user->toArray();
        $changes = [];

        // Validate
        $this->editForm->validate();

        DB::beginTransaction();
        try {
            // Track changes
            if ($originalUser['name'] !== $this->editForm->name) {
                $changes[] = "Name: {$originalUser['name']} → {$this->editForm->name}";
            }
            if ($originalUser['email'] !== $this->editForm->email) {
                $changes[] = "Email: {$originalUser['email']} → {$this->editForm->email}";
            }
            if ($originalUser['phone'] !== $this->editForm->phone) {
                $changes[] = "Phone: {$originalUser['phone']} → {$this->editForm->phone}";
            }

            // Update user data
            $user->name = $this->editForm->name;
            $user->email = $this->editForm->email;
            $user->phone = $this->editForm->phone;

            // Only update role if not superadmin
            if (! $user->isSuperAdmin()) {
                $newRoleId = User::getRoleId($this->editForm->role);

                if ($newRoleId === null) {
                    throw new \RuntimeException('The selected role could not be found.');
                }

                if ($originalUser['role_id'] !== $newRoleId) {
                    $roleChange = $this->changeUserRole($user->user_id, $newRoleId);

                    if ($roleChange['status'] === 'last_superadmin') {
                        throw new \RuntimeException('Cannot remove the last superadmin user.');
                    }

                    $changes[] = 'Role changed';
                }

                // Update role-specific data
                if ($this->editForm->portal() === 'student-org') {
                    $user->org_id = $this->editForm->org_name;
                    $user->position_id = $this->editForm->position;
                    $user->office_id = null;
                    if ($originalUser['org_id'] !== $this->editForm->org_name) {
                        $changes[] = 'Organization changed';
                    }
                    if ($originalUser['position_id'] !== $this->editForm->position) {
                        $changes[] = 'Position changed';
                    }
                } elseif ($this->editForm->portal() === 'osa') {
                    // Automatically assign OSA office
                    $osaOffice = Office::where('office_code', 'OSA')->first();
                    $user->office_id = $osaOffice?->office_id;
                    $user->org_id = null;
                    $user->position_id = null;
                    if ($originalUser['office_id'] !== $user->office_id) {
                        $changes[] = 'Office automatically assigned to OSA';
                    }
                } elseif ($this->editForm->portal() === 'gso') {
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
            if (! empty($this->editForm->password)) {
                $user->password = Hash::make($this->editForm->password);
                $changes[] = 'Password updated';
                // Log password change
                TransactionLogService::logAuthEvent('password_changed', $user, 'Password changed via admin interface');
            }

            $user->save();

            // Log the user update with changes
            TransactionLogService::logUserOperation('updated', $user, $changes);

            DB::commit();

            // Re-read the role selector for this row on the next render
            unset($this->selectedRoleIds[$user->user_id]);

            // Reset and close
            $this->editForm->reset();
            $this->dispatch('user-drawer-close');

            // Clear computed cache and refresh
            unset($this->users);
            $this->resetPage();

            $this->success('User updated successfully!', position: 'toast-top');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('User update failed', [
                'user_id' => $this->editForm->user_id,
                'error' => $e->getMessage(),
            ]);
            $this->error('Failed to update user: '.$e->getMessage(), position: 'toast-top');
        }
    }

    public function resetCreateForm()
    {
        $this->createForm->reset();
        $this->resetErrorBag();
    }

    public function resetEditForm()
    {
        $this->editForm->reset();
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

    public function confirmDelete(): void
    {
        Gate::authorize('manage-rbac');

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

        DB::beginTransaction();
        try {
            // Log the user deletion before deleting
            TransactionLogService::logUserOperation('deleted', $user);

            $user->delete();

            DB::commit();

            // Close modal and refresh
            $this->closeDeleteModal();

            // Clear computed cache and refresh
            unset($this->users, $this->selectedRoleIds[$user->user_id]);
            $this->resetPage();

            $this->success('User deleted successfully!', position: 'toast-top');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('User deletion failed', [
                'user_id' => $this->deletingUserId,
                'error' => $e->getMessage(),
            ]);
            $this->error('Failed to delete user: '.$e->getMessage(), position: 'toast-top');
            $this->closeDeleteModal();
        }
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

    public function hasActiveFilters()
    {
        return $this->search !== '' || $this->roleFilter !== 'all';
    }

    public function hasCreateFormErrors()
    {
        // Check for any error keys starting with createForm.
        $errors = $this->getErrorBag();
        foreach ($errors->keys() as $key) {
            if (str_starts_with($key, 'createForm.')) {
                return true;
            }
        }

        return false;
    }

    public function hasEditFormErrors()
    {
        // Check for any error keys starting with editForm.
        $errors = $this->getErrorBag();
        foreach ($errors->keys() as $key) {
            if (str_starts_with($key, 'editForm.')) {
                return true;
            }
        }

        return false;
    }

    public function isCreateFormValid()
    {
        // Check if all required fields are filled and no errors exist
        return ! empty($this->createForm->name)
            && ! empty($this->createForm->email)
            && ! empty($this->createForm->password)
            && ! empty($this->createForm->password_confirmation)
            && ! empty($this->createForm->role)
            && ! empty($this->createForm->phone)
            && ! $this->hasCreateFormErrors();
    }

    public function isEditFormValid()
    {
        // Check if all required fields are filled and no errors exist
        return ! empty($this->editForm->name)
            && ! empty($this->editForm->email)
            && ! empty($this->editForm->role)
            && ! empty($this->editForm->phone)
            && ! $this->hasEditFormErrors();
    }

    public function render()
    {
        $this->fillSelectedRoleIds($this->users);

        return view('livewire.superadmin.users.index')
            ->with([
                'users' => $this->users,
                'headers' => $this->headers,
                'roles' => $this->roles,
                'availableRoles' => $this->availableRoles,
                'positions' => $this->positions,
                'organizations' => $this->organizations,
                'hasActiveFilters' => $this->hasActiveFilters(),
            ]);
    }

    #[Computed(persist: true, seconds: 1800)] // Cache for 30 minutes
    public function roles()
    {
        return Roles::select(['role_id', 'role_name'])
            ->where('role_name', '!=', 'superadmin')
            ->get();
    }

    #[Computed]
    public function availableRoles(): Collection
    {
        return Roles::query()
            ->select(['role_id', 'role_name'])
            ->orderBy('role_name')
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
