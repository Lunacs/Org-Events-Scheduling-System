<?php

use App\Models\Ticket;
use App\Models\User;
use App\Policies\TicketPolicy;

test('superadmin can update any ticket', function (): void {
    $superAdmin = new class extends User
    {
        public function isSuperAdmin(): bool
        {
            return true;
        }
    };

    $ticket = new Ticket;
    $ticket->user_id = 99;

    $policy = new TicketPolicy;

    expect($policy->update($superAdmin, $ticket))->toBeTrue();
});

test('owner can update own ticket', function (): void {
    $owner = new class extends User
    {
        public function isSuperAdmin(): bool
        {
            return false;
        }
    };

    $owner->user_id = 42;

    $ticket = new Ticket;
    $ticket->user_id = 42;

    $policy = new TicketPolicy;

    expect($policy->update($owner, $ticket))->toBeTrue();
});
