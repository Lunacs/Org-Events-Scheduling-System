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

    protected const AVAILABLE_STYLES = [
        'big-ears' => 'Big Ears',
    ];

    protected const AVATAR_SEEDS = [
        'felix', 'aneka', 'bob', 'charlie', 'david', 'emma', 'frank', 'grace',
        'hannah', 'ivan', 'julia', 'kevin', 'laura', 'mike', 'nina', 'oliver',
        'peter', 'quinn', 'rachel', 'sam', 'tina', 'uma', 'victor', 'wendy',
    ];

    protected const DEFAULT_STYLE = 'big-ears';
    protected const DEFAULT_SEED = 'felix';

    public function mount()
    {
        $user = Auth::user();
        $this->selectedStyle = $user->avatar_style ?? self::DEFAULT_STYLE;
        $this->selectedSeed = $user->avatar_seed ?? self::DEFAULT_SEED;
    }

    public function getAvatarOptionsProperty()
    {
        // Generate flat array of avatar options
        $options = [];

        foreach (self::AVAILABLE_STYLES as $styleId => $styleName) {
            foreach (self::AVATAR_SEEDS as $seed) {
                $options[] = [
                    'style' => $styleId,
                    'seed' => $seed,
                    'id' => "{$styleId}-{$seed}",
                ];
            }
        }

        return $options;
    }

    public function saveAvatar($style, $seed)
    {
        // Validate inputs
        if (!isset(self::AVAILABLE_STYLES[$style]) || !in_array($seed, self::AVATAR_SEEDS, true)) {
            $this->error('Invalid avatar selection.', position: 'toast-top');
            return;
        }

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
        return view('livewire.avatar-selector', [
            'avatarOptions' => $this->avatarOptions,
        ]);
    }
}
