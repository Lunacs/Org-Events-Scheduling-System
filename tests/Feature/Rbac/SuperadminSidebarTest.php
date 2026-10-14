<?php

// Task 13.2: Superadmin sidebar navigation
// **Validates: Requirements 9.2, 9.5**
it('lists Roles & Permissions in the Superadmin DaisyUI sidebar menu', function (): void {
    $layout = file_get_contents(resource_path('views/components/layouts/superadmin.blade.php'));
    $shell = file_get_contents(resource_path('views/components/layouts/portal.blade.php'));

    // The superadmin layout owns its menu items and hands them to the shared shell.
    expect($layout)
        ->toMatch('/\[\s*\'title\' => \'Roles & Permissions\',\s*\'icon\' => \'fa-solid fa-key\',\s*\'link\' => route\(\'superadmin\.roles\'\),\s*\'pattern\' => \'superadmin\/roles\*\',\s*\],/s')
        ->toContain(':items="$menuItems"');

    // The shell renders them as a DaisyUI menu with a marked active item.
    expect($shell)
        ->toContain('<ul class="menu w-full gap-1 px-2 py-3">')
        ->toContain('aria-current="page"')
        ->toContain('bg-primary text-primary-content');
});
