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
        // Use correlated subqueries instead of LEFT JOINs to avoid row multiplication
        // (a vehicle with N repairs and M fuel records would otherwise produce N×M rows,
        //  inflating each SUM by the opposite table's row count)
        $topVehiclesByExpenses = Vehicle::query()
            ->select('vehicles.id', 'vehicles.institutional_number', 'vehicles.manufacturer', 'vehicles.model')
            ->selectRaw('
                COALESCE((SELECT SUM(r.total_cost) FROM repairs r WHERE r.vehicle_id = vehicles.id AND r.repair_date BETWEEN ? AND ?), 0)
                +
                COALESCE((SELECT SUM(f.total_cost) FROM fuel_records f WHERE f.vehicle_id = vehicles.id AND f.date BETWEEN ? AND ?), 0)
                as total_expenses
            ', [$monthStart, $monthEnd, $monthStart, $monthEnd])
            ->orderByDesc('total_expenses')
            ->limit(5)
            ->get();

        $currency = Setting::get('currency', 'ل.س');

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
