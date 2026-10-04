<?php

use App\Livewire\Osa\Reports;
use App\Models\Ticket;
use App\Support\TicketStatus;

/*
 * The OSA "Status distribution" donut speaks the same status language as the badges:
 * the same labels, and each status's tone (info shades for in-progress states, ochre for
 * needs revision, green for approved, red for rejected) with a distinct color per slice.
 */
function statusDistribution(): array
{
    return \Livewire\invade(new Reports)->getStatusDistribution();
}

it('labels and colors status slices from the shared ticket status vocabulary', function () {
    foreach (['received', 'amended', 'gso_review', 'pending_osa_approval', 'for_revision', 'approved'] as $status) {
        Ticket::factory()->create(['status' => $status]);
    }

    $chart = statusDistribution();
    $byLabel = array_combine($chart['labels'], $chart['colors']);

    expect($chart['labels'])->toContain(
        TicketStatus::label('received'),
        TicketStatus::label('gso_review'),
        TicketStatus::label('for_revision'),
        TicketStatus::label('approved'),
    );
    expect($byLabel[TicketStatus::label('approved')])->toBe(TicketStatus::chartColor('approved'))
        ->and($byLabel[TicketStatus::label('for_revision')])->toBe(TicketStatus::chartColor('for_revision'));

    // Every slice is distinguishable.
    expect(array_unique($chart['colors']))->toHaveCount(count($chart['colors']));
});
