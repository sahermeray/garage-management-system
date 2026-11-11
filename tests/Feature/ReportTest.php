<?php

use App\Livewire\Reports\ReportPage;
use App\Models\User;
use App\Models\Vehicle;
use Livewire\Livewire;

beforeEach(function () {
    $this->user = User::factory()->create();
});

test('reports page requires authentication', function () {
    $this->get(route('reports.index'))
        ->assertRedirect(route('login'));
});

test('authenticated user can access reports page', function () {
    $this->actingAs($this->user)
        ->get(route('reports.index'))
        ->assertOk();
});

test('reports page renders the report component', function () {
    Livewire::actingAs($this->user)
        ->test(ReportPage::class)
        ->assertSee('التقارير')
        ->assertSee('توليد التقرير');
});

test('can generate vehicle list report', function () {
    $vehicle = Vehicle::factory()->create([
        'institutional_number' => 'VH-RPT-001',
        'manufacturer' => 'Toyota',
        'model' => 'Land Cruiser',
        'status' => 'active',
    ]);

    Livewire::actingAs($this->user)
        ->test(ReportPage::class)
        ->set('reportType', ReportPage::VEHICLE_LIST)
        ->call('generateReport')
        ->assertSet('reportGenerated', true)
        ->assertSee('VH-RPT-001')
        ->assertSee('Toyota');
});

test('vehicle list report validation error when no type selected', function () {
    Livewire::actingAs($this->user)
        ->test(ReportPage::class)
        ->set('reportType', '')
        ->call('generateReport')
        ->assertSet('reportGenerated', false)
        ->assertSee('يرجى اختيار نوع التقرير');
});

test('vehicle-specific report requires vehicle selection', function () {
    Livewire::actingAs($this->user)
        ->test(ReportPage::class)
        ->set('reportType', ReportPage::TRIP_HISTORY_BY_VEHICLE)
        ->set('vehicleId', '')
        ->call('generateReport')
        ->assertSet('reportGenerated', false)
        ->assertSee('يرجى اختيار المركبة');
});

test('period report requires date range', function () {
    Livewire::actingAs($this->user)
        ->test(ReportPage::class)
        ->set('reportType', ReportPage::MONTHLY_YEARLY_EXPENSES)
        ->set('dateFrom', '')
        ->set('dateTo', '')
        ->call('generateReport')
        ->assertSet('reportGenerated', false)
        ->assertSee('يرجى تحديد الفترة الزمنية');
});

test('PDF export route is accessible and returns a download response', function () {
    Vehicle::factory()->create();

    $this->actingAs($this->user)
        ->get(route('reports.export.pdf', ['type' => ReportPage::VEHICLE_LIST]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/pdf');
});

test('Excel export route is accessible and returns a download response', function () {
    Vehicle::factory()->create();

    $this->actingAs($this->user)
        ->get(route('reports.export.excel', ['type' => ReportPage::VEHICLE_LIST]))
        ->assertOk()
        ->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
