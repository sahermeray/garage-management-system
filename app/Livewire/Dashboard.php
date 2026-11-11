<?php

namespace App\Livewire;

use App\Models\FuelRecord;
use App\Models\Repair;
use App\Models\Setting;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public function render(): View
    {
        $now = now();
        $monthStart = $now->copy()->startOfMonth();
        $monthEnd = $now->copy()->endOfMonth();

        // Vehicle stats
        $totalVehicles = Vehicle::count();
        $activeVehicles = Vehicle::where('status', 'active')->count();
        $underRepairVehicles = Vehicle::where('status', 'under_repair')->count();
        $outOfServiceVehicles = Vehicle::where('status', 'out_of_service')->count();

        // This month stats
        $monthTrips = Trip::whereBetween('trip_date', [$monthStart, $monthEnd])->count();
        $monthRepairs = Repair::whereBetween('repair_date', [$monthStart, $monthEnd])->count();
        $monthRepairExpenses = (float) Repair::whereBetween('repair_date', [$monthStart, $monthEnd])->sum('total_cost');
        $monthFuelExpenses = (float) FuelRecord::whereBetween('date', [$monthStart, $monthEnd])->sum('total_cost');
        $monthTotalExpenses = $monthRepairExpenses + $monthFuelExpenses;

        // Recent data
        $recentTrips = Trip::with(['vehicle', 'driver'])
            ->orderByDesc('trip_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        $recentRepairs = Repair::with('vehicle')
            ->orderByDesc('repair_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get();

        // Top 5 vehicles by total expenses this month
        $topVehiclesByExpenses = Vehicle::query()
            ->select('vehicles.id', 'vehicles.institutional_number', 'vehicles.manufacturer', 'vehicles.model')
            ->leftJoin('repairs', function ($join) use ($monthStart, $monthEnd) {
                $join->on('repairs.vehicle_id', '=', 'vehicles.id')
                    ->whereBetween('repairs.repair_date', [$monthStart, $monthEnd]);
            })
            ->leftJoin('fuel_records', function ($join) use ($monthStart, $monthEnd) {
                $join->on('fuel_records.vehicle_id', '=', 'vehicles.id')
                    ->whereBetween('fuel_records.date', [$monthStart, $monthEnd]);
            })
            ->selectRaw('COALESCE(SUM(repairs.total_cost), 0) + COALESCE(SUM(fuel_records.total_cost), 0) as total_expenses')
            ->groupBy('vehicles.id', 'vehicles.institutional_number', 'vehicles.manufacturer', 'vehicles.model')
            ->orderByDesc('total_expenses')
            ->limit(5)
            ->get();

        $currency = Setting::get('currency', 'ل.ع');

        return view('livewire.dashboard', [
            'totalVehicles' => $totalVehicles,
            'activeVehicles' => $activeVehicles,
            'underRepairVehicles' => $underRepairVehicles,
            'outOfServiceVehicles' => $outOfServiceVehicles,
            'monthTrips' => $monthTrips,
            'monthRepairs' => $monthRepairs,
            'monthRepairExpenses' => $monthRepairExpenses,
            'monthFuelExpenses' => $monthFuelExpenses,
            'monthTotalExpenses' => $monthTotalExpenses,
            'recentTrips' => $recentTrips,
            'recentRepairs' => $recentRepairs,
            'topVehiclesByExpenses' => $topVehiclesByExpenses,
            'currency' => $currency,
        ])->title('لوحة التحكم');
    }
}
