<?php

namespace App\Livewire;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.public')]
class AboutUs extends Component
{
    public $developers = [
        [
            'name' => 'Acov',
            'role' => 'Developer',
            'image' => 'images/developers/acov.jpg',
            'facebook' => 'https://www.facebook.com/adrian.acob.2024'
        ],
        [
            'name' => 'Kurt',
            'role' => 'Developer',
            'image' => 'images/developers/kurt.jpg',
            'facebook' => 'https://www.facebook.com/oxfordm12'
        ],
        [
            'name' => 'Lex',
            'role' => 'Developer',
            'image' => 'images/developers/lex.jpg',
            'facebook' => 'https://www.facebook.com/lexerichson.talavera'
        ],
        [
            'name' => 'Maykel',
            'role' => 'Developer',
            'image' => 'images/developers/maykel.jpg',
            'facebook' => 'https://www.facebook.com/michael.silva.674058'
        ],
        [
            'name' => 'Rel',
            'role' => 'Developer',
            'image' => 'images/developers/rel.jpg',
            'facebook' => 'https://www.facebook.com/johnrel.parente.5'
        ],
        [
            'name' => 'Rey',
            'role' => 'Developer',
            'image' => 'images/developers/rey.jpg',
            'facebook' => 'https://www.facebook.com/johnreygabonc1729/'
        ],
    ];

    public function render()
    {
        return view('livewire.about-us');
    }
}
