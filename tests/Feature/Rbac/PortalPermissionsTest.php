<?php

use App\Livewire\Osa\EventCalendar as OsaEventCalendar;
use App\Livewire\Osa\TicketReview\Show as OsaTicketReviewShow;
use App\Livewire\Superadmin\Roles\Index as RolesIndex;
use App\Livewire\Superadmin\Users\Index as UsersIndex;
use App\Livewire\Viewer\Calendar as ViewerCalendar;
use App\Models\Permission;
use App\Models\Roles;
use App\Models\Ticket;
use App\Models\Transaction_Logs;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Services\TransactionLogService;
use App\Support\PermissionCatalog;
use App\Support\PortalNavigation;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Livewire\Livewire;

beforeEach(function (): void {
    // Register the permission gates against the seeded permissions for this test.
    (new AppServiceProvider($this->app))->boot();
});

/**
 * Create a custom role on a base portal with the given permissions.
 *
 * @param  list<string>  $permissionNames
 */
function portalRole(string $roleName, string $portal, array $permissionNames): Roles
{
    $role = Roles::query()->create(['role_name' => $roleName, 'base_portal' => $portal]);
    $role->permissions()->sync(Permission::query()->whereIn('name', $permissionNames)->pluck('permission_id'));

    return $role;
}

function userWithRole(Roles $role): User
{
    return User::factory()->create(['role_id' => $role->role_id, 'email_verified_at' => now()]);
}

function builtInRole(string $roleName): Roles
{
    return Roles::query()->where('role_name', $roleName)->sole();
}

function revokePermission(Roles $role, string $permissionName): void
{
    $role->permissions()->detach(Permission::query()->where('name', $permissionName)->value('permission_id'));
    TransactionLogService::invalidateRolePermissionCache($role->role_id);
}

it('lands a calendar-only security guard on the viewer calendar with a single sidebar entry', function (): void {
    $guard = userWithRole(portalRole('SecurityGuard', 'viewer', ['calendar.view']));

    expect($guard->portal())->toBe('viewer')
        ->and($guard->getDashboardRoute())->toBe('portal.calendar')
        ->and(array_column(PortalNavigation::itemsFor($guard), 'title'))->toBe(['Event Calendar']);

    $this->actingAs($guard)
        ->get(route('portal.calendar'))
        ->assertOk()
        ->assertSee('Viewer Portal')
        ->assertSee('Event Calendar');

    $this->actingAs($guard)->get(route('portal.dashboard'))->assertRedirect(route('portal.calendar'));
    $this->actingAs($guard)->get('/dashboard')->assertRedirect(route('portal.calendar'));
});

it('forbids viewer pages the role has not been granted', function (): void {
    $guard = userWithRole(portalRole('SecurityGuard', 'viewer', ['calendar.view']));

    $this->actingAs($guard)->get(route('portal.notifications'))->assertForbidden();
});

it('keeps a viewer role out of every office portal', function (string $routeName): void {
    $guard = userWithRole(portalRole('SecurityGuard', 'viewer', ['calendar.view']));

    $this->actingAs($guard)->get(route($routeName))->assertRedirect(route('portal.calendar'));
})->with(['admin.dashboard', 'admin.calendar', 'gso.dashboard', 'student-org.dashboard', 'superadmin.dashboard']);

it('shows the security guard calendar without the ticket link or notification bell', function (): void {
    $guard = userWithRole(portalRole('SecurityGuard', 'viewer', ['calendar.view']));

    $this->actingAs($guard);

    Livewire::withoutLazyLoading()
        ->test(ViewerCalendar::class)
        ->assertOk()
        ->assertDontSee('View Full Info');

    $this->get(route('portal.calendar'))->assertDontSeeHtml('wire:name="notification-dropdown"');
});

it('shows the calendar ticket link only to office users who can view tickets', function (): void {
    $osaUser = userWithRole(builtInRole('osa'));
    $this->actingAs($osaUser);

    Livewire::withoutLazyLoading()->test(OsaEventCalendar::class)->assertSee('View Full Info');

    revokePermission(builtInRole('osa'), 'tickets.view');

    Livewire::withoutLazyLoading()->test(OsaEventCalendar::class)->assertDontSee('View Full Info');
});

it('hides and forbids a built-in portal page once its permission is revoked', function (): void {
    $gsoRole = builtInRole('gso');
    $gsoUser = userWithRole($gsoRole);

    expect(array_column(PortalNavigation::itemsFor($gsoUser), 'route'))->toContain('gso.calendar');
    $this->actingAs($gsoUser)->get(route('gso.calendar'))->assertOk();

    revokePermission($gsoRole, 'calendar.view');

    expect(array_column(PortalNavigation::itemsFor($gsoUser->fresh()), 'route'))->not->toContain('gso.calendar');
    $this->actingAs($gsoUser->fresh())->get(route('gso.calendar'))->assertForbidden();
});

