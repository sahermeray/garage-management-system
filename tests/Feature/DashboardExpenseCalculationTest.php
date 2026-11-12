<?php

use App\Models\FuelRecord;
use App\Models\Repair;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('dashboard top vehicles expense does not multiply rows from joins', function () {
    $vehicle = Vehicle::factory()->create([
        'institutional_number' => 'TEST-DASH-001',
        'manufacturer' => 'Toyota',
        'model' => 'Camry',
    ]);

    $monthStart = Carbon::now()->startOfMonth();
    $monthEnd = Carbon::now()->endOfMonth();

    // 3 repairs: 100 + 200 + 300 = 600
    Repair::factory()->create(['vehicle_id' => $vehicle->id, 'repair_date' => $monthStart, 'total_cost' => 100]);
    Repair::factory()->create(['vehicle_id' => $vehicle->id, 'repair_date' => $monthStart, 'total_cost' => 200]);
    Repair::factory()->create(['vehicle_id' => $vehicle->id, 'repair_date' => $monthStart, 'total_cost' => 300]);

    // 2 fuel records: total_cost is auto-calculated from liters × price_per_liter
    // Fuel 1: 50 × 10 = 500, Fuel 2: 25 × 10 = 250 → sum = 750
    FuelRecord::factory()->create([
        'vehicle_id' => $vehicle->id,
        'date' => $monthStart,
        'liters' => 50,
        'price_per_liter' => 10,
    ]);
    FuelRecord::factory()->create([
        'vehicle_id' => $vehicle->id,
        'date' => $monthStart,
        'liters' => 25,
        'price_per_liter' => 10,
    ]);

    // Expected: 600 (repairs) + 750 (fuel) = 1350
    // Buggy cartesian product would give: (600×2) + (750×3) = 1200 + 2250 = 3450
    $expectedTotal = 1350;

    $component = Livewire::actingAs($this->user)->test(Dashboard::class);

    $topVehicles = $component->viewData('topVehiclesByExpenses');
    $match = $topVehicles->firstWhere('institutional_number', 'TEST-DASH-001');

    expect($match)->not->toBeNull()
        ->and((float) $match->total_expenses)->toBe((float) $expectedTotal)
        ->and((float) $match->total_expenses)->not->toBe(3450.0);
});

test('dashboard top vehicles expense matches independent sum queries', function () {
    $vehicle = Vehicle::factory()->create([
        'institutional_number' => 'TEST-DASH-002',
        'manufacturer' => 'Honda',
        'model' => 'Civic',
    ]);

    $monthStart = Carbon::now()->startOfMonth();

    Repair::factory()->create(['vehicle_id' => $vehicle->id, 'repair_date' => $monthStart, 'total_cost' => 1500.50]);
    Repair::factory()->create(['vehicle_id' => $vehicle->id, 'repair_date' => $monthStart, 'total_cost' => 3000.00]);
    FuelRecord::factory()->create(['vehicle_id' => $vehicle->id, 'date' => $monthStart, 'liters' => 60, 'price_per_liter' => 75]);
    FuelRecord::factory()->create(['vehicle_id' => $vehicle->id, 'date' => $monthStart, 'liters' => 40, 'price_per_liter' => 30]);

    // Calculate using the same approach as Reports page (independent sums)
    $repairSum = (float) Repair::where('vehicle_id', $vehicle->id)->sum('total_cost');
    $fuelSum = (float) FuelRecord::where('vehicle_id', $vehicle->id)->sum('total_cost');
    $expectedTotal = $repairSum + $fuelSum;

    $component = Livewire::actingAs($this->user)->test(Dashboard::class);

    $topVehicles = $component->viewData('topVehiclesByExpenses');
    $match = $topVehicles->firstWhere('institutional_number', 'TEST-DASH-002');

    expect((float) $match->total_expenses)->toBe($expectedTotal);
});

test('dashboard excludes expenses from other months', function () {
    $vehicle = Vehicle::factory()->create([
        'institutional_number' => 'TEST-DASH-003',
        'manufacturer' => 'Nissan',
        'model' => 'Altima',
    ]);

    $lastMonth = Carbon::now()->subMonth();

    // Repair from last month — should NOT be counted
    Repair::factory()->create([
        'vehicle_id' => $vehicle->id,
        'repair_date' => $lastMonth->startOfMonth(),
        'total_cost' => 99999,
    ]);

    // Fuel from last month — should NOT be counted
    FuelRecord::factory()->create([
        'vehicle_id' => $vehicle->id,
        'date' => $lastMonth->startOfMonth(),
        'liters' => 999,
        'price_per_liter' => 999,
    ]);

    // Repair from this month — should be counted
    Repair::factory()->create([
        'vehicle_id' => $vehicle->id,
        'repair_date' => Carbon::now()->startOfMonth(),
        'total_cost' => 500,
    ]);

    $component = Livewire::actingAs($this->user)->test(Dashboard::class);

    $topVehicles = $component->viewData('topVehiclesByExpenses');
    $match = $topVehicles->firstWhere('institutional_number', 'TEST-DASH-003');

    expect((float) $match->total_expenses)->toBe(500.0);
});

test('dashboard ranks vehicles by expense correctly', function () {
    $monthStart = Carbon::now()->startOfMonth();

    $vehicleA = Vehicle::factory()->create(['institutional_number' => 'RANK-A']);
    $vehicleB = Vehicle::factory()->create(['institutional_number' => 'RANK-B']);
    $vehicleC = Vehicle::factory()->create(['institutional_number' => 'RANK-C']);

    // Vehicle A: 600 (repair) + 400 (fuel: 40×10) = 1000
    Repair::factory()->create(['vehicle_id' => $vehicleA->id, 'repair_date' => $monthStart, 'total_cost' => 600]);
    FuelRecord::factory()->create(['vehicle_id' => $vehicleA->id, 'date' => $monthStart, 'liters' => 40, 'price_per_liter' => 10]);

    // Vehicle B: 2000 (repair) + 1000 (fuel: 100×10) = 3000 (highest)
    Repair::factory()->create(['vehicle_id' => $vehicleB->id, 'repair_date' => $monthStart, 'total_cost' => 2000]);
    FuelRecord::factory()->create(['vehicle_id' => $vehicleB->id, 'date' => $monthStart, 'liters' => 100, 'price_per_liter' => 10]);

    // Vehicle C: 500 (repair only, lowest)
    Repair::factory()->create(['vehicle_id' => $vehicleC->id, 'repair_date' => $monthStart, 'total_cost' => 500]);

    $component = Livewire::actingAs($this->user)->test(Dashboard::class);

    $topVehicles = $component->viewData('topVehiclesByExpenses');

    $testVehicles = $topVehicles
        ->filter(fn ($v) => in_array($v->institutional_number, ['RANK-A', 'RANK-B', 'RANK-C']))
        ->values();

    expect($testVehicles[0]->institutional_number)->toBe('RANK-B')
        ->and((float) $testVehicles[0]->total_expenses)->toBe(3000.0)
        ->and($testVehicles[1]->institutional_number)->toBe('RANK-A')
        ->and((float) $testVehicles[1]->total_expenses)->toBe(1000.0)
        ->and($testVehicles[2]->institutional_number)->toBe('RANK-C')
        ->and((float) $testVehicles[2]->total_expenses)->toBe(500.0);
});
