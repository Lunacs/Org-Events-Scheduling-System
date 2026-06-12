<div>
    <div class="p-6 space-y-6">
        {{-- Header --}}
        <section
            class="relative overflow-hidden rounded-2xl border border-base-300 bg-linear-to-br from-base-100 via-base-100 to-primary/10 shadow-sm">
            <div class="absolute -top-20 -right-20 h-48 w-48 rounded-full bg-primary/15 blur-2xl"></div>
            <div class="relative p-6 sm:p-8">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 class="text-3xl font-heading font-bold text-base-content">Admin Tools</h1>
                        <p class="text-sm text-base-content/70 mt-1">System maintenance and management utilities</p>
                    </div>
                    <div class="flex items-center gap-2 relative z-10 w-full sm:w-auto">
                        <x-mary-button icon="o-arrow-path" class="btn-outline w-full sm:w-auto" wire:click="loadSystemStatus; $refresh">
                            Refresh
                        </x-mary-button>
                    </div>
                </div>
            </div>
        </section>

        {{-- System Status Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-mary-stat title="Cache Size" :value="$systemStatus['cache_size'] ?? 'N/A'" icon="o-server-stack"
                class="bg-info text-info-content shadow-lg" />

            <x-mary-stat title="Database Size" :value="$systemStatus['db_size'] ?? 'N/A'" icon="o-circle-stack"
                class="bg-primary text-primary-content shadow-lg" />

            <x-mary-stat title="Storage Size" :value="$systemStatus['storage_size'] ?? 'N/A'" icon="o-folder"
                class="bg-accent text-accent-content shadow-lg" />

            <x-mary-stat title="Transaction Logs" :value="$systemStatus['logs_count'] ?? 0" icon="o-document-text"
                class="bg-warning text-warning-content shadow-lg" />

            <x-mary-stat title="Queue Jobs" :value="$systemStatus['queue_jobs'] ?? 0" icon="o-queue-list"
                class="bg-success text-success-content shadow-lg" />

            <x-mary-stat title="Failed Jobs" :value="$systemStatus['failed_jobs'] ?? 0" icon="o-exclamation-triangle"
                class="bg-error text-error-content shadow-lg" />
        </div>

        {{-- Cache Management --}}
        <x-mary-card title="Cache Management" shadow>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="card bg-base-200">
                    <div class="card-body">
                        <h3 class="card-title text-base">Clear All Cache</h3>
                        <p class="text-sm text-base-content/70">Clear application, config, route, and view cache</p>
                        <div class="card-actions justify-end mt-4">
                            <x-mary-button icon="o-trash" class="btn-outline" wire:click="clearCache"
                                spinner="clearCache" wire:confirm="Are you sure you want to clear all cache?">
                                Clear Cache
                            </x-mary-button>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-200">
                    <div class="card-body">
                        <h3 class="card-title text-base">Optimize Cache</h3>
                        <p class="text-sm text-base-content/70">Cache config, routes, and views for better performance
                        </p>
                        <div class="card-actions justify-end mt-4">
                            <x-mary-button icon="o-rocket-launch" class="btn-primary" wire:click="optimizeCache"
                                spinner="optimizeCache">
                                Optimize
                            </x-mary-button>
                        </div>
                    </div>
                </div>
            </div>
        </x-mary-card>

        {{-- Database Management --}}
        <x-mary-card title="Database Management" shadow>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="card bg-base-200">
                    <div class="card-body">
                        <h3 class="card-title text-base">Optimize Database</h3>
                        <p class="text-sm text-base-content/70">Run database optimization commands</p>
                        <div class="card-actions justify-end mt-4">
                            <x-mary-button icon="o-wrench-screwdriver" class="btn-primary" wire:click="optimizeDatabase"
                                spinner="optimizeDatabase">
                                Optimize Database
                            </x-mary-button>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-200">
                    <div class="card-body">
                        <h3 class="card-title text-base">Generate Backup</h3>
                        <p class="text-sm text-base-content/70">Create a database backup</p>
                        <div class="card-actions justify-end mt-4">
                            <x-mary-button icon="o-arrow-down-tray" class="btn-success" wire:click="generateBackup"
                                spinner="generateBackup">
                                Generate Backup
                            </x-mary-button>
                        </div>
                    </div>
                </div>
            </div>
        </x-mary-card>

        {{-- Logs Management --}}
        <x-mary-card title="Logs Management" shadow>
            <div class="card bg-base-200">
                <div class="card-body">
                    <h3 class="card-title text-base">Cleanup Transaction Logs</h3>
                    <p class="text-sm text-base-content/70">Keep only the last 100 transaction logs</p>
                    <div class="alert alert-warning mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="stroke-current shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span>Current logs: {{ $systemStatus['logs_count'] ?? 0 }}</span>
                    </div>
                    <div class="card-actions justify-end mt-4">
                        <x-mary-button icon="o-trash" class="btn-warning" wire:click="clearLogs" spinner="clearLogs"
                            wire:confirm="Are you sure? This will delete older logs.">
                            Cleanup Logs
                        </x-mary-button>
                    </div>
                </div>
            </div>
        </x-mary-card>

        {{-- Queue Management --}}
        <x-mary-card title="Queue Management" shadow>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="card bg-base-200">
                    <div class="card-body">
                        <h3 class="card-title text-base">Retry Failed Jobs</h3>
                        <p class="text-sm text-base-content/70">Retry all failed queue jobs</p>
                        <div class="alert alert-info mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="stroke-current shrink-0 w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Failed jobs: {{ $systemStatus['failed_jobs'] ?? 0 }}</span>
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <x-mary-button icon="o-arrow-path" class="btn-primary" wire:click="retryFailedJobs"
                                spinner="retryFailedJobs">
                                Retry All
                            </x-mary-button>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-200">
                    <div class="card-body">
                        <h3 class="card-title text-base">Clear Failed Jobs</h3>
                        <p class="text-sm text-base-content/70">Remove all failed jobs from the database</p>
                        <div class="alert alert-error mt-4">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="stroke-current shrink-0 w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>This action cannot be undone!</span>
                        </div>
                        <div class="card-actions justify-end mt-4">
                            <x-mary-button icon="o-trash" class="btn-error" wire:click="clearFailedJobs"
                                spinner="clearFailedJobs"
                                wire:confirm="Are you sure? This will permanently delete all failed jobs.">
                                Clear Failed Jobs
                            </x-mary-button>
                        </div>
                    </div>
                </div>
            </div>
        </x-mary-card>

        {{-- Maintenance Mode --}}
        <x-mary-card title="Maintenance Mode" shadow>
            <div class="card bg-base-200">
                <div class="card-body">
                    <h3 class="card-title text-base">System Maintenance Mode</h3>
                    <p class="text-sm text-base-content/70">Put the application in maintenance mode</p>

                    <div class="alert alert-info mt-4">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="stroke-current shrink-0 w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <div class="flex flex-col">
                            <span class="font-semibold">SuperAdmin Bypass Active</span>
                            <span class="text-sm">You can access admin panel during maintenance using:</span>
                            <code class="mt-2 bg-base-300 px-2 py-1 rounded text-xs">
                                {{ url('/superadmin?secret=superadmin-bypass-2025') }}
                            </code>
                        </div>
                    </div>

                    @if ($maintenanceMode)
                        <div class="alert mt-4 alert-error">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="stroke-current shrink-0 w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Status: <strong>ENABLED</strong></span>
                        </div>
                    @else
                        <div class="alert mt-4 alert-success">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                class="stroke-current shrink-0 w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span>Status: <strong>DISABLED</strong></span>
                        </div>
                    @endif

                    <div class="card-actions justify-end mt-4">
                        <x-mary-button :icon="$maintenanceMode ? 'o-play' : 'o-pause'" :class="$maintenanceMode ? 'btn-success' : 'btn-warning'" wire:click="toggleMaintenanceMode"
                            spinner="toggleMaintenanceMode"
                            wire:confirm="Are you sure you want to {{ $maintenanceMode ? 'disable' : 'enable' }} maintenance mode?">
                            {{ $maintenanceMode ? 'Disable' : 'Enable' }} Maintenance Mode
                        </x-mary-button>
                    </div>
                </div>
            </div>
        </x-mary-card>
    </div>
</div>