it('lands a role on its first permitted page and falls back to the no-access page', function (): void {
    $osaRole = builtInRole('osa');
    $osaUser = userWithRole($osaRole);

    expect($osaUser->getDashboardRoute())->toBe('admin.dashboard');

    revokePermission($osaRole, 'dashboard.view');
    expect($osaUser->fresh()->getDashboardRoute())->toBe('admin.ticket');

    $emptyViewer = userWithRole(portalRole('Unassigned Viewer', 'viewer', []));
    expect($emptyViewer->getDashboardRoute())->toBe(PortalNavigation::NO_ACCESS_ROUTE);
});

it('treats a custom role on the OSA portal as OSA for the ticket workflow', function (): void {
    $assistant = userWithRole(portalRole('OSA Assistant', 'osa', ['tickets.view', 'tickets.forward', 'calendar.view']));

    expect($assistant->isOSA())->toBeTrue()
        ->and($assistant->hasRole('osa'))->toBeFalse()
        ->and(User::query()->inPortal('osa')->pluck('user_id'))->toContain($assistant->user_id)
        ->and(User::query()->inPortal('viewer')->pluck('user_id'))->not->toContain($assistant->user_id);

    $this->actingAs($assistant)->get(route('admin.calendar'))->assertOk();
    $this->actingAs($assistant)->get(route('admin.reports'))->assertForbidden();
});

it('removes the approve action from an OSA-portal role without tickets.approve while forward still works', function (): void {
    $assistant = userWithRole(portalRole('OSA Assistant', 'osa', ['tickets.view', 'tickets.forward', 'tickets.reject']));
    $ticket = Ticket::factory()->received()->create();

    expect(Gate::forUser($assistant)->allows('approve', $ticket))->toBeFalse()
        ->and(Gate::forUser($assistant)->allows('forwardToGso', $ticket))->toBeTrue()
        ->and(Gate::forUser($assistant)->allows('requestRevision', $ticket))->toBeTrue();

    $this->actingAs($assistant);

    Livewire::test(OsaTicketReviewShow::class, ['ticketNumber' => $ticket->ticket_number])
        ->set('approvalRemarks', 'Looks good')
        ->call('approveTicket')
        ->assertForbidden();

    expect($ticket->fresh()->status)->toBe('received');
});

it('requires both the permission and the workflow stage for each ticket policy ability', function (string $ability, string $permission, string $status): void {
    $ticket = Ticket::factory()->create(['status' => $status]);
    $withPermission = userWithRole(portalRole("osa-with-{$permission}", 'osa', [$permission]));
    $withoutPermission = userWithRole(portalRole("osa-without-{$permission}", 'osa', []));

    expect(Gate::forUser($withPermission)->allows($ability, $ticket))->toBeTrue()
        ->and(Gate::forUser($withoutPermission)->allows($ability, $ticket))->toBeFalse();
})->with([
    'approve' => ['approve', 'tickets.approve', 'received'],
    'reject' => ['reject', 'tickets.reject', 'received'],
    'request revision' => ['requestRevision', 'tickets.reject', 'received'],
    'forward to GSO' => ['forwardToGso', 'tickets.forward', 'received'],
    'final approve' => ['finalApprove', 'tickets.approve', 'pending_osa_approval'],
    'final reject' => ['finalReject', 'tickets.reject', 'pending_osa_approval'],
]);

it('guards every portal page route with a permission middleware', function (): void {
    $unguardedRoutes = collect(Route::getRoutes()->getRoutes())
        ->filter(fn ($route): bool => collect(['admin/', 'gso/', 'student-org/', 'portal/'])
            ->contains(fn (string $prefix): bool => str_starts_with($route->uri(), $prefix)))
        ->reject(fn ($route): bool => in_array($route->getName(), [
            'admin.profile', 'gso.profile', 'student-org.profile', 'portal.profile', 'portal.dashboard',
            'admin.login', 'gso.login', 'student-org.login',
        ], true))
        ->reject(fn ($route): bool => collect($route->gatherMiddleware())
            ->contains(fn ($middleware): bool => is_string($middleware) && str_starts_with($middleware, 'can:')))
        ->map(fn ($route): string => (string) $route->getName())
        ->values()
        ->all();

    expect($unguardedRoutes)->toBe([]);
});

