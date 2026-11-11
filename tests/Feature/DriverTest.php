<?php

use App\Livewire\Drivers\DriverForm;
use App\Livewire\Drivers\DriverList;
use App\Models\Driver;
use App\Models\Trip;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('drivers list page requires authentication', function () {
    $this->get(route('drivers.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view drivers list page', function () {
    $this->actingAs($this->user)
        ->get(route('drivers.index'))
        ->assertOk();
});

test('drivers list displays drivers', function () {
    $driver = Driver::factory()->create(['name' => 'أحمد محمد']);

    Livewire::actingAs($this->user)
        ->test(DriverList::class)
        ->assertSee('أحمد محمد');
});

test('can create a driver', function () {
    Livewire::actingAs($this->user)
        ->test(DriverForm::class)
        ->set('name', 'علي حسن')
        ->set('notes', 'سائق متمرس')
        ->call('save')
        ->assertDispatched('driver-saved');

    $this->assertDatabaseHas('drivers', [
        'name' => 'علي حسن',
        'notes' => 'سائق متمرس',
    ]);
});

test('driver creation validates required name', function () {
    Livewire::actingAs($this->user)
        ->test(DriverForm::class)
        ->call('save')
        ->assertHasErrors(['name']);
});

test('can edit a driver', function () {
    $driver = Driver::factory()->create(['name' => 'الاسم القديم']);

    Livewire::actingAs($this->user)
        ->test(DriverForm::class, ['driverId' => $driver->id])
        ->set('name', 'الاسم الجديد')
        ->call('save')
        ->assertDispatched('driver-saved');

    $this->assertDatabaseHas('drivers', [
        'id' => $driver->id,
        'name' => 'الاسم الجديد',
    ]);
});

test('cannot delete driver with trips', function () {
    $vehicle = Vehicle::factory()->create();
    $driver = Driver::factory()->create();
    Trip::factory()->create([
        'vehicle_id' => $vehicle->id,
        'driver_id' => $driver->id,
    ]);

    $component = Livewire::actingAs($this->user)
        ->test(DriverList::class)
        ->call('deleteDriver', $driver->id);

    $component->assertSet('deleteError', 'لا يمكن حذف السائق لأن لديه رحلات مسجلة');

    $this->assertDatabaseHas('drivers', ['id' => $driver->id]);
});

test('can delete driver without trips', function () {
    $driver = Driver::factory()->create();

    Livewire::actingAs($this->user)
        ->test(DriverList::class)
        ->call('deleteDriver', $driver->id);

    $this->assertDatabaseMissing('drivers', ['id' => $driver->id]);
});
