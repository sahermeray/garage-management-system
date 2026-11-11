<?php

namespace App\Livewire\Reports;

use App\Models\FuelRecord;
use App\Models\Repair;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('التقارير')]
class ReportPage extends Component
{
    // Report type constants
    const VEHICLE_LIST = 'vehicle_list';

    const TRIP_HISTORY_BY_VEHICLE = 'trip_history_by_vehicle';

    const TRIPS_IN_PERIOD = 'trips_in_period';

    const REPAIR_HISTORY_BY_VEHICLE = 'repair_history_by_vehicle';

    const REPAIR_EXPENSES_BY_VEHICLE = 'repair_expenses_by_vehicle';

    const TOTAL_REPAIR_EXPENSES_IN_PERIOD = 'total_repair_expenses_in_period';

    const FUEL_EXPENSES_BY_VEHICLE = 'fuel_expenses_by_vehicle';

    const TOTAL_FUEL_EXPENSES_IN_PERIOD = 'total_fuel_expenses_in_period';

    const TOTAL_EXPENSES_BY_VEHICLE = 'total_expenses_by_vehicle';

    const MONTHLY_YEARLY_EXPENSES = 'monthly_yearly_expenses';

    public string $reportType = '';

    public string $vehicleId = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    /** @var array<int|string, mixed> */
    public array $reportData = [];

    public string $validationError = '';

    public bool $reportGenerated = false;

    /**
     * Report types that require a vehicle selection.
     *
     * @return list<string>
     */
    public static function vehicleRequiredTypes(): array
    {
        return [
            self::TRIP_HISTORY_BY_VEHICLE,
            self::REPAIR_HISTORY_BY_VEHICLE,
            self::REPAIR_EXPENSES_BY_VEHICLE,
            self::FUEL_EXPENSES_BY_VEHICLE,
            self::TOTAL_EXPENSES_BY_VEHICLE,
        ];
    }

    /**
     * Report types that require date range.
     *
     * @return list<string>
     */
    public static function dateRequiredTypes(): array
    {
        return [
            self::TRIPS_IN_PERIOD,
            self::TOTAL_REPAIR_EXPENSES_IN_PERIOD,
            self::TOTAL_FUEL_EXPENSES_IN_PERIOD,
            self::MONTHLY_YEARLY_EXPENSES,
        ];
    }

    public function updatingReportType(): void
    {
        $this->reportData = [];
        $this->validationError = '';
        $this->reportGenerated = false;
    }

