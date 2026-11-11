<?php

use App\Livewire\Vehicles\VehicleForm;
use App\Livewire\Vehicles\VehicleList;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('vehicles list page requires authentication', function () {
    $this->get(route('vehicles.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view vehicles list page', function () {
    $this->actingAs($this->user)
        ->get(route('vehicles.index'))
        ->assertOk();
});

test('vehicles list displays vehicles', function () {
    $vehicle = Vehicle::factory()->create([
        'manufacturer' => 'Toyota',
        'model' => 'Land Cruiser',
    ]);

    Livewire::actingAs($this->user)
        ->test(VehicleList::class)
        ->assertSee($vehicle->institutional_number)
        ->assertSee('Toyota')
        ->assertSee('Land Cruiser');
});

test('can create a vehicle', function () {
    Livewire::actingAs($this->user)
        ->test(VehicleForm::class)
        ->set('institutionalNumber', 'VH-TEST-001')
        ->set('category', 'administrative')
        ->set('manufacturer', 'Toyota')
        ->set('model', 'Camry')
        ->set('year', '2022')
        ->set('plateNumber', 'AB-123-CD')
        ->set('currentOdometer', '5000')
        ->set('status', 'active')
        ->call('save')
        ->assertDispatched('vehicle-saved');

    $this->assertDatabaseHas('vehicles', [
        'institutional_number' => 'VH-TEST-001',
        'manufacturer' => 'Toyota',
        'model' => 'Camry',
    ]);
});

test('vehicle creation validates required fields', function () {
    Livewire::actingAs($this->user)
        ->test(VehicleForm::class)
        ->call('save')
        ->assertHasErrors(['institutionalNumber', 'manufacturer', 'model', 'year', 'plateNumber']);
});

test('vehicle institutional number must be unique', function () {
    Vehicle::factory()->create(['institutional_number' => 'VH-DUPE-001']);

    Livewire::actingAs($this->user)
        ->test(VehicleForm::class)
        ->set('institutionalNumber', 'VH-DUPE-001')
        ->set('category', 'administrative')
        ->set('manufacturer', 'Honda')
        ->set('model', 'Civic')
        ->set('year', '2020')
        ->set('plateNumber', 'XY-999-ZZ')
        ->set('currentOdometer', '0')
        ->set('status', 'active')
        ->call('save')
        ->assertHasErrors(['institutionalNumber']);
});

test('can edit a vehicle', function () {
    $vehicle = Vehicle::factory()->create(['manufacturer' => 'Ford']);

    Livewire::actingAs($this->user)
        ->test(VehicleForm::class, ['vehicleId' => $vehicle->id])
        ->set('manufacturer', 'Chevrolet')
        ->call('save')
        ->assertDispatched('vehicle-saved');

    $this->assertDatabaseHas('vehicles', [
        'id' => $vehicle->id,
        'manufacturer' => 'Chevrolet',
    ]);
});

test('cannot delete vehicle with trips', function () {
    $vehicle = Vehicle::factory()->create();
    $driver = Driver::factory()->create();
    Trip::factory()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(VehicleList::class)
        ->call('deleteVehicle', $vehicle->id);

    $component->assertSet('deleteError', 'لا يمكن حذف المركبة لأن لديها سجلات مرتبطة بها');

    $this->assertDatabaseHas('vehicles', ['id' => $vehicle->id, 'deleted_at' => null]);
});

test('can delete vehicle without records', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(VehicleList::class)
        ->call('deleteVehicle', $vehicle->id);

    $this->assertSoftDeleted('vehicles', ['id' => $vehicle->id]);
});
