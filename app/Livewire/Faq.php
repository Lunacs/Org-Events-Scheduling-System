<?php

namespace App\Livewire;

use App\Models\Faq as FaqModel;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.public')]
#[Title('FAQ - Frequently Asked Questions')]
class Faq extends Component
{
    /**
     * Get all active FAQs grouped by category
     */
    public function getFaqsProperty()
    {
        return FaqModel::getActiveGroupedByCategory();
    }

    /**
     * Check if there are any FAQs available
     */
    public function getHasFaqsProperty(): bool
    {
        return FaqModel::active()->exists();
    }

    public function render()
    {
        return view('livewire.faq');
    }
}
