<?php

namespace App\Livewire\Superadmin\AdminTools;

use App\Models\Transaction_Logs;
use App\Services\TransactionLogService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Mary\Traits\Toast;

class Index extends Component
{
    use Toast;

    #[Title('Admin Tools - SuperAdmin')]
    #[Layout('components.layouts.superadmin')]

    public $systemStatus = [];
    public $maintenanceMode = false;

    public function mount()
    {
        $this->loadSystemStatus();
        $this->maintenanceMode = app()->isDownForMaintenance();
    }

    public function loadSystemStatus()
    {
        $this->systemStatus = [
            'cache_size' => $this->getCacheSize(),
            'db_size' => $this->getDatabaseSize(),
            'storage_size' => $this->getStorageSize(),
            'logs_count' => Transaction_Logs::count(),
            'queue_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
        ];
    }

    protected function getCacheSize()
    {
        try {
            return Cache::get('cache_size', '0 KB');
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    protected function getDatabaseSize()
    {
        try {
            $dbName = config('database.connections.mysql.database');
            $size = DB::select("
                SELECT ROUND(SUM(data_length + index_length) / 1024 / 1024, 2) AS size_mb
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$dbName]);

            return ($size[0]->size_mb ?? 0) . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    protected function getStorageSize()
    {
        try {
            $size = 0;
            $files = Storage::allFiles('public');
            foreach ($files as $file) {
                $size += Storage::size($file);
            }
            return round($size / 1024 / 1024, 2) . ' MB';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }

    public function clearCache()
    {
        try {
            Artisan::call('cache:clear');
            Artisan::call('config:clear');
            Artisan::call('route:clear');
            Artisan::call('view:clear');

            TransactionLogService::log(
                'CACHE_CLEAR',
                'SuperAdmin cleared application cache',
                Auth::user()->user_id
            );

            $this->success('Cache cleared successfully!', position: 'toast-top');
            $this->loadSystemStatus();
        } catch (\Exception $e) {
            $this->error('Failed to clear cache: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function optimizeCache()
    {
        try {
            Artisan::call('config:cache');
            Artisan::call('route:cache');
            Artisan::call('view:cache');
            Artisan::call('optimize:clear');

            TransactionLogService::log(
                'CACHE_OPTIMIZE',
                'SuperAdmin optimized application cache',
                Auth::user()->user_id
            );

            $this->success('Cache optimized successfully!', position: 'toast-top');
            $this->loadSystemStatus();
        } catch (\Exception $e) {
            $this->error('Failed to optimize cache: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function clearLogs()
    {
        try {
            $count = Transaction_Logs::count();

            // Keep only last 100 logs
            $logsToDelete = Transaction_Logs::orderBy('created_at', 'asc')
                ->limit($count - 100)
                ->pluck('log_id');

            Transaction_Logs::whereIn('log_id', $logsToDelete)->delete();

            TransactionLogService::log(
                'LOGS_CLEANUP',
                "SuperAdmin cleaned up transaction logs (deleted " . $logsToDelete->count() . " entries)",
                Auth::user()->user_id
            );

            $this->success('Logs cleaned up successfully!', position: 'toast-top');
            $this->loadSystemStatus();
        } catch (\Exception $e) {
            $this->error('Failed to cleanup logs: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function retryFailedJobs()
    {
        try {
            Artisan::call('queue:retry all');

            TransactionLogService::log(
                'QUEUE_RETRY',
                'SuperAdmin retried all failed queue jobs',
                Auth::user()->user_id
            );

            $this->success('Failed jobs retried successfully!', position: 'toast-top');
            $this->loadSystemStatus();
        } catch (\Exception $e) {
            $this->error('Failed to retry jobs: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function clearFailedJobs()
    {
        try {
            DB::table('failed_jobs')->truncate();

            TransactionLogService::log(
                'QUEUE_CLEAR',
                'SuperAdmin cleared all failed queue jobs',
                Auth::user()->user_id
            );

            $this->success('Failed jobs cleared successfully!', position: 'toast-top');
            $this->loadSystemStatus();
        } catch (\Exception $e) {
            $this->error('Failed to clear jobs: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function optimizeDatabase()
    {
        try {
            Artisan::call('optimize');

            TransactionLogService::log(
                'DB_OPTIMIZE',
                'SuperAdmin optimized database',
                Auth::user()->user_id
            );

            $this->success('Database optimized successfully!', position: 'toast-top');
            $this->loadSystemStatus();
        } catch (\Exception $e) {
            $this->error('Failed to optimize database: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function toggleMaintenanceMode()
    {
        try {
            if ($this->maintenanceMode) {
                Artisan::call('up');

                // Clear all caches to ensure maintenance mode is fully disabled
                Artisan::call('cache:clear');
                Artisan::call('config:clear');
                Artisan::call('route:clear');

                $this->maintenanceMode = false;
                $message = 'SuperAdmin disabled maintenance mode';

                $this->success($message . '!', position: 'toast-top');

                // Redirect to refresh the entire page and clear any cached state
                return redirect()->route('superadmin.admin-tools');
            } else {
                // Enable maintenance mode with a secret bypass token
                // SuperAdmin can access by visiting: /superadmin?secret=superadmin-bypass-2025
                Artisan::call('down', [
                    '--secret' => 'superadmin-bypass-2025',
                    '--render' => 'errors.503'
                ]);
                $this->maintenanceMode = true;
                $message = 'SuperAdmin enabled maintenance mode';

                $this->success($message . '!', position: 'toast-top');

                // Important: Inform SuperAdmin about bypass URL
                $this->info('To access admin panel during maintenance, use: /superadmin?secret=superadmin-bypass-2025', position: 'toast-top', timeout: 10000);
            }

            TransactionLogService::log(
                'MAINTENANCE',
                $message,
                Auth::user()->user_id
            );
        } catch (\Exception $e) {
            $this->error('Failed to toggle maintenance mode: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function generateBackup()
    {
        try {
            // This is a placeholder - actual backup implementation would depend on your setup
            $this->warning('Backup feature coming soon! Please use your hosting panel for now.', position: 'toast-top');

            TransactionLogService::log(
                'BACKUP_REQUEST',
                'SuperAdmin requested database backup',
                Auth::user()->user_id
            );
        } catch (\Exception $e) {
            $this->error('Failed to generate backup: ' . $e->getMessage(), position: 'toast-top');
        }
    }

    public function render()
    {
        return view('livewire.superadmin.admin-tools.index');
    }
}
