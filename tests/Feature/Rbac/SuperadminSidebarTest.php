<?php

// Task 13.2: Superadmin sidebar navigation
// **Validates: Requirements 9.2, 9.5**
it('lists Roles & Permissions in the Superadmin DaisyUI sidebar menu', function (): void {
    $layout = file_get_contents(resource_path('views/components/layouts/superadmin.blade.php'));

    expect($layout)
        ->toContain('<ul class="menu w-full grow font-heading mt-2 gap-2">')
        ->toMatch('/\[\s*\'title\' => \'Roles & Permissions\',\s*\'icon\' => \'fa-solid fa-key\',\s*\'link\' => route\(\'superadmin\.roles\'\),\s*\'pattern\' => \'superadmin\/roles\*\',\s*\],/s')
        ->toContain('bg-accent text-accent-content');
});
