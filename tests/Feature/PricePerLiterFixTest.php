<?php

use App\Livewire\Fuel\FuelForm;
use App\Models\FuelRecord;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->vehicle = Vehicle::factory()->create();
});

test('price per liter accepts whole number 1000', function () {
    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $this->vehicle->id)
        ->set('date', '2024-06-15')
        ->set('liters', '50')
        ->set('pricePerLiter', '1000')
        ->call('save')
        ->assertHasNoErrors()
        ->assertDispatched('fuel-saved');

    $record = FuelRecord::latest('id')->first();
    expect((float) $record->price_per_liter)->toBe(1000.0)
        ->and((float) $record->total_cost)->toBe(50000.0);
});

test('price per liter accepts whole number 1500', function () {
    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $this->vehicle->id)
        ->set('date', '2024-06-15')
        ->set('liters', '40')
        ->set('pricePerLiter', '1500')
        ->call('save')
        ->assertHasNoErrors();

    $record = FuelRecord::latest('id')->first();
    expect((float) $record->price_per_liter)->toBe(1500.0)
        ->and((float) $record->total_cost)->toBe(60000.0);
});

test('price per liter accepts whole number 2500', function () {
    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $this->vehicle->id)
        ->set('date', '2024-06-15')
        ->set('liters', '30')
        ->set('pricePerLiter', '2500')
        ->call('save')
        ->assertHasNoErrors();

    $record = FuelRecord::latest('id')->first();
    expect((float) $record->price_per_liter)->toBe(2500.0)
        ->and((float) $record->total_cost)->toBe(75000.0);
});

test('price per liter accepts decimal value 1000.50', function () {
    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $this->vehicle->id)
        ->set('date', '2024-06-15')
        ->set('liters', '50')
        ->set('pricePerLiter', '1000.50')
        ->call('save')
        ->assertHasNoErrors();

    $record = FuelRecord::latest('id')->first();
    expect((float) $record->price_per_liter)->toBe(1000.50)
        ->and((float) $record->total_cost)->toBe(50025.0);
});

test('price per liter accepts decimal value 1250.75', function () {
    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $this->vehicle->id)
        ->set('date', '2024-06-15')
        ->set('liters', '40')
        ->set('pricePerLiter', '1250.75')
        ->call('save')
        ->assertHasNoErrors();

    $record = FuelRecord::latest('id')->first();
    expect((float) $record->price_per_liter)->toBe(1250.75)
        ->and((float) $record->total_cost)->toBe(50030.0);
});

test('price per liter rejects negative values', function () {
    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $this->vehicle->id)
        ->set('date', '2024-06-15')
        ->set('liters', '50')
        ->set('pricePerLiter', '-100')
        ->call('save')
        ->assertHasErrors(['pricePerLiter']);
});

test('price per liter rejects zero', function () {
    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $this->vehicle->id)
        ->set('date', '2024-06-15')
        ->set('liters', '50')
        ->set('pricePerLiter', '0')
        ->call('save')
        ->assertHasErrors(['pricePerLiter']);
});

test('fuel total cost calculates correctly for decimal price', function () {
    $component = Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('liters', '50')
        ->set('pricePerLiter', '1000.50');

    expect($component->get('totalCost'))->toBe(50025.0);
});

test('fuel form blade template uses step 0.01 for price per liter', function () {
    $content = file_get_contents(resource_path('views/livewire/fuel/fuel-form.blade.php'));

    expect($content)->toContain('wire:model.live="pricePerLiter"')
        ->and($content)->toContain('step="0.01"')
        ->and($content)->not->toContain('step="1"');
});
