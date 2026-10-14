<?php

use App\Models\Attachment;
use App\Models\Event;
use App\Models\Office;
use App\Models\Office_Approval;
use App\Models\OSA_Approval;
use App\Models\Roles;
use App\Models\Ticket;
use App\Models\Transaction_Logs;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Database\Seeders\ContentSectionSeeder;
use Database\Seeders\CustomRoleSeeder;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\TicketSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

/**
 * Run the seeders that DatabaseSeeder skips in the testing environment.
 */
function seedDemoData(): void
{
    test()->seed([ContentSectionSeeder::class, FaqSeeder::class, CustomRoleSeeder::class, TicketSeeder::class]);
}

beforeEach(function (): void {
    Storage::fake(config('filesystems.default'));
    Mail::fake();
    Queue::fake();
});

it('keeps workflow demo data out of the test seed', function (): void {
    expect(Ticket::withTrashed()->count())->toBe(0)
        ->and(Transaction_Logs::query()->count())->toBe(0)
        ->and(Roles::query()->where('role_name', CustomRoleSeeder::ROLE_NAME)->exists())->toBeFalse();
});

it('seeds tickets in every workflow status with matching approval records', function (): void {
    seedDemoData();

    $tickets = Ticket::query()->with(['osaApprovals', 'officeApprovals'])->get();
    $gsoOfficeId = Office::query()->where('office_code', 'GSO')->value('office_id');

    expect($tickets->pluck('status')->unique()->values()->all())->toEqualCanonicalizing([
        'received', 'amended', 'gso_review', 'pending_osa_approval', 'for_revision', 'approved', 'completed',
    ]);

    foreach ($tickets as $ticket) {
        expect($ticket->ticket_number)->toStartWith('TKT-'.$ticket->user->studentOrganization->org_code.'-');

        $expectedOsaDecision = match ($ticket->status) {
            'received' => null,
            'amended' => 'pending',
            'gso_review', 'pending_osa_approval' => 'forwarded',
            'for_revision' => 'for_revision',
            'approved', 'completed' => 'approved',
        };

        expect($ticket->osaApprovals->pluck('decision')->all())
            ->toBe($expectedOsaDecision === null ? [] : [$expectedOsaDecision]);

        $officeDecisions = $ticket->officeApprovals->pluck('decision')->all();

        match ($ticket->status) {
            'gso_review' => expect($officeDecisions)->toBe(['pending']),
            'pending_osa_approval' => expect($officeDecisions)->toHaveCount(1)->each->toBeIn(['approved', 'for_revision']),
            default => null,
        };

        expect($ticket->officeApprovals->pluck('office_id')->unique()->all())
            ->toBe($officeDecisions === [] ? [] : [$gsoOfficeId]);
    }
});

it('creates one approved event schedule for every approved or completed ticket', function (): void {
    seedDemoData();

    $scheduledTickets = Ticket::query()->whereIn('status', ['approved', 'completed'])->get();

    expect($scheduledTickets)->not->toBeEmpty()
        ->and(Event::query()->count())->toBe($scheduledTickets->count());

    foreach ($scheduledTickets as $ticket) {
        $schedules = $ticket->events()->with('eventSchedules')->get()->flatMap->eventSchedules;

        expect($schedules)->toHaveCount(1)
            ->and($schedules->first()->status)->toBe('approved')
            ->and($schedules->first()->venue)->toBe($ticket->venue_display_name)
            ->and($schedules->first()->start_date->format('Y-m-d'))->toBe($ticket->date_from);
    }
});

it('records the history, logs, attachments and notifications the workflow writes', function (): void {
    seedDemoData();

    $tickets = Ticket::query()->withCount(['approvalHistory', 'attachments'])->get();

    foreach ($tickets as $ticket) {
        expect($ticket->attachments_count)->toBeGreaterThan(0)
            ->and($ticket->approval_history_count > 0)->toBe($ticket->status !== 'received');
    }

    Attachment::query()->each(fn (Attachment $attachment) => Storage::assertExists($attachment->file_path));

    expect(Transaction_Logs::query()->where('action', 'TICKET_CREATED')->count())->toBe($tickets->count())
        ->and(Transaction_Logs::query()->where('action', 'TICKET_COMPLETED')->count())->toBe($tickets->where('status', 'completed')->count())
        ->and(DB::table('ticket_comments')->count())->toBeGreaterThan(0)
        ->and(DB::table('notifications')->count())->toBeGreaterThan(0)
        ->and(DB::table('notifications')->whereNotIn('notifiable_id', User::withTrashed()->select('user_id'))->count())->toBe(0);

    Mail::assertNothingOutgoing();
    Queue::assertNothingPushed();
});

it('seeds a calendar-only security guard on the viewer portal', function (): void {
    seedDemoData();
    (new AppServiceProvider($this->app))->boot();

    $role = Roles::query()->where('role_name', CustomRoleSeeder::ROLE_NAME)->sole();
    $guard = User::query()->where('email', CustomRoleSeeder::USER_EMAIL)->sole();

    expect($role->portal())->toBe('viewer')
        ->and($role->isProtected())->toBeFalse()
        ->and($role->permissions()->pluck('name')->all())->toEqualCanonicalizing(CustomRoleSeeder::PERMISSIONS)
        ->and($guard->role_id)->toBe($role->role_id)
        ->and(Transaction_Logs::query()->whereIn('action', ['RBAC_ROLE_CREATED', 'RBAC_PERMISSIONS_MAPPED'])->count())->toBe(2);

    $this->actingAs($guard)->get(route('portal.calendar'))->assertOk();
    $this->actingAs($guard)->get(route('admin.dashboard'))->assertRedirect(route('portal.calendar'));
});

it('verifies every seeded account and can run every seeder twice', function (): void {
    seedDemoData();

    $counts = fn (): array => [
        Ticket::withTrashed()->count(),
        User::withTrashed()->count(),
        Roles::query()->count(),
        DB::table('role_permission')->count(),
        Transaction_Logs::query()->count(),
        OSA_Approval::query()->count(),
        Office_Approval::query()->count(),
    ];

    $before = $counts();

    $this->seed(DatabaseSeeder::class);
    seedDemoData();

    expect($counts())->toBe($before)
        ->and(User::withTrashed()->whereNull('email_verified_at')->count())->toBe(0);
});
