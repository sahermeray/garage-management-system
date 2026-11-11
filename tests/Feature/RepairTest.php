<?php

use App\Livewire\Repairs\RepairForm;
use App\Livewire\Repairs\RepairList;
use App\Models\Repair;
use App\Models\RepairItem;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('repairs list page requires authentication', function () {
    $this->get(route('repairs.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user can view repairs list page', function () {
    $this->actingAs($this->user)
        ->get(route('repairs.index'))
        ->assertOk();
});

test('repairs list displays repairs', function () {
    $vehicle = Vehicle::factory()->create(['institutional_number' => 'VH-REP-001']);
    Repair::factory()->create([
        'vehicle_id' => $vehicle->id,
        'problem' => 'مشكلة اختبار القائمة',
    ]);

    Livewire::actingAs($this->user)
        ->test(RepairList::class)
        ->assertSee('VH-REP-001')
        ->assertSee('مشكلة اختبار القائمة');
});

test('can create a repair with items', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(RepairForm::class)
        ->set('vehicleId', (string) $vehicle->id)
        ->set('repairDate', '2024-06-15')
        ->set('problem', 'تلف في محرك السيارة')
        ->set('description', 'تم استبدال المحرك بالكامل')
        ->set('location', 'external')
        ->set('workshopName', 'ورشة الأمل')
        ->set('invoiceNumber', 'INV-001')
        ->set('items', [
            [
                'description' => 'زيت المحرك',
                'type' => 'spare_part',
                'quantity' => '5',
                'unit_price' => '100000',
                'total' => 500000,
            ],
            [
                'description' => 'فلتر الزيت',
                'type' => 'spare_part',
                'quantity' => '1',
                'unit_price' => '75000',
                'total' => 75000,
            ],
            [
                'description' => 'أجرة عمل',
                'type' => 'labor',
                'quantity' => '1',
                'unit_price' => '50000',
                'total' => 50000,
            ],
        ])
        ->call('save')
        ->assertDispatched('repair-saved');

    $repair = Repair::with('items')->latest('id')->first();

    expect($repair)->not->toBeNull()
        ->and($repair->vehicle_id)->toBe($vehicle->id)
        ->and($repair->problem)->toBe('تلف في محرك السيارة')
        ->and($repair->location)->toBe('external')
        ->and($repair->workshop_name)->toBe('ورشة الأمل')
        ->and($repair->items)->toHaveCount(3);
});

test('total_cost is computed correctly from items', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(RepairForm::class)
        ->set('vehicleId', (string) $vehicle->id)
        ->set('repairDate', '2024-06-15')
        ->set('problem', 'مشكلة اختبار الحساب')
        ->set('description', 'وصف الإصلاح')
        ->set('location', 'institution')
        ->set('items', [
            [
                'description' => 'قطعة أولى',
                'type' => 'spare_part',
                'quantity' => '5',
                'unit_price' => '100000',
                'total' => 500000,
            ],
            [
                'description' => 'قطعة ثانية',
                'type' => 'labor',
                'quantity' => '1',
                'unit_price' => '75000',
                'total' => 75000,
            ],
        ])
        ->call('save')
        ->assertDispatched('repair-saved');

    $repair = Repair::latest('id')->first();

    expect((float) $repair->total_cost)->toBe(575000.0);
});

test('repair requires at least one item', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(RepairForm::class)
        ->set('vehicleId', (string) $vehicle->id)
        ->set('repairDate', '2024-06-15')
        ->set('problem', 'مشكلة ما')
        ->set('description', 'وصف ما')
        ->set('location', 'institution')
        ->set('items', [])
        ->call('save')
        ->assertHasErrors(['items']);
});

test('repair item quantity must be greater than zero', function () {
    $vehicle = Vehicle::factory()->create();

    Livewire::actingAs($this->user)
        ->test(RepairForm::class)
        ->set('vehicleId', (string) $vehicle->id)
        ->set('repairDate', '2024-06-15')
        ->set('problem', 'مشكلة ما')
        ->set('description', 'وصف ما')
        ->set('location', 'institution')
        ->set('items', [
            [
                'description' => 'قطعة ما',
                'type' => 'spare_part',
                'quantity' => '0',
                'unit_price' => '1000',
                'total' => 0,
            ],
        ])
        ->call('save')
        ->assertHasErrors(['items.0.quantity']);
});

test('DB transaction rolls back when item validation fails', function () {
    $vehicle = Vehicle::factory()->create();

    $initialRepairCount = Repair::count();
    $initialItemCount = RepairItem::count();

    Livewire::actingAs($this->user)
        ->test(RepairForm::class)
        ->set('vehicleId', (string) $vehicle->id)
        ->set('repairDate', '2024-06-15')
        ->set('problem', 'مشكلة ما')
        ->set('description', 'وصف ما')
        ->set('location', 'institution')
        ->set('items', [
            [
                'description' => '',
                'type' => 'spare_part',
                'quantity' => '0',
                'unit_price' => '-100',
                'total' => 0,
            ],
        ])
        ->call('save')
        ->assertHasErrors();

    expect(Repair::count())->toBe($initialRepairCount)
        ->and(RepairItem::count())->toBe($initialItemCount);
});

test('can edit an existing repair', function () {
    $vehicle = Vehicle::factory()->create();
    $repair = Repair::factory()->create([
        'vehicle_id' => $vehicle->id,
        'problem' => 'مشكلة قديمة',
    ]);
    RepairItem::factory()->create([
        'repair_id' => $repair->id,
        'description' => 'قطعة قديمة',
        'type' => 'spare_part',
        'quantity' => 1,
        'unit_price' => 50000,
        'total' => 50000,
    ]);

    Livewire::actingAs($this->user)
        ->test(RepairForm::class, ['repairId' => $repair->id])
        ->assertSet('problem', 'مشكلة قديمة')
        ->set('problem', 'مشكلة محدّثة')
        ->set('items', [
            [
                'description' => 'قطعة جديدة',
                'type' => 'spare_part',
                'quantity' => '2',
                'unit_price' => '30000',
                'total' => 60000,
            ],
        ])
        ->call('save')
        ->assertDispatched('repair-saved');

    expect($repair->fresh()->problem)->toBe('مشكلة محدّثة')
        ->and($repair->fresh()->items)->toHaveCount(1)
        ->and((float) $repair->fresh()->total_cost)->toBe(60000.0);
});
