<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
class VehicleDetail extends Component
{
    use WithPagination;

    public Vehicle $vehicle;

    public string $activeTab = 'info';

    public function mount(Vehicle $vehicle): void
    {
        $this->vehicle = $vehicle;
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
        $this->resetPage();
    }

    public function render(): View
    {
        $trips = null;
        $repairs = null;
        $fuelRecords = null;
        $expenseSummary = null;

        if ($this->activeTab === 'trips') {
            $trips = $this->vehicle->trips()
                ->with('driver')
                ->orderByDesc('trip_date')
                ->paginate(15);
        }

        if ($this->activeTab === 'repairs') {
            $repairs = $this->vehicle->repairs()
                ->orderByDesc('repair_date')
                ->paginate(15);
        }

        if ($this->activeTab === 'fuel') {
            $fuelRecords = $this->vehicle->fuelRecords()
                ->orderByDesc('date')
                ->paginate(15);
        }

        if ($this->activeTab === 'expenses') {
            $totalRepairCost = $this->vehicle->repairs()->sum('total_cost');
            $totalFuelCost = $this->vehicle->fuelRecords()->sum('total_cost');

            $repairsByYear = $this->vehicle->repairs()
                ->selectRaw('strftime(\'%Y\', repair_date) as year, SUM(total_cost) as total')
                ->groupByRaw('strftime(\'%Y\', repair_date)')
                ->orderByDesc('year')
                ->get();

            $fuelByYear = $this->vehicle->fuelRecords()
                ->selectRaw('strftime(\'%Y\', date) as year, SUM(total_cost) as total')
                ->groupByRaw('strftime(\'%Y\', date)')
                ->orderByDesc('year')
                ->get();

            $allYears = $repairsByYear->pluck('year')
                ->merge($fuelByYear->pluck('year'))
                ->unique()
                ->sort()
                ->reverse()
                ->values();

            $expenseSummary = [
                'total_repair_cost' => $totalRepairCost,
                'total_fuel_cost' => $totalFuelCost,
                'grand_total' => $totalRepairCost + $totalFuelCost,
                'repairs_by_year' => $repairsByYear->keyBy('year'),
                'fuel_by_year' => $fuelByYear->keyBy('year'),
                'all_years' => $allYears,
            ];
        }

        return view('livewire.vehicles.vehicle-detail', [
            'trips' => $trips,
            'repairs' => $repairs,
            'fuelRecords' => $fuelRecords,
            'expenseSummary' => $expenseSummary,
        ])->title($this->vehicle->institutional_number.' - '.$this->vehicle->manufacturer.' '.$this->vehicle->model);
    }
}
