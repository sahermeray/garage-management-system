<?php

namespace App\Http\Controllers;

use App\Exports\ReportExport;
use App\Livewire\Reports\ReportPage;
use App\Models\FuelRecord;
use App\Models\Repair;
use App\Models\Setting;
use App\Models\Trip;
use App\Models\Vehicle;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function exportPdf(Request $request): Response
    {
        $type = $request->query('type', '');
        $vehicleId = $request->query('vehicle_id', '');
        $dateFrom = $request->query('date_from', '');
        $dateTo = $request->query('date_to', '');

        $data = $this->buildReportData($type, $vehicleId, $dateFrom, $dateTo);
        $title = ReportPage::reportTypeLabels()[$type] ?? 'تقرير';
        $vehicleLabel = $vehicleId ? (Vehicle::find($vehicleId)?->institutional_number ?? '') : '';
        $institutionName = Setting::get('institution_name', 'المرآب');

        $pdf = Pdf::loadView('reports.pdf.layout', compact(
            'data', 'type', 'title', 'vehicleLabel', 'dateFrom', 'dateTo', 'institutionName'
        ))->setPaper('a4', 'landscape');

        $filename = 'report-'.now()->format('Y-m-d').'.pdf';

        return $pdf->download($filename);
    }

    public function exportExcel(Request $request): BinaryFileResponse|StreamedResponse
    {
        $type = $request->query('type', '');
        $vehicleId = $request->query('vehicle_id', '');
        $dateFrom = $request->query('date_from', '');
        $dateTo = $request->query('date_to', '');

        $data = $this->buildReportData($type, $vehicleId, $dateFrom, $dateTo);
        $title = ReportPage::reportTypeLabels()[$type] ?? 'تقرير';
        $filename = 'report-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(
            new ReportExport($title, $data),
            $filename
        );
    }

    /**
     * @return array<int|string, mixed>
     */
    private function buildReportData(string $type, string $vehicleId, string $dateFrom, string $dateTo): array
    {
        return match ($type) {
            ReportPage::VEHICLE_LIST => $this->vehicleListData(),
            ReportPage::TRIP_HISTORY_BY_VEHICLE => $this->tripHistoryByVehicleData($vehicleId),
            ReportPage::TRIPS_IN_PERIOD => $this->tripsInPeriodData($vehicleId, $dateFrom, $dateTo),
            ReportPage::REPAIR_HISTORY_BY_VEHICLE => $this->repairHistoryByVehicleData($vehicleId),
            ReportPage::REPAIR_EXPENSES_BY_VEHICLE => $this->repairExpensesByVehicleData($vehicleId),
            ReportPage::TOTAL_REPAIR_EXPENSES_IN_PERIOD => $this->totalRepairExpensesInPeriodData($vehicleId, $dateFrom, $dateTo),
            ReportPage::FUEL_EXPENSES_BY_VEHICLE => $this->fuelExpensesByVehicleData($vehicleId),
            ReportPage::TOTAL_FUEL_EXPENSES_IN_PERIOD => $this->totalFuelExpensesInPeriodData($vehicleId, $dateFrom, $dateTo),
            ReportPage::TOTAL_EXPENSES_BY_VEHICLE => $this->totalExpensesByVehicleData($vehicleId),
            ReportPage::MONTHLY_YEARLY_EXPENSES => $this->monthlyYearlyExpensesData($dateFrom, $dateTo),
            default => [],
        };
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function vehicleListData(): array
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
    private function tripHistoryByVehicleData(string $vehicleId): array
    {
        return Trip::query()
            ->with(['vehicle', 'driver'])
            ->where('vehicle_id', $vehicleId)
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
    private function tripsInPeriodData(string $vehicleId, string $dateFrom, string $dateTo): array
    {
        return Trip::query()
            ->with(['vehicle', 'driver'])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($dateFrom, fn ($q) => $q->whereDate('trip_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('trip_date', '<=', $dateTo))
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
    private function repairHistoryByVehicleData(string $vehicleId): array
    {
        return Repair::query()
            ->with(['vehicle'])
            ->where('vehicle_id', $vehicleId)
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
    private function repairExpensesByVehicleData(string $vehicleId): array
    {
        $repairs = Repair::query()
            ->with(['items'])
            ->where('vehicle_id', $vehicleId)
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
    private function totalRepairExpensesInPeriodData(string $vehicleId, string $dateFrom, string $dateTo): array
    {
        return Repair::query()
            ->with(['vehicle'])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($dateFrom, fn ($q) => $q->whereDate('repair_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('repair_date', '<=', $dateTo))
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
    private function fuelExpensesByVehicleData(string $vehicleId): array
    {
        return FuelRecord::query()
            ->where('vehicle_id', $vehicleId)
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
    private function totalFuelExpensesInPeriodData(string $vehicleId, string $dateFrom, string $dateTo): array
    {
        return FuelRecord::query()
            ->with(['vehicle'])
            ->when($vehicleId, fn ($q) => $q->where('vehicle_id', $vehicleId))
            ->when($dateFrom, fn ($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('date', '<=', $dateTo))
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
    private function totalExpensesByVehicleData(string $vehicleId): array
    {
        $vehicle = Vehicle::findOrFail($vehicleId);
        $repairTotal = Repair::query()->where('vehicle_id', $vehicleId)->sum('total_cost');
        $fuelTotal = FuelRecord::query()->where('vehicle_id', $vehicleId)->sum('total_cost');

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
    private function monthlyYearlyExpensesData(string $dateFrom, string $dateTo): array
    {
        $repairByMonth = Repair::query()
            ->select(
                DB::raw("strftime('%Y', repair_date) as year"),
                DB::raw("strftime('%m', repair_date) as month"),
                DB::raw('SUM(total_cost) as repair_total')
            )
            ->when($dateFrom, fn ($q) => $q->whereDate('repair_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('repair_date', '<=', $dateTo))
            ->groupBy('year', 'month')
            ->get()
            ->keyBy(fn ($r) => $r->year.'-'.$r->month);

        $fuelByMonth = FuelRecord::query()
            ->select(
                DB::raw("strftime('%Y', date) as year"),
                DB::raw("strftime('%m', date) as month"),
                DB::raw('SUM(total_cost) as fuel_total')
            )
            ->when($dateFrom, fn ($q) => $q->whereDate('date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->whereDate('date', '<=', $dateTo))
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
}
