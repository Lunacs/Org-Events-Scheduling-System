<?php

use App\Support\Concerns\InteractsWithToasts;
use Livewire\Component;
use Livewire\Livewire;

/*
 * Verifies the toast replacement trait (App\Support\Concerns\InteractsWithToasts)
 * dispatches the `toast-show` browser event consumed by the <x-ui.toast> Alpine host
 * with the correct type, message, and default position/timeout.
 *
 * Lives in the Integration suite (no RefreshDatabase) because it exercises the trait
 * through Livewire's harness without touching the database.
 *
 * Covers Requirements 3.1 and 11.2.
 */

/**
 * Minimal Livewire host that mixes in the toast trait so its public methods can be
 * exercised through Livewire's testing harness.
 */
class ToastTraitTestComponent extends Component
{
    use InteractsWithToasts;

    public function triggerSuccess(): void
    {
        $this->success('Saved successfully');
    }

    public function triggerError(): void
    {
        $this->error('Something went wrong');
    }

    public function render(): string
    {
        return '<div></div>';
    }
}

it('dispatches a success toast-show with the expected type, message, and defaults', function () {
    Livewire::test(ToastTraitTestComponent::class)
        ->call('triggerSuccess')
        ->assertDispatched(
            'toast-show',
            type: 'success',
            message: 'Saved successfully',
            position: 'toast-top toast-end',
            timeout: 3000,
        );
});

it('dispatches an error toast-show with the expected type, message, and defaults', function () {
    Livewire::test(ToastTraitTestComponent::class)
        ->call('triggerError')
        ->assertDispatched(
            'toast-show',
            type: 'error',
            message: 'Something went wrong',
            position: 'toast-top toast-end',
            timeout: 3000,
        );
});
