<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permission Cache TTL
    |--------------------------------------------------------------------------
    |
    | This value controls how long, in seconds, role permission mappings remain
    | cached before they are loaded from the database again. Values are clamped
    | to between 1 minute and 24 hours.
    |
    */

    'permission_cache_ttl' => max(60, min(86400, (int) env('RBAC_CACHE_TTL', 3600))),

    /*
    |--------------------------------------------------------------------------
    | Fallback Role
    |--------------------------------------------------------------------------
    |
    | Users assigned to a deleted non-protected role are reassigned to this
    | role name.
    |
    */

    'fallback_role' => env('RBAC_FALLBACK_ROLE', 'student-org'),
];
