<?php

namespace Tests\Unit\Superadmin;

use App\Livewire\Superadmin\Tickets\Index;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class TicketBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_bulk_reject_updates_tickets_to_rejected_status(): void
    {
        // Create a SuperAdmin user to execute the bulk action
        $superadmin = User::factory()->create([
            'role_id' => User::getRoleId('superadmin'),
        ]);

        // Log in as superadmin
        $this->actingAs($superadmin);

        // Create a student org user
        $student = User::factory()->create([
            'role_id' => User::getRoleId('student-org'),
        ]);

        // Create some test tickets
        $ticket1 = Ticket::factory()->create([
            'user_id' => $student->user_id,
            'status' => 'received',
        ]);

        $ticket2 = Ticket::factory()->create([
            'user_id' => $student->user_id,
            'status' => 'received',
        ]);

        // Run Livewire test
        $lwTest = Livewire::actingAs($superadmin)
            ->test(Index::class)
            ->set('selectedTickets', [$ticket1->ticket_id, $ticket2->ticket_id])
            ->set('bulkAction', 'reject')
            ->call('executeBulkAction');

        // Assert that ticket statuses are updated to 'for_revision'
        $this->assertEquals('for_revision', $ticket1->fresh()->status);
        $this->assertEquals('for_revision', $ticket2->fresh()->status);
    }
}
