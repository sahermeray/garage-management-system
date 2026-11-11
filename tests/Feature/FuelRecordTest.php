<?php

use App\Livewire\Fuel\FuelForm;
use App\Livewire\Fuel\FuelList;
use App\Models\FuelRecord;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('fuel list page requires authentication', function () {
    $this->get(route('fuel.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view fuel list page', function () {
    $this->actingAs($this->user)
        ->get(route('fuel.index'))
        ->assertOk();
});

test('fuel list displays fuel records', function () {
    $vehicle = Vehicle::factory()->create(['institutional_number' => 'VH-FUEL-001']);
    FuelRecord::factory()->create([
        'vehicle_id' => $vehicle->id,
        'liters' => 40,
        'price_per_liter' => 1500,
        'total_cost' => 60000,
    ]);

    Livewire::actingAs($this->user)
        ->test(FuelList::class)
        ->assertSee('VH-FUEL-001');
});

test('can create a fuel record', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $vehicle->id)
        ->set('date', '2024-06-15')
        ->set('liters', '50')
        ->set('pricePerLiter', '1500')
        ->set('odometer', '120000')
        ->set('notes', 'تعبئة وقود كاملة')
        ->call('save')
        ->assertDispatched('fuel-saved');

    $record = FuelRecord::latest('id')->first();

    expect($record)->not->toBeNull()
        ->and($record->vehicle_id)->toBe($vehicle->id)
        ->and((float) $record->liters)->toBe(50.0)
        ->and((float) $record->price_per_liter)->toBe(1500.0)
        ->and($record->notes)->toBe('تعبئة وقود كاملة');
});

test('total_cost is computed correctly from liters and price_per_liter', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $vehicle->id)
        ->set('date', '2024-06-15')
        ->set('liters', '40')
        ->set('pricePerLiter', '1500')
        ->call('save')
        ->assertDispatched('fuel-saved');

    $record = FuelRecord::latest('id')->first();

    expect((float) $record->total_cost)->toBe(60000.0);
});

test('live total_cost updates when liters is changed', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $vehicle->id)
        ->set('pricePerLiter', '2000')
        ->set('liters', '30')
        ->assertSet('totalCost', 60000.0);
});

test('live total_cost updates when price_per_liter is changed', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $vehicle->id)
        ->set('liters', '20')
        ->set('pricePerLiter', '1750')
        ->assertSet('totalCost', 35000.0);
});

test('liters must be greater than zero', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $vehicle->id)
        ->set('date', '2024-06-15')
        ->set('liters', '0')
        ->set('pricePerLiter', '1500')
        ->call('save')
        ->assertHasErrors(['liters']);
});

test('price_per_liter must be greater than zero', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('vehicleId', (string) $vehicle->id)
        ->set('date', '2024-06-15')
        ->set('liters', '50')
        ->set('pricePerLiter', '0')
        ->call('save')
        ->assertHasErrors(['pricePerLiter']);
});

test('vehicle_id is required to save a fuel record', function () {
    Livewire::actingAs($this->user)
        ->test(FuelForm::class)
        ->set('date', '2024-06-15')
        ->set('liters', '50')
        ->set('pricePerLiter', '1500')
        ->call('save')
        ->assertHasErrors(['vehicleId']);
});

test('can edit an existing fuel record', function () {
    $vehicle = Vehicle::factory()->create();
    $record = FuelRecord::factory()->create([
        'vehicle_id' => $vehicle->id,
        'liters' => 30,
        'price_per_liter' => 1500,
        'total_cost' => 45000,
        'notes' => 'ملاحظة قديمة',
    ]);

    Livewire::actingAs($this->user)
        ->test(FuelForm::class, ['fuelId' => $record->id])
        ->assertSet('notes', 'ملاحظة قديمة')
        ->set('liters', '60')
        ->set('pricePerLiter', '2000')
        ->set('notes', 'ملاحظة محدّثة')
        ->call('save')
        ->assertDispatched('fuel-saved');

    expect($record->fresh()->notes)->toBe('ملاحظة محدّثة')
        ->and((float) $record->fresh()->liters)->toBe(60.0)
        ->and((float) $record->fresh()->total_cost)->toBe(120000.0);
});
