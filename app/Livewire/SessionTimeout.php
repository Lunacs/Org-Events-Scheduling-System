<?php

namespace App\Livewire;

use Livewire\Component;

class SessionTimeout extends Component
{
    public $showWarning = false;

    public $timeRemaining = 0;

    /**
     * Keep the session alive by making a request to the server.
     * This refreshes the session's last_activity timestamp.
     */
    public function keepAlive(): void
    {
        // Simply touching the session updates last_activity
        session()->put('last_activity_refresh', now()->timestamp);

        $this->showWarning = false;

        // Dispatch event to reset JavaScript timer
        $this->dispatch('session-refreshed');
    }

    /**
     * Show the warning modal.
     * Called from JavaScript when timeout is approaching.
     */
    public function showWarningModal(int $seconds): void
    {
        $this->showWarning = true;
        $this->timeRemaining = $seconds;
    }

    public function render()
    {
        return view('livewire.session-timeout');
    }
}
