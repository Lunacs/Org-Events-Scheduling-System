<?php

use App\Livewire\Superadmin\Tickets\Index;
use Illuminate\Support\Facades\Auth;

afterEach(function (): void {
    \Mockery::close();
});

test('bulk reject updates tickets to rejected status', function (): void {
    Auth::shouldReceive('user')
        ->once()
        ->andReturn((object) ['user_id' => 1]);

    $ticketAlias = \Mockery::mock('alias:App\\Models\\Ticket');
    $ticketAlias->shouldReceive('whereIn')
        ->once()
        ->with('ticket_id', [10, 11])
        ->andReturnSelf();
    $ticketAlias->shouldReceive('update')
        ->once()
        ->with(['status' => 'rejected'])
        ->andReturn(2);

    $transactionLogAlias = \Mockery::mock('alias:App\\Services\\TransactionLogService');
    $transactionLogAlias->shouldReceive('log')
        ->once();

    $component = new class extends Index
    {
        public function success(string $title, ?string $description = null, ?string $position = null, string $icon = 'o-check-circle', string $css = 'alert-success', int $timeout = 3000, ?string $redirectTo = null, bool $noProgress = false, ?string $progressClass = null): void {}

        public function error(string $title, ?string $description = null, ?string $position = null, string $icon = 'o-x-circle', string $css = 'alert-error', int $timeout = 3000, ?string $redirectTo = null, bool $noProgress = false, ?string $progressClass = null): void {}

        public function warning(string $title, ?string $description = null, ?string $position = null, string $icon = 'o-exclamation-triangle', string $css = 'alert-warning', int $timeout = 3000, ?string $redirectTo = null, bool $noProgress = false, ?string $progressClass = null): void {}
    };
    $component->selectedTickets = [10, 11];
    $component->bulkAction = 'reject';

    $component->executeBulkAction();

    expect($component->selectedTickets)->toBe([]);
    expect($component->bulkAction)->toBe('');
});
