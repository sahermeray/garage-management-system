<?php

use App\Livewire\Vehicles\VehicleDetail;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('can view vehicle detail page', function () {
    $vehicle = Vehicle::factory()->create();

    $this->actingAs($this->user)
        ->get(route('vehicles.show', $vehicle))
        ->assertOk();
});

test('vehicle detail shows tabs', function () {
    $vehicle = Vehicle::factory()->create([
        'manufacturer' => 'Toyota',
        'model' => 'Land Cruiser',
    ]);

    Livewire::actingAs($this->user)
        ->test(VehicleDetail::class, ['vehicle' => $vehicle])
        ->assertSee('المعلومات الأساسية')
        ->assertSee('الرحلات')
        ->assertSee('الإصلاحات')
        ->assertSee('الوقود')
        ->assertSee('ملخص المصاريف');
});
