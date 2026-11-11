<?php

use App\Livewire\Trips\TripForm;
use App\Livewire\Trips\TripList;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('trips list page requires authentication', function () {
    $this->get(route('trips.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view trips list page', function () {
    $this->actingAs($this->user)
        ->get(route('trips.index'))
        ->assertOk();
});

test('trips list displays trips', function () {
    $vehicle = Vehicle::factory()->create(['institutional_number' => 'VH-LIST-001']);
    $driver = Driver::factory()->create(['name' => 'سائق الاختبار']);
    Trip::factory()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'destination' => 'وجهة الاختبار',
    ]);

    Livewire::actingAs($this->user)
        ->test(TripList::class)
        ->assertSee('وجهة الاختبار')
        ->assertSee('VH-LIST-001');
});

test('can create a trip', function () {
    $vehicle = Vehicle::factory()->create(['current_odometer' => 10000]);
    $driver = Driver::factory()->create();

    Livewire::actingAs($this->user)
        ->test(TripForm::class)
        ->set('tripDate', '2024-06-15')
        ->set('vehicleId', (string) $vehicle->id)
        ->set('driverId', (string) $driver->id)
        ->set('destination', 'مكتب المحافظة')
        ->set('purpose', 'تسليم وثائق رسمية')
        ->set('odometerStart', '10000')
        ->set('odometerEnd', '10150')
        ->call('save')
        ->assertDispatched('trip-saved');

    $this->assertDatabaseHas('trips', [
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
        'destination' => 'مكتب المحافظة',
        'purpose' => 'تسليم وثائق رسمية',
        'odometer_start' => 10000,
        'odometer_end' => 10150,
        'distance' => 150,
    ]);
});

test('can create an open trip without odometer end', function () {
    $vehicle = Vehicle::factory()->create(['current_odometer' => 5000]);
    $driver = Driver::factory()->create();

    Livewire::actingAs($this->user)
        ->test(TripForm::class)
        ->set('tripDate', '2024-06-20')
        ->set('vehicleId', (string) $vehicle->id)
        ->set('driverId', (string) $driver->id)
        ->set('destination', 'المطار')
        ->set('purpose', 'استقبال ضيوف')
        ->set('odometerStart', '5000')
        ->call('save')
        ->assertDispatched('trip-saved');

    $this->assertDatabaseHas('trips', [
        'vehicle_id' => $vehicle->id,
        'destination' => 'المطار',
        'odometer_start' => 5000,
        'odometer_end' => null,
        'distance' => null,
    ]);
});

test('trip number is auto-generated', function () {
    $vehicle = Vehicle::factory()->create();
    $driver = Driver::factory()->create();

    $year = now()->year;

    Livewire::actingAs($this->user)
        ->test(TripForm::class)
        ->set('tripDate', now()->format('Y-m-d'))
        ->set('vehicleId', (string) $vehicle->id)
        ->set('driverId', (string) $driver->id)
        ->set('destination', 'وجهة ما')
        ->set('purpose', 'مهمة رسمية')
        ->set('odometerStart', '1000')
        ->call('save')
        ->assertDispatched('trip-saved');

    $trip = Trip::latest('id')->first();

    expect($trip->trip_number)->toMatch('/^TRIP-'.$year.'-\d{4}$/');
});

test('odometer end must be greater than odometer start', function () {
    $vehicle = Vehicle::factory()->create();
    $driver = Driver::factory()->create();

    Livewire::actingAs($this->user)
        ->test(TripForm::class)
        ->set('tripDate', '2024-06-15')
        ->set('vehicleId', (string) $vehicle->id)
        ->set('driverId', (string) $driver->id)
        ->set('destination', 'وجهة ما')
        ->set('purpose', 'مهمة رسمية')
        ->set('odometerStart', '10000')
        ->set('odometerEnd', '9000')
        ->call('save')
        ->assertHasErrors(['odometerEnd']);
});

test('odometer end equal to odometer start fails validation', function () {
    $vehicle = Vehicle::factory()->create();
    $driver = Driver::factory()->create();

    Livewire::actingAs($this->user)
        ->test(TripForm::class)
        ->set('tripDate', '2024-06-15')
        ->set('vehicleId', (string) $vehicle->id)
        ->set('driverId', (string) $driver->id)
        ->set('destination', 'وجهة ما')
        ->set('purpose', 'مهمة رسمية')
        ->set('odometerStart', '10000')
        ->set('odometerEnd', '10000')
        ->call('save')
        ->assertHasErrors(['odometerEnd']);
});

test('closing trip updates vehicle current odometer', function () {
    $vehicle = Vehicle::factory()->create(['current_odometer' => 10000]);
    $driver = Driver::factory()->create();

    Livewire::actingAs($this->user)
        ->test(TripForm::class)
        ->set('tripDate', '2024-06-15')
        ->set('vehicleId', (string) $vehicle->id)
        ->set('driverId', (string) $driver->id)
        ->set('destination', 'وجهة ما')
        ->set('purpose', 'مهمة رسمية')
        ->set('odometerStart', '10000')
        ->set('odometerEnd', '10500')
        ->call('save')
        ->assertDispatched('trip-saved');

    $this->assertDatabaseHas('vehicles', [
        'id' => $vehicle->id,
        'current_odometer' => 10500,
    ]);
});