it('grants the new portal permissions once without restoring earlier manual revocations', function (): void {
    $gsoRole = builtInRole('gso');
    revokePermission($gsoRole, 'calendar.view');
    DB::table('role_permission')
        ->where('role_id', $gsoRole->role_id)
        ->where('permission_id', Permission::query()->where('name', 'dashboard.view')->value('permission_id'))
        ->delete();

    $migration = require database_path('migrations/2026_09_30_092901_seed_portal_permissions.php');
    $migration->up();
    $grantCountAfterFirstRun = DB::table('role_permission')->count();
    $migration->up();

    $gsoPermissions = $gsoRole->permissions()->pluck('name')->all();

    expect(DB::table('role_permission')->count())->toBe($grantCountAfterFirstRun)
        ->and(Permission::query()->count())->toBe(count(PermissionCatalog::PERMISSIONS))
        ->and($gsoPermissions)->toContain('dashboard.view', 'tickets.reject', 'notifications.view')
        ->and($gsoPermissions)->not->toContain('calendar.view')
        ->and(Permission::query()->where('name', 'tickets.comment')->value('label'))->toBe('Comment on tickets');
});

it('creates a custom role on the chosen base portal and logs it', function (): void {
    $this->actingAs(userWithRole(builtInRole('superadmin')));

    Livewire::test(RolesIndex::class)
        ->set('createName', 'SecurityGuard')
        ->set('createPortal', 'viewer')
        ->call('createRole')
        ->assertHasNoErrors()
        ->set('createName', 'Invalid Portal Role')
        ->set('createPortal', 'superadmin')
        ->call('createRole')
        ->assertHasErrors(['createPortal' => 'in']);

    $role = Roles::query()->where('role_name', 'SecurityGuard')->sole();

    expect($role->portal())->toBe('viewer')
        ->and(Roles::query()->where('role_name', 'Invalid Portal Role')->exists())->toBeFalse();
});

it('changes a custom role portal, signs its users out, and refuses built-in roles', function (): void {
    $this->actingAs(userWithRole(builtInRole('superadmin')));
    $role = portalRole('Front Desk', 'viewer', ['calendar.view']);
    $member = User::factory()->create(['role_id' => $role->role_id, 'remember_token' => 'front-desk-token']);

    Livewire::test(RolesIndex::class)
        ->call('openPortalModal', $role->role_id)
        ->assertSet('changingPortal', 'viewer')
        ->set('changingPortal', 'osa')
        ->call('confirmPortalChange')
        ->assertHasNoErrors()
        ->assertSet('changingPortalRoleId', null);

    $log = Transaction_Logs::query()->where('action', 'RBAC_ROLE_PORTAL_CHANGED')->sole();
    $payload = json_decode($log->details, true, flags: JSON_THROW_ON_ERROR);

    expect($role->fresh()->portal())->toBe('osa')
        ->and($member->fresh()->isOSA())->toBeTrue()
        ->and($member->fresh()->remember_token)->not->toBe('front-desk-token')
        ->and($payload['before'])->toBe(['base_portal' => 'viewer'])
        ->and($payload['after'])->toBe(['base_portal' => 'osa']);

    $gsoRole = builtInRole('gso');

    Livewire::test(RolesIndex::class)
        ->call('openPortalModal', $gsoRole->role_id)
        ->set('changingPortal', 'viewer')
        ->call('confirmPortalChange')
        ->assertHasErrors('changingPortal');

    expect($gsoRole->fresh()->portal())->toBe('gso');
});

it('asks for an organization when creating a user on a custom student-org based role', function (): void {
    portalRole('Org Officer', 'student-org', ['calendar.view']);
    $this->actingAs(userWithRole(builtInRole('superadmin')));

    Livewire::withoutLazyLoading()
        ->test(UsersIndex::class)
        ->set('createForm.name', 'Org Officer User')
        ->set('createForm.email', 'officer@plv.edu.ph')
        ->set('createForm.password', 'Secret#Pass123')
        ->set('createForm.password_confirmation', 'Secret#Pass123')
        ->set('createForm.role', 'Org Officer')
        ->set('createForm.phone', '09123456789')
        ->assertSee('Organization Name')
        ->call('createUser')
        ->assertHasErrors(['createForm.org_name' => 'required', 'createForm.position' => 'required']);
});

it('marks permissions that have no effect in a role portal on the permissions screen', function (): void {
    $this->actingAs(userWithRole(builtInRole('superadmin')));
    $role = portalRole('SecurityGuard', 'viewer', ['calendar.view']);

    $this->get(route('superadmin.roles.permissions', $role->role_id))
        ->assertOk()
        ->assertSee('View event calendar')
        ->assertSee('Forward tickets to GSO')
        ->assertSee('Not used in this portal');
});
