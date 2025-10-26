<?php

namespace App\Livewire\Superadmin\Roles;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast, WithPagination;

    #[Title('Superadmin - Roles & Permissions')]
    #[Layout('components.layouts.superadmin')]

    // Search and filter
    public $search = '';

    // Cache duration
    protected $cacheDuration = 10;

    // Drawer states
    public $showRoleDrawer = false;

    public $showDeleteModal = false;

    // Form data using arrays
    public $roleForm = [
        'id' => null,
        'name' => '',
        'description' => '',
        'permissions' => [],
    ];

    // Available permissions
    public $allPermissions = [];

    // Delete data
    public $deletingRoleId = null;

    public function mount()
    {
        $this->allPermissions = [
            'user_management' => 'User Management',
            'event_management' => 'Event Management',
            'approval_workflow' => 'Approval Workflow',
            'system_settings' => 'System Settings',
            'reports' => 'Reports & Analytics',
            'logs' => 'Transaction Logs',
        ];
    }

    public function render()
    {
        return view('livewire.superadmin.roles.index')->with([
            'roles' => $this->getRoles(),
        ]);
    }

    protected function getRoles()
    {
        return Cache::remember('roles_data', $this->cacheDuration, function () {
            return [
                [
                    'id' => 'superadmin',
                    'name' => 'Super Admin',
                    'description' => 'Full system access and control',
                    'user_count' => User::where('role_id', User::ROLE_SUPERADMIN)->count(),
                    'permissions' => ['All Permissions'],
                ],
                [
                    'id' => 'osa',
                    'name' => 'OSA Staff',
                    'description' => 'Office of Student Affairs staff',
                    'user_count' => User::where('role_id', User::ROLE_OSA)->count(),
                    'permissions' => ['View Events', 'Approve Events', 'Manage Users'],
                ],
                [
                    'id' => 'gso',
                    'name' => 'GSO Staff',
                    'description' => 'General Services Office staff',
                    'user_count' => User::where('role_id', User::ROLE_GSO)->count(),
                    'permissions' => ['View Events', 'Final Approval', 'Resource Management'],
                ],
                [
                    'id' => 'student_org',
                    'name' => 'Student Organization',
                    'description' => 'Student organization members',
                    'user_count' => User::where('role_id', User::ROLE_STUDENT_ORG)->count(),
                    'permissions' => ['Create Events', 'View Own Events', 'Submit Requests'],
                ],
            ];
        });
    }

    public function openCreateRoleDrawer()
    {
        $this->resetRoleForm();
        $this->showRoleDrawer = true;
    }

    public function openEditRoleDrawer($roleId)
    {
        $this->loadRoleForm($roleId);
        $this->showRoleDrawer = true;
    }

    public function openDeleteModal($roleId)
    {
        $this->deletingRoleId = $roleId;
        $this->showDeleteModal = true;
    }

    public function loadRoleForm($roleId)
    {
        $role = collect($this->getRoles())->firstWhere('id', $roleId);

        if ($role) {
            $this->roleForm = [
                'id' => $role['id'],
                'name' => $role['name'],
                'description' => $role['description'],
                'permissions' => array_keys($this->allPermissions), // Simplified for demo
            ];
        }
    }

    public function saveRole()
    {
        if ($this->roleForm['id']) {
            $this->updateRole();
        } else {
            $this->createRole();
        }
    }

    public function createRole()
    {
        $this->validate([
            'roleForm.name' => 'required|string|max:255',
            'roleForm.description' => 'required|string|max:500',
            'roleForm.permissions' => 'required|array|min:1',
        ], [
            'roleForm.name.required' => 'Role name is required.',
            'roleForm.description.required' => 'Role description is required.',
            'roleForm.permissions.required' => 'At least one permission must be selected.',
            'roleForm.permissions.min' => 'At least one permission must be selected.',
        ]);

        // In a real application, you would create the role in the database

        $this->resetRoleForm();
        $this->showRoleDrawer = false;
        $this->clearRolesCache();

        $this->success('Role created successfully!', position: 'toast-top');
    }

    public function updateRole()
    {
        $this->validate([
            'roleForm.name' => 'required|string|max:255',
            'roleForm.description' => 'required|string|max:500',
            'roleForm.permissions' => 'required|array|min:1',
        ], [
            'roleForm.name.required' => 'Role name is required.',
            'roleForm.description.required' => 'Role description is required.',
            'roleForm.permissions.required' => 'At least one permission must be selected.',
            'roleForm.permissions.min' => 'At least one permission must be selected.',
        ]);

        // In a real application, you would update the role in the database

        $this->resetRoleForm();
        $this->showRoleDrawer = false;
        $this->clearRolesCache();

        $this->success('Role updated successfully!', position: 'toast-top');
    }

    public function confirmDeleteRole()
    {
        // In a real application, you would delete the role from the database

        $this->deletingRoleId = null;
        $this->showDeleteModal = false;
        $this->clearRolesCache();

        $this->success('Role deleted successfully!', position: 'toast-top');
    }

    public function resetRoleForm()
    {
        $this->roleForm = [
            'id' => null,
            'name' => '',
            'description' => '',
            'permissions' => [],
        ];
        $this->resetErrorBag();
    }

    public function resetDeleteModal()
    {
        $this->deletingRoleId = null;
        $this->showDeleteModal = false;
    }

    protected function clearRolesCache()
    {
        Cache::forget('roles_data');
    }

    public function refreshRoles()
    {
        $this->clearRolesCache();
        $this->success('Roles data refreshed!', position: 'toast-top');
    }
}
