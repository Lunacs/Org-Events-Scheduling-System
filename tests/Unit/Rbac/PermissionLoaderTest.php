<?php

use App\Models\Permission;
use App\Providers\AppServiceProvider;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

uses(TestCase::class);

// Validates: Requirements 5.6
it('continues booting and logs a warning when loading permissions fails', function (): void {
    $connectionResolver = Permission::getConnectionResolver();

    Cache::forget(Permission::NAMES_CACHE_KEY);
    Log::spy();
    Permission::setConnectionResolver(new class implements ConnectionResolverInterface
    {
        public function connection($name = null)
        {
            throw new Exception('Permission database connection is unavailable.');
        }

        public function getDefaultConnection()
        {
            return 'mysql';
        }

        public function setDefaultConnection($name): void {}
    });

    try {
        expect(fn () => (new AppServiceProvider($this->app))->boot())
            ->not->toThrow(Exception::class);
    } finally {
        Permission::setConnectionResolver($connectionResolver);
    }

    Log::shouldHaveReceived('warning')
        ->once()
        ->withArgs(fn (string $message, array $context): bool => $message === 'RBAC: Failed to load permissions at boot — Gate definitions skipped.'
            && array_key_exists('error', $context));
});
