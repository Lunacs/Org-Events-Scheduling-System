<?php

use App\Livewire\DataPrivacyNotice;
use Livewire\Livewire;

it('renders the data privacy notice page for guests', function () {
    $this->get(route('data-privacy'))
        ->assertOk()
        ->assertSeeLivewire(DataPrivacyNotice::class)
        ->assertSee('Data Privacy Notice');
});

it('shows the key data privacy sections', function () {
    Livewire::test(DataPrivacyNotice::class)
        ->assertSee('What Personal Data We Collect')
        ->assertSee('Your Rights as a Data Subject')
        ->assertSee('Retention of Personal Data');
});