    public function generateReport(): void
    {
        $this->validationError = '';
        $this->reportData = [];
        $this->reportGenerated = false;

        if ($this->reportType === '') {
            $this->validationError = 'يرجى اختيار نوع التقرير';

            return;
        }

        if (in_array($this->reportType, self::vehicleRequiredTypes()) && $this->vehicleId === '') {
            $this->validationError = 'يرجى اختيار المركبة لهذا النوع من التقارير';

            return;
        }

        if (in_array($this->reportType, self::dateRequiredTypes())) {
            if ($this->dateFrom === '' || $this->dateTo === '') {
                $this->validationError = 'يرجى تحديد الفترة الزمنية (من - إلى)';

                return;
            }
        }

        $this->reportData = match ($this->reportType) {
            self::VEHICLE_LIST => $this->buildVehicleListData(),
            self::TRIP_HISTORY_BY_VEHICLE => $this->buildTripHistoryByVehicleData(),
            self::TRIPS_IN_PERIOD => $this->buildTripsInPeriodData(),
            self::REPAIR_HISTORY_BY_VEHICLE => $this->buildRepairHistoryByVehicleData(),
            self::REPAIR_EXPENSES_BY_VEHICLE => $this->buildRepairExpensesByVehicleData(),
            self::TOTAL_REPAIR_EXPENSES_IN_PERIOD => $this->buildTotalRepairExpensesInPeriodData(),
            self::FUEL_EXPENSES_BY_VEHICLE => $this->buildFuelExpensesByVehicleData(),
            self::TOTAL_FUEL_EXPENSES_IN_PERIOD => $this->buildTotalFuelExpensesInPeriodData(),
            self::TOTAL_EXPENSES_BY_VEHICLE => $this->buildTotalExpensesByVehicleData(),
            self::MONTHLY_YEARLY_EXPENSES => $this->buildMonthlyYearlyExpensesData(),
            default => [],
        };

        $this->reportGenerated = true;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildVehicleListData(): array
    {
        return Vehicle::query()
            ->orderBy('institutional_number')
            ->get()
            ->map(fn (Vehicle $v) => [
                'institutional_number' => $v->institutional_number,
                'category' => $v->category === 'administrative' ? 'إدارية' : 'ركاب',
                'manufacturer' => $v->manufacturer,
                'model' => $v->model,
                'year' => $v->year,
                'plate_number' => $v->plate_number,
                'color' => $v->color,
                'current_odometer' => $v->current_odometer,
                'status' => $this->translateStatus($v->status),
            ])
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTripHistoryByVehicleData(): array
    {
        return Trip::query()
            ->with(['vehicle', 'driver'])
            ->where('vehicle_id', $this->vehicleId)
            ->orderByDesc('trip_date')
            ->get()
            ->map(fn (Trip $t) => [
                'trip_number' => $t->trip_number,
                'trip_date' => $t->trip_date?->format('Y-m-d'),
                'driver' => $t->driver?->name ?? '—',
                'destination' => $t->destination,
                'purpose' => $t->purpose,
                'odometer_start' => $t->odometer_start,
                'odometer_end' => $t->odometer_end ?? '—',
                'distance' => $t->distance ?? '—',
            ])
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTripsInPeriodData(): array
    {
        return Trip::query()
            ->with(['vehicle', 'driver'])
            ->when($this->vehicleId, fn ($q) => $q->where('vehicle_id', $this->vehicleId))
            ->whereDate('trip_date', '>=', $this->dateFrom)
            ->whereDate('trip_date', '<=', $this->dateTo)
            ->orderByDesc('trip_date')
            ->get()
            ->map(fn (Trip $t) => [
                'trip_number' => $t->trip_number,
                'trip_date' => $t->trip_date?->format('Y-m-d'),
                'vehicle' => $t->vehicle?->institutional_number ?? '—',
                'driver' => $t->driver?->name ?? '—',
                'destination' => $t->destination,
                'purpose' => $t->purpose,
                'distance' => $t->distance ?? '—',
            ])
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRepairHistoryByVehicleData(): array
    {
        return Repair::query()
            ->with(['vehicle'])
            ->where('vehicle_id', $this->vehicleId)
            ->orderByDesc('repair_date')
            ->get()
            ->map(fn (Repair $r) => [
                'repair_date' => $r->repair_date?->format('Y-m-d'),
                'odometer' => $r->odometer,
                'problem' => $r->problem,
                'description' => $r->description,
                'location' => $r->location === 'internal' ? 'ورشة المؤسسة' : 'ورشة خارجية',
                'workshop_name' => $r->workshop_name ?? '—',
                'invoice_number' => $r->invoice_number ?? '—',
                'total_cost' => number_format((float) $r->total_cost, 2),
            ])
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildRepairExpensesByVehicleData(): array
    {
        $repairs = Repair::query()
            ->with(['items'])
            ->where('vehicle_id', $this->vehicleId)
            ->orderByDesc('repair_date')
            ->get();

        $rows = [];
        foreach ($repairs as $repair) {
            foreach ($repair->items as $item) {
                $rows[] = [
                    'repair_date' => $repair->repair_date?->format('Y-m-d'),
                    'invoice_number' => $repair->invoice_number ?? '—',
                    'description' => $item->description,
                    'type' => $this->translateItemType($item->type),
                    'quantity' => $item->quantity,
                    'unit_price' => number_format((float) $item->unit_price, 2),
                    'total' => number_format((float) $item->total, 2),
                ];
            }
        }

        return $rows;
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTotalRepairExpensesInPeriodData(): array
    {
        return Repair::query()
            ->with(['vehicle'])
            ->when($this->vehicleId, fn ($q) => $q->where('vehicle_id', $this->vehicleId))
            ->whereDate('repair_date', '>=', $this->dateFrom)
            ->whereDate('repair_date', '<=', $this->dateTo)
            ->orderByDesc('repair_date')
            ->get()
            ->map(fn (Repair $r) => [
                'repair_date' => $r->repair_date?->format('Y-m-d'),
                'vehicle' => $r->vehicle?->institutional_number ?? '—',
                'problem' => $r->problem,
                'location' => $r->location === 'internal' ? 'ورشة المؤسسة' : 'ورشة خارجية',
                'invoice_number' => $r->invoice_number ?? '—',
                'total_cost' => number_format((float) $r->total_cost, 2),
            ])
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildFuelExpensesByVehicleData(): array
    {
        return FuelRecord::query()
            ->where('vehicle_id', $this->vehicleId)
            ->orderByDesc('date')
            ->get()
            ->map(fn (FuelRecord $f) => [
                'date' => $f->date?->format('Y-m-d'),
                'liters' => number_format((float) $f->liters, 2),
                'price_per_liter' => number_format((float) $f->price_per_liter, 2),
                'total_cost' => number_format((float) $f->total_cost, 2),
                'odometer' => $f->odometer,
                'notes' => $f->notes ?? '—',
            ])
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTotalFuelExpensesInPeriodData(): array
    {
        return FuelRecord::query()
            ->with(['vehicle'])
            ->when($this->vehicleId, fn ($q) => $q->where('vehicle_id', $this->vehicleId))
            ->whereDate('date', '>=', $this->dateFrom)
            ->whereDate('date', '<=', $this->dateTo)
            ->orderByDesc('date')
            ->get()
            ->map(fn (FuelRecord $f) => [
                'date' => $f->date?->format('Y-m-d'),
                'vehicle' => $f->vehicle?->institutional_number ?? '—',
                'liters' => number_format((float) $f->liters, 2),
                'price_per_liter' => number_format((float) $f->price_per_liter, 2),
                'total_cost' => number_format((float) $f->total_cost, 2),
                'odometer' => $f->odometer,
            ])
            ->toArray();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildTotalExpensesByVehicleData(): array
    {
        $vehicle = Vehicle::findOrFail($this->vehicleId);

        $repairTotal = Repair::query()
            ->where('vehicle_id', $this->vehicleId)
            ->sum('total_cost');

        $fuelTotal = FuelRecord::query()
            ->where('vehicle_id', $this->vehicleId)
            ->sum('total_cost');

        return [
            [
                'vehicle' => $vehicle->institutional_number,
                'plate_number' => $vehicle->plate_number,
                'repair_total' => number_format((float) $repairTotal, 2),
                'fuel_total' => number_format((float) $fuelTotal, 2),
                'grand_total' => number_format((float) ($repairTotal + $fuelTotal), 2),
            ],
        ];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function buildMonthlyYearlyExpensesData(): array
    {
        $repairByMonth = Repair::query()
            ->select(
                DB::raw("strftime('%Y', repair_date) as year"),
                DB::raw("strftime('%m', repair_date) as month"),
                DB::raw('SUM(total_cost) as repair_total')
            )
            ->whereDate('repair_date', '>=', $this->dateFrom)
            ->whereDate('repair_date', '<=', $this->dateTo)
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn ($r) => $r->year.'-'.$r->month);

        $fuelByMonth = FuelRecord::query()
            ->select(
                DB::raw("strftime('%Y', date) as year"),
                DB::raw("strftime('%m', date) as month"),
                DB::raw('SUM(total_cost) as fuel_total')
            )
            ->whereDate('date', '>=', $this->dateFrom)
            ->whereDate('date', '<=', $this->dateTo)
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn ($r) => $r->year.'-'.$r->month);

        $allKeys = $repairByMonth->keys()->merge($fuelByMonth->keys())->unique()->sort();

        $arabicMonths = [
            '01' => 'يناير', '02' => 'فبراير', '03' => 'مارس',
            '04' => 'أبريل', '05' => 'مايو', '06' => 'يونيو',
            '07' => 'يوليو', '08' => 'أغسطس', '09' => 'سبتمبر',
            '10' => 'أكتوبر', '11' => 'نوفمبر', '12' => 'ديسمبر',
        ];

        return $allKeys->map(function (string $key) use ($repairByMonth, $fuelByMonth, $arabicMonths) {
            [, $month] = explode('-', $key);
            $repair = (float) ($repairByMonth->get($key)?->repair_total ?? 0);
            $fuel = (float) ($fuelByMonth->get($key)?->fuel_total ?? 0);

            return [
                'period' => ($arabicMonths[$month] ?? $month).' '.explode('-', $key)[0],
                'repair_total' => number_format($repair, 2),
                'fuel_total' => number_format($fuel, 2),
                'grand_total' => number_format($repair + $fuel, 2),
            ];
        })->values()->toArray();
    }

    private function translateStatus(string $status): string
    {
        return match ($status) {
            'active' => 'نشطة',
            'under_repair' => 'تحت الإصلاح',
            'out_of_service' => 'خارج الخدمة',
            'retired' => 'متقاعدة',
            default => $status,
        };
    }

    private function translateItemType(string $type): string
    {
        return match ($type) {
            'spare_part' => 'قطعة غيار',
            'labor' => 'عمالة',
            'service' => 'خدمة',
            'other' => 'أخرى',
            default => $type,
        };
    }

    /**
     * @return Collection<int, Vehicle>
     */
    public function getVehiclesProperty(): Collection
    {
        return Vehicle::query()->orderBy('institutional_number')->get();
    }

    /**
     * @return array<string, string>
     */
    public static function reportTypeLabels(): array
    {
        return [
            self::VEHICLE_LIST => 'قائمة المركبات',
            self::TRIP_HISTORY_BY_VEHICLE => 'سجل رحلات مركبة',
            self::TRIPS_IN_PERIOD => 'الرحلات في فترة زمنية',
            self::REPAIR_HISTORY_BY_VEHICLE => 'سجل إصلاحات مركبة',
            self::REPAIR_EXPENSES_BY_VEHICLE => 'مصاريف إصلاح مركبة',
            self::TOTAL_REPAIR_EXPENSES_IN_PERIOD => 'إجمالي مصاريف الإصلاح في فترة',
            self::FUEL_EXPENSES_BY_VEHICLE => 'مصاريف وقود مركبة',
            self::TOTAL_FUEL_EXPENSES_IN_PERIOD => 'إجمالي مصاريف الوقود في فترة',
            self::TOTAL_EXPENSES_BY_VEHICLE => 'إجمالي مصاريف مركبة',
            self::MONTHLY_YEARLY_EXPENSES => 'المصاريف الشهرية والسنوية',
        ];
    }

    public function render(): View
    {
        return view('livewire.reports.report-page', [
            'vehicles' => $this->vehicles,
            'reportTypeLabels' => self::reportTypeLabels(),
            'needsVehicle' => in_array($this->reportType, self::vehicleRequiredTypes()),
            'needsDates' => in_array($this->reportType, self::dateRequiredTypes()),
            'dateOptional' => in_array($this->reportType, [
                self::TRIPS_IN_PERIOD,
                self::TOTAL_REPAIR_EXPENSES_IN_PERIOD,
                self::TOTAL_FUEL_EXPENSES_IN_PERIOD,
            ]),
        ]);
    }
}
