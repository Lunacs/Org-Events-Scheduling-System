<?php

use App\Jobs\ProcessTicketAttachments;
use App\Livewire\Osa\Reports;
use App\Models\Attachment;
use App\Models\Ticket;
use App\Models\User;
use App\Notifications\TicketStatusUpdatedNotification;
use App\Providers\AppServiceProvider;
use App\Services\Cache\DashboardCacheService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function (): void {
    (new AppServiceProvider($this->app))->boot();

    $this->osa = User::query()->where('email', 'osa@plv.edu.ph')->sole();
    $this->student = User::query()->where('email', 'student@plv.edu.ph')->sole();
});

it('links a status notification to the ticket in each recipient\'s own portal', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->student->user_id]);
    $notification = new TicketStatusUpdatedNotification($ticket, 'gso_review', 'pending_osa_approval', 'GSO approved');

    expect($notification->toArray($this->osa)['action_url'])->toBe(route('osa.ticket-review.show', ['ticketNumber' => $ticket->ticket_number]))
        ->and($notification->toArray($this->student)['action_url'])->toBe(route('student-org.ticket-details', ['ticketNumber' => $ticket->ticket_number]));
});

it('clears the dashboards when a ticket changes', function (): void {
    $calls = 0;
    DashboardCacheService::getDashboardWidget('gso', 'stats', function () use (&$calls) {
        return ++$calls;
    });

    Ticket::factory()->create(['user_id' => $this->student->user_id]);

    $value = DashboardCacheService::getDashboardWidget('gso', 'stats', function () use (&$calls) {
        return ++$calls;
    });

    expect($value)->toBe(2);
});

it('rebuilds the OSA report when the report type changes', function (): void {
    Ticket::factory()->create(['user_id' => $this->student->user_id, 'status' => 'for_revision']);

    $component = Livewire::actingAs($this->osa)->test(Reports::class)
        ->set('dateFrom', now()->subMonth()->format('Y-m-d'))
        ->set('dateTo', now()->format('Y-m-d'));
    $component->set('reportType', 'monthly_summary');
    expect($component->instance()->reportData)->toBeArray();

    $component->set('reportType', 'for_revision_events');
    expect($component->instance()->reportData)->toBeInstanceOf(Collection::class);
});

it('records only copied attachments and does not duplicate them on retry', function (): void {
    Storage::fake(config('filesystems.default'));
    $ticket = Ticket::factory()->create(['user_id' => $this->student->user_id]);
    Storage::put('draft-attachments/1/123_abc_plan.pdf', 'pdf');

    $files = [
        ['file_name' => 'plan.pdf', 'file_path' => 'draft-attachments/1/123_abc_plan.pdf', 'file_type' => 'application/pdf', 'file_size' => 3],
        ['file_name' => 'missing.pdf', 'file_path' => 'draft-attachments/1/missing.pdf', 'file_type' => 'application/pdf', 'file_size' => 3],
    ];

    (new ProcessTicketAttachments($ticket->ticket_id, $files))->handle();
    (new ProcessTicketAttachments($ticket->ticket_id, $files))->handle();

    expect(Attachment::query()->where('ticket_id', $ticket->ticket_id)->pluck('file_name')->all())->toBe(['plan.pdf']);
    Storage::assertExists("tickets/{$ticket->ticket_id}/attachments/123_abc_plan.pdf");
});
