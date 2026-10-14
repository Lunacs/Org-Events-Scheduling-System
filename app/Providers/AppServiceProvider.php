<?php

namespace App\Providers;

use App\Http\Middleware\CheckUserRole;
use App\Models\Attachment;
use App\Models\Office_Approval;
use App\Models\Permission;
use App\Models\Roles;
use App\Models\Ticket;
use App\Models\User;
use App\Observers\AttachmentObserver;
use App\Observers\OfficeApprovalObserver;
use App\Observers\TicketObserver;
use App\Support\RolePortals;
use App\View\Html\Sanitizer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Symfony\Component\HtmlSanitizer\HtmlSanitizer;
use Symfony\Component\HtmlSanitizer\HtmlSanitizerConfig;
use Symfony\Component\Mailer\Bridge\Brevo\Transport\BrevoTransportFactory;
use Symfony\Component\Mailer\Transport\Dsn;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the HTML Sanitizer for XSS protection
        $this->app->scoped(Sanitizer::class, function () {
            return new Sanitizer(new HtmlSanitizer(
                (new HtmlSanitizerConfig)
                    ->allowSafeElements()
                    // Explicitly allow elements used by Trix editor
                    ->allowElement('div')
                    ->allowElement('p')
                    ->allowElement('br')
                    ->allowElement('ul')
                    ->allowElement('ol')
                    ->allowElement('li')
                    ->allowElement('strong')
                    ->allowElement('em')
                    ->allowElement('del')
                    ->allowElement('a', ['href', 'target', 'rel'])
                    ->allowElement('blockquote')
                    ->allowElement('pre')
                    ->allowElement('h1')
                    ->allowElement('h2')
                    ->allowElement('h3')
                    ->allowElement('code')
                    ->allowAttribute('class', '*')
                    ->allowAttribute('style', '*')
                    ->allowAttribute('id', '*')
            ));
        });

        $this->app->scoped(RolePortals::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Re-check the portal on every Livewire update, not only the initial page load.
        Livewire::addPersistentMiddleware([CheckUserRole::class]);

        Gate::define('manage-rbac', static function (User $user): bool {
            return $user->isSuperAdmin();
        });

        // Superadmin bypasses permission checks only; model policies still decide their own abilities.
        Gate::before(function (User $user, string $ability): ?bool {
            if (! Permission::isPermissionAbility($ability)) {
                return null;
            }

            return $user->role?->role_name === 'superadmin' ? true : null;
        });

        try {
            $permissionNames = Cache::remember(
                Permission::NAMES_CACHE_KEY,
                config('rbac.permission_cache_ttl', 3600),
                static fn (): array => Permission::query()->pluck('name')->all(),
            );
        } catch (\Exception $exception) {
            Log::warning('RBAC: Failed to load permissions at boot — Gate definitions skipped.', [
                'error' => $exception->getMessage(),
            ]);
            $permissionNames = collect();
        }

        foreach ($permissionNames as $permissionName) {
            Gate::define($permissionName, function (?User $user) use ($permissionName): bool {
                if (! $user || $user->role_id === null) {
                    return false;
                }

                $roleId = $user->role_id;
                $assignedPermissions = Cache::remember(
                    "rbac_role_permissions_{$roleId}",
                    config('rbac.permission_cache_ttl', 3600),
                    function () use ($roleId): array {
                        try {
                            $role = Roles::query()->find($roleId);

                            return $role?->permissions()->pluck('name')->all() ?? [];
                        } catch (\Exception $exception) {
                            Log::warning("RBAC: Failed to load permissions for role {$roleId}.", [
                                'error' => $exception->getMessage(),
                            ]);

                            return [];
                        }
                    },
                );

                return in_array($permissionName, $assignedPermissions, true);
            });
        }

        Mail::extend('brevo', function () {
            return (new BrevoTransportFactory)->create(
                new Dsn('brevo+api', 'default', config('services.brevo.key'))
            );
        });

        // Register observers
        Attachment::observe(AttachmentObserver::class);
        Ticket::observe(TicketObserver::class);
        Office_Approval::observe(OfficeApprovalObserver::class);

        // Automatically eager load relationships (Laravel 12.0.8+)
        Model::automaticallyEagerLoadRelationships();

        // Prevent lazy loading in non-production (catch N+1 issues early)
        // Model::preventLazyLoading(! app()->isProduction());

        // Enable HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
            $this->app['request']->server->set('HTTPS', 'on');
        }

        // Optimize queries
        DB::enableQueryLog();
    }
}
