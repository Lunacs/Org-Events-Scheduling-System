<?php

use App\Models\User;
use App\Models\Ticket;
use Illuminate\Support\Facades\Cache;

beforeEach(function () {
    Cache::flush();
});

it('prevents concurrent ticket submissions using redis locks', function () {
    $user = User::factory()->create();
    
    // Acquire the lock to simulate an ongoing submission
    $lock = Cache::lock("lock:ticket:submit:{$user->user_id}", 10);
    expect($lock->get())->toBeTrue();
    
    // Attempt to acquire the lock again (simulating a double-click)
    $secondLock = Cache::lock("lock:ticket:submit:{$user->user_id}", 10);
    expect($secondLock->get())->toBeFalse();
    
    // Release the first lock
    $lock->release();
    
    // Now we should be able to acquire it
    expect($secondLock->get())->toBeTrue();
    $secondLock->release();
});

it('prevents concurrent ticket approvals using redis locks', function () {
    $ticket = Ticket::factory()->create();
    
    // Acquire the lock to simulate an ongoing approval
    $lock = Cache::lock("lock:ticket:approve:{$ticket->ticket_id}", 10);
    expect($lock->get())->toBeTrue();
    
    // Attempt to acquire the lock again
    $secondLock = Cache::lock("lock:ticket:approve:{$ticket->ticket_id}", 10);
    expect($secondLock->get())->toBeFalse();
    
    $lock->release();
});
