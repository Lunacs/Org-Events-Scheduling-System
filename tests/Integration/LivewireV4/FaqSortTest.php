<?php

use App\Livewire\Superadmin\FaqManager;
use App\Models\Faq;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;

uses(DatabaseTransactions::class);

beforeEach(function () {
    $role = Roles::firstOrCreate(['role_name' => 'superadmin']);
    $this->superadmin = User::factory()->create(['role_id' => $role->role_id]);
});

it('persists the new display_order via handleSort', function () {
    $faqA = Faq::create(['question' => 'Q1', 'answer' => 'A1', 'display_order' => 1, 'is_active' => true]);
    $faqB = Faq::create(['question' => 'Q2', 'answer' => 'A2', 'display_order' => 2, 'is_active' => true]);
    $faqC = Faq::create(['question' => 'Q3', 'answer' => 'A3', 'display_order' => 3, 'is_active' => true]);

    Livewire::actingAs($this->superadmin)
        ->test(FaqManager::class)
        ->call('handleSort', $faqA->id, 2);

    expect(Faq::find($faqA->id)->display_order)->toBe(3)
        ->and(Faq::find($faqB->id)->display_order)->toBe(1)
        ->and(Faq::find($faqC->id)->display_order)->toBe(2);
});

it('handles moving an item to an earlier position', function () {
    $faqA = Faq::create(['question' => 'Q1', 'answer' => 'A1', 'display_order' => 1, 'is_active' => true]);
    $faqB = Faq::create(['question' => 'Q2', 'answer' => 'A2', 'display_order' => 2, 'is_active' => true]);
    $faqC = Faq::create(['question' => 'Q3', 'answer' => 'A3', 'display_order' => 3, 'is_active' => true]);

    Livewire::actingAs($this->superadmin)
        ->test(FaqManager::class)
        ->call('handleSort', $faqC->id, 0);

    expect(Faq::find($faqC->id)->display_order)->toBe(1)
        ->and(Faq::find($faqA->id)->display_order)->toBe(2)
        ->and(Faq::find($faqB->id)->display_order)->toBe(3);
});

it('does nothing when position unchanged', function () {
    $faq = Faq::create(['question' => 'Q1', 'answer' => 'A1', 'display_order' => 1, 'is_active' => true]);

    Livewire::actingAs($this->superadmin)
        ->test(FaqManager::class)
        ->call('handleSort', $faq->id, 0);

    expect(Faq::find($faq->id)->display_order)->toBe(1);
});

it('renders the FAQ manager component', function () {
    Livewire::actingAs($this->superadmin)
        ->test(FaqManager::class)
        ->assertSuccessful();
});
