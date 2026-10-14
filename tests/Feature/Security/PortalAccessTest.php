<?php

use App\Livewire\StudentOrg\Dashboard as StudentDashboard;
use App\Livewire\StudentOrg\History;
use App\Livewire\StudentOrg\SubmitTicket;
use App\Models\Ticket;
use App\Models\TicketComment;
use App\Models\TicketDraft;
use App\Models\TicketDraftAttachment;
use App\Models\User;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Storage;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Livewire;

beforeEach(function (): void {
    (new AppServiceProvider($this->app))->boot();

    $this->studentA = User::query()->where('email', 'student@plv.edu.ph')->sole();
    $this->studentB = User::query()->where('email', 'john.martinez@plv.edu.ph')->sole();
});

it('caches the student dashboard per organization, not once for everyone', function (): void {
    $ticketA = Ticket::factory()->create(['user_id' => $this->studentA->user_id, 'title' => 'Org A secret event']);

    $seenByA = Livewire::actingAs($this->studentA)->test(StudentDashboard::class)->instance()->tickets->pluck('ticket_id');
    $seenByB = Livewire::actingAs($this->studentB)->test(StudentDashboard::class)->instance()->tickets->pluck('ticket_id');

    expect($seenByA)->toContain($ticketA->ticket_id)
        ->and($seenByB)->not->toContain($ticketA->ticket_id);
});

it('does not open another organization\'s ticket from History', function (): void {
    $ticketA = Ticket::factory()->create(['user_id' => $this->studentA->user_id]);

    Livewire::actingAs($this->studentB)
        ->test(History::class)
        ->call('loadTicketDetails', $ticketA->ticket_id)
        ->assertNotFound();
});

it('searches History by venue without crashing', function (): void {
    Livewire::actingAs($this->studentA)
        ->test(History::class)
        ->set('search', 'Auditorium')
        ->set('yearFilter', (string) now()->year)
        ->assertOk();
});

it('does not open a ticket GSO was never asked to review', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->studentA->user_id, 'status' => 'received']);
    $gso = User::query()->where('email', 'gso@plv.edu.ph')->sole();

    $this->actingAs($gso)->get(route('gso.ticket-details', $ticket->ticket_number))->assertNotFound();
});

it('does not delete another user\'s draft upload', function (): void {
    Storage::fake(config('filesystems.default'));

    $draft = TicketDraft::create(['user_id' => $this->studentB->user_id, 'current_step' => 5, 'data' => []]);
    Storage::put('draft-attachments/victim.pdf', 'pdf');
    $victimFile = TicketDraftAttachment::create([
        'ticket_draft_id' => $draft->id,
        'file_name' => 'victim.pdf',
        'file_path' => 'draft-attachments/victim.pdf',
        'file_type' => 'application/pdf',
        'file_size' => 3,
    ]);

    Livewire::actingAs($this->studentA)
        ->test(SubmitTicket::class)
        ->set('attachments', [['id' => $victimFile->id]])
        ->call('removeAttachment', 0);

    expect(TicketDraftAttachment::query()->whereKey($victimFile->id)->exists())->toBeTrue();
    Storage::assertExists('draft-attachments/victim.pdf');
});

it('locks the number of steps the submit form validates', function (): void {
    Livewire::actingAs($this->studentA)
        ->test(SubmitTicket::class)
        ->set('totalSteps', 1);
})->throws(CannotUpdateLockedPropertyException::class);

it('keeps comments from deleted users readable', function (): void {
    $ticket = Ticket::factory()->create(['user_id' => $this->studentA->user_id]);
    $comment = TicketComment::create(['ticket_id' => $ticket->ticket_id, 'user_id' => $this->studentB->user_id, 'content' => 'Hello there']);

    $this->studentB->delete();

    expect($comment->fresh()->user?->name)->toBe('John Martinez');
});
