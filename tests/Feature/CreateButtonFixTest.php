<?php

use App\Livewire\Drivers\DriverList;
use App\Livewire\Fuel\FuelList;
use App\Livewire\Repairs\RepairList;
use App\Livewire\Trips\TripList;
use App\Livewire\Vehicles\VehicleList;
use App\Models\Driver;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

/*
 * Tests that the Add buttons (rendered in the layout header, outside the
 * Livewire component boundary) correctly open the create modal via
 * Livewire.dispatch() global events.
 */

test('dispatching open-create-vehicle opens the vehicle create modal', function () {
    Livewire::actingAs($this->user)
        ->test(VehicleList::class)
        ->assertSet('showCreateModal', false)
        ->dispatch('open-create-vehicle')
        ->assertSet('showCreateModal', true);
});

test('dispatching open-create-driver opens the driver create modal', function () {
    Livewire::actingAs($this->user)
        ->test(DriverList::class)
        ->assertSet('showCreateModal', false)
        ->dispatch('open-create-driver')
        ->assertSet('showCreateModal', true);
});

test('dispatching open-create-trip opens the trip create modal', function () {
    Vehicle::factory()->create();
    Driver::factory()->create();

    Livewire::actingAs($this->user)
        ->test(TripList::class)
        ->assertSet('showCreateModal', false)
        ->dispatch('open-create-trip')
        ->assertSet('showCreateModal', true);
});

test('dispatching open-create-repair opens the repair create modal', function () {
    Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(RepairList::class)
        ->assertSet('showCreateModal', false)
        ->dispatch('open-create-repair')
        ->assertSet('showCreateModal', true);
});

test('dispatching open-create-fuel opens the fuel create modal', function () {
    Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(FuelList::class)
        ->assertSet('showCreateModal', false)
        ->dispatch('open-create-fuel')
        ->assertSet('showCreateModal', true);
});

test('close-modal event closes the vehicle create modal', function () {
    Livewire::actingAs($this->user)
        ->test(VehicleList::class)
        ->call('createVehicle')
        ->assertSet('showCreateModal', true)
        ->dispatch('close-modal')
        ->assertSet('showCreateModal', false);
});

test('close-modal event closes the driver create modal', function () {
    Livewire::actingAs($this->user)
        ->test(DriverList::class)
        ->call('createDriver')
        ->assertSet('showCreateModal', true)
        ->dispatch('close-modal')
        ->assertSet('showCreateModal', false);
});

test('vehicles list page renders the add button with Livewire.dispatch', function () {
    $this->actingAs($this->user)
        ->get(route('vehicles.index'))
        ->assertSee("Livewire.dispatch('open-create-vehicle')", false);
});

test('drivers list page renders the add button with Livewire.dispatch', function () {
    $this->actingAs($this->user)
        ->get(route('drivers.index'))
        ->assertSee("Livewire.dispatch('open-create-driver')", false);
});

test('trips list page renders the add button with Livewire.dispatch', function () {
    $this->actingAs($this->user)
        ->get(route('trips.index'))
        ->assertSee("Livewire.dispatch('open-create-trip')", false);
});

test('repairs list page renders the add button with Livewire.dispatch', function () {
    $this->actingAs($this->user)
        ->get(route('repairs.index'))
        ->assertSee("Livewire.dispatch('open-create-repair')", false);
});

test('fuel list page renders the add button with Livewire.dispatch', function () {
    $this->actingAs($this->user)
        ->get(route('fuel.index'))
        ->assertSee("Livewire.dispatch('open-create-fuel')", false);
});
