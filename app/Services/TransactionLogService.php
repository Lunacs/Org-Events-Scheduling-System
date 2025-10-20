<?php

namespace App\Services;

use App\Models\Transaction_Logs;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class TransactionLogService
{
    /**
     * Log a transaction with automatic user detection
     */
    public static function log(string $action, string $details, ?int $userId = null): void
    {
        $userId = $userId ?? Auth::id() ?? 1; // Default to user ID 1 if no authenticated user
        
        Transaction_Logs::create([
            'user_id' => $userId,
            'action' => strtoupper($action),
            'details' => $details,
        ]);

        // Auto-cleanup old logs periodically (every 100 new logs)
        static::autoCleanup();
    }

    /**
     * Automatically clean up old logs to prevent database bloat
     */
    private static function autoCleanup(): void
    {
        $totalLogs = Transaction_Logs::count();
        
        // Only cleanup if we have more than 1000 logs
        if ($totalLogs > 1000) {
            // Keep only the last 500 logs
            $logsToDelete = Transaction_Logs::orderBy('created_at', 'asc')
                ->limit($totalLogs - 500)
                ->pluck('log_id');
                
            Transaction_Logs::whereIn('log_id', $logsToDelete)->delete();
        }
    }

    /**
     * Log user management operations
     */
    public static function logUserOperation(string $operation, User $user, array $changes = []): void
    {
        $details = match($operation) {
            'created' => "Created user account: {$user->name} ({$user->email})",
            'updated' => "Updated user account: {$user->name} ({$user->email})" . 
                        (empty($changes) ? '' : ' - Changes: ' . implode(', ', $changes)),
            'deleted' => "Deleted user account: {$user->name} ({$user->email})",
            'password_reset' => "Reset password for user: {$user->name} ({$user->email})",
            'role_changed' => "Changed role for user: {$user->name} ({$user->email})",
            default => "User operation: {$operation} on {$user->name} ({$user->email})"
        };

        self::log('USER_' . strtoupper($operation), $details);
    }

    /**
     * Log event type operations
     */
    public static function logEventTypeOperation(string $operation, $eventType, array $changes = []): void
    {
        $eventTypeName = is_object($eventType) ? $eventType->type_name : $eventType;
        
        $details = match($operation) {
            'created' => "Created event type: {$eventTypeName}",
            'updated' => "Updated event type: {$eventTypeName}" . 
                        (empty($changes) ? '' : ' - Changes: ' . implode(', ', $changes)),
            'deleted' => "Deleted event type: {$eventTypeName}",
            default => "Event type operation: {$operation} on {$eventTypeName}"
        };

        self::log('EVENT_TYPE_' . strtoupper($operation), $details);
    }

    /**
     * Log ticket operations
     */
    public static function logTicketOperation(string $operation, $ticket, array $changes = []): void
    {
        $ticketTitle = is_object($ticket) ? $ticket->title : $ticket;
        $ticketNumber = is_object($ticket) ? $ticket->ticket_number : '';
        
        $details = match($operation) {
            'created' => "Created ticket: {$ticketTitle} ({$ticketNumber})",
            'updated' => "Updated ticket: {$ticketTitle} ({$ticketNumber})" . 
                        (empty($changes) ? '' : ' - Changes: ' . implode(', ', $changes)),
            'approved' => "Approved ticket: {$ticketTitle} ({$ticketNumber})",
            'rejected' => "Rejected ticket: {$ticketTitle} ({$ticketNumber})",
            'deleted' => "Deleted ticket: {$ticketTitle} ({$ticketNumber})",
            default => "Ticket operation: {$operation} on {$ticketTitle} ({$ticketNumber})"
        };

        self::log('TICKET_' . strtoupper($operation), $details);
    }

    /**
     * Log authentication events
     */
    public static function logAuthEvent(string $event, ?User $user = null, string $additionalInfo = ''): void
    {
        $userInfo = $user ? "{$user->name} ({$user->email})" : 'Unknown user';
        
        $details = match($event) {
            'login' => "User logged in: {$userInfo}",
            'logout' => "User logged out: {$userInfo}",
            'login_failed' => "Failed login attempt: {$additionalInfo}",
            'password_changed' => "Password changed for: {$userInfo}",
            'email_verified' => "Email verified for: {$userInfo}",
            default => "Auth event: {$event} for {$userInfo}"
        };

        self::log('AUTH_' . strtoupper($event), $details);
    }

    /**
     * Log system operations
     */
    public static function logSystemOperation(string $operation, string $details = ''): void
    {
        $fullDetails = empty($details) ? "System operation: {$operation}" : $details;
        self::log('SYSTEM_' . strtoupper($operation), $fullDetails);
    }

    /**
     * Log office operations
     */
    public static function logOfficeOperation(string $operation, $office, array $changes = []): void
    {
        $officeName = is_object($office) ? $office->office_name : $office;
        
        $details = match($operation) {
            'created' => "Created office: {$officeName}",
            'updated' => "Updated office: {$officeName}" . 
                        (empty($changes) ? '' : ' - Changes: ' . implode(', ', $changes)),
            'deleted' => "Deleted office: {$officeName}",
            default => "Office operation: {$operation} on {$officeName}"
        };

        self::log('OFFICE_' . strtoupper($operation), $details);
    }

    /**
     * Get recent logs with pagination
     */
    public static function getRecentLogs(int $limit = 10)
    {
        return Transaction_Logs::with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Manually clean up old logs (for admin use)
     */
    public static function manualCleanup(int $keepCount = 500): int
    {
        $totalLogs = Transaction_Logs::count();
        
        if ($totalLogs <= $keepCount) {
            return 0; // No logs to delete
        }
        
        $logsToDelete = Transaction_Logs::orderBy('created_at', 'asc')
            ->limit($totalLogs - $keepCount)
            ->pluck('log_id');
            
        $deletedCount = Transaction_Logs::whereIn('log_id', $logsToDelete)->delete();
        
        return $deletedCount;
    }

    /**
     * Get logs by action type
     */
    public static function getLogsByAction(string $action, int $limit = 50)
    {
        return Transaction_Logs::with('user')
            ->where('action', 'LIKE', "%{$action}%")
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get logs by user
     */
    public static function getLogsByUser(int $userId, int $limit = 50)
    {
        return Transaction_Logs::with('user')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}
