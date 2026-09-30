<?php

use Tests\TestCase;

uses(TestCase::class);

test('it loads the RBAC configuration defaults', function (): void {
    expect(config('rbac.permission_cache_ttl'))
        ->toBe(3600)
        ->and(config('rbac.fallback_role'))
        ->toBe('student-org');
});

test('it reads RBAC configuration from environment variables', function (): void {
    $originalCacheTtl = getenv('RBAC_CACHE_TTL');
    $originalFallbackRole = getenv('RBAC_FALLBACK_ROLE');

    putenv('RBAC_CACHE_TTL=1800');
    putenv('RBAC_FALLBACK_ROLE=osa');

    try {
        $configuration = require config_path('rbac.php');

        expect($configuration['permission_cache_ttl'])
            ->toBe(1800)
            ->and($configuration['fallback_role'])
            ->toBe('osa');
    } finally {
        putenv($originalCacheTtl === false ? 'RBAC_CACHE_TTL' : "RBAC_CACHE_TTL={$originalCacheTtl}");
        putenv($originalFallbackRole === false ? 'RBAC_FALLBACK_ROLE' : "RBAC_FALLBACK_ROLE={$originalFallbackRole}");
    }
});

test('it clamps the permission cache TTL between one minute and twenty-four hours', function (string $configuredTtl, int $expectedTtl): void {
    $originalCacheTtl = getenv('RBAC_CACHE_TTL');

    putenv("RBAC_CACHE_TTL={$configuredTtl}");

    try {
        $configuration = require config_path('rbac.php');

        expect($configuration['permission_cache_ttl'])->toBe($expectedTtl);
    } finally {
        putenv($originalCacheTtl === false ? 'RBAC_CACHE_TTL' : "RBAC_CACHE_TTL={$originalCacheTtl}");
    }
})->with([
    'zero' => ['0', 60],
    'negative' => ['-100', 60],
    'below minimum' => ['59', 60],
    'minimum' => ['60', 60],
    'maximum' => ['86400', 86400],
    'above maximum' => ['999999', 86400],
    'non-numeric' => ['abc', 60],
]);
