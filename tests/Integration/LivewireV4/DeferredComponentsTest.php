<?php

use App\Livewire\Components\EventCalendar;
use App\Livewire\Osa\Dashboard as OsaDashboard;
use App\Livewire\StudentOrg\Dashboard as StudentOrgDashboard;
use App\Livewire\Superadmin\Dashboard as SuperadminDashboard;
use App\Livewire\Superadmin\SystemSettings\Index as SystemSettingsIndex;
use App\Models\Course;
use App\Models\Positions;
use App\Models\Roles;
use App\Models\Student_Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

it('renders superadmin dashboard with deferred components', function () {
    $role = Roles::firstOrCreate(['role_name' => 'superadmin']);
    $user = User::factory()->create(['role_id' => $role->role_id]);

    Livewire::actingAs($user)
        ->test(SuperadminDashboard::class)
        ->assertSuccessful();
});

it('renders OSA dashboard with deferred components', function () {
    $role = Roles::firstOrCreate(['role_name' => 'osa']);
    $user = User::factory()->create(['role_id' => $role->role_id]);

    Livewire::actingAs($user)
        ->test(OsaDashboard::class)
        ->assertSuccessful();
});

it('renders student-org dashboard with deferred components', function () {
    $role = Roles::firstOrCreate(['role_name' => 'student-org']);
    $position = Positions::firstOrCreate(['position_name' => 'President']);
    $course = Course::factory()->create();
    $org = Student_Organization::create([
        'org_code' => 'TEST-ORG',
        'org_name' => 'Test Organization',
        'course_id' => $course->course_id,
        'adviser_name' => 'Test Adviser',
        'status' => 'active',
    ]);
    $user = User::factory()->create([
        'role_id' => $role->role_id,
        'position_id' => $position->position_id,
        'org_id' => $org->org_id,
    ]);

    Livewire::actingAs($user)
        ->test(StudentOrgDashboard::class)
        ->assertSuccessful();
});

it('renders system settings with deferred tab managers', function () {
    $role = Roles::firstOrCreate(['role_name' => 'superadmin']);
    $user = User::factory()->create(['role_id' => $role->role_id]);

    Livewire::actingAs($user)
        ->test(SystemSettingsIndex::class)
        ->assertSuccessful();
});

it('renders event calendar with island directive', function () {
    $role = Roles::firstOrCreate(['role_name' => 'osa']);
    $user = User::factory()->create(['role_id' => $role->role_id]);

    Livewire::actingAs($user)
        ->test(EventCalendar::class)
        ->assertSuccessful();
});
