<?php

use App\Livewire\Settings\SettingsPage;
use App\Models\Setting;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('can view settings page', function () {
    $this->actingAs($this->user)
        ->get(route('settings.index'))
        ->assertOk();
});

test('can update settings', function () {
    Livewire::actingAs($this->user)
        ->test(SettingsPage::class)
        ->set('institutionName', 'وزارة الداخلية')
        ->set('currency', 'د.ع')
        ->call('save')
        ->assertHasNoErrors()
        ->assertSet('saved', true);

    expect(Setting::get('institution_name'))->toBe('وزارة الداخلية')
        ->and(Setting::get('currency'))->toBe('د.ع');
});
