<?php

use App\Models\Roles;
use Database\Seeders\RolesSeeder;

it('keeps the legacy roles seeder idempotent when it runs repeatedly', function (): void {
    $seeder = new RolesSeeder;
    $builtInRoles = ['superadmin', 'osa', 'gso', 'student-org'];

    $exceptions = [];

    foreach (range(1, 2) as $run) {
        try {
            $seeder->run();
        } catch (Throwable $exception) {
            $exceptions[] = $exception;
        }
    }

    expect($exceptions)->toBeEmpty();

    foreach ($builtInRoles as $roleName) {
        expect(Roles::query()->where('role_name', $roleName)->count())->toBeOne();
    }
});
