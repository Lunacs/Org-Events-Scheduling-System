<?php

namespace App\Livewire\Gso\Concerns;

use App\Models\Office;
use App\Models\User;

trait ResolvesOfficeContext
{
    /**
     * Resolve the office id to scope GSO data when explicit assignment is missing.
     */
    protected function resolveOfficeId(?User $user): ?int
    {
        if ($user?->office_id) {
            return (int) $user->office_id;
        }

        $gsoOfficeId = Office::query()
            ->where('office_code', 'GSO')
            ->value('office_id');

        if ($gsoOfficeId) {
            return (int) $gsoOfficeId;
        }

        return null;
    }
}
