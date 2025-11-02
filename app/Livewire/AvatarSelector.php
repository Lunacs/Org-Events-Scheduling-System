<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Mary\Traits\Toast;

class AvatarSelector extends Component
{
    use Toast;

    public $selectedStyle;

    public $selectedSeed;

    public $avatarOptions = [];

    public $availableStyles = [
        'big-ears' => 'Big Ears',
    ];

    public function mount()
    {
        $user = Auth::user();
        $this->selectedStyle = $user->avatar_style ?? 'big-ears';
        $this->selectedSeed = $user->avatar_seed ?? '';

        // Generate avatar options (mix of styles with different seeds)
        $this->generateAvatarOptions();
    }

    public function generateAvatarOptions()
    {
        $this->avatarOptions = [];
        // Generate 24 Big Ears variations using different seeds
        $seeds = [
            'felix', 'aneka', 'bob', 'charlie', 'david', 'emma', 'frank', 'grace',
            'hannah', 'ivan', 'julia', 'kevin', 'laura', 'mike', 'nina', 'oliver',
            'peter', 'quinn', 'rachel', 'sam', 'tina', 'uma', 'victor', 'wendy',
        ];

        foreach ($this->availableStyles as $styleId => $styleName) {
            foreach ($seeds as $seed) {
                $this->avatarOptions[] = [
                    'style' => $styleId,
                    'seed' => $seed,
                    'id' => $styleId.'-'.$seed,
                ];
            }
        }
    }

    public function saveAvatar($style, $seed)
    {
        $user = Auth::user();

        $user->update([
            'avatar_style' => $style,
            'avatar_seed' => $seed,
        ]);

        // Update local state to reflect saved values
        $this->selectedStyle = $style;
        $this->selectedSeed = $seed;

        $this->success('Avatar updated successfully!', position: 'toast-top');

        // Dispatch event to all navigation components (including Volt components)
        $this->dispatch('avatar-updated');

        // Also dispatch a global browser event for JavaScript
        $this->js('window.dispatchEvent(new CustomEvent("avatar-changed"))');
    }

    public function render()
    {
        return view('livewire.avatar-selector');
    }
}
