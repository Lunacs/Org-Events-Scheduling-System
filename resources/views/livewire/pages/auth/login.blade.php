<?php

use App\Livewire\Forms\LoginForm;
use Illuminate\Support\Facades\Session;
use Livewire\Attributes\Layout;
use Livewire\Volt\Component;

new #[Layout('layouts.guest')] class extends Component {
    public function mount()
    {
        // Redirect to OSA login by default
        return redirect()->route('admin.login');
    }
}; ?>

<div>
    <p>Redirecting to login...</p>
</div>
