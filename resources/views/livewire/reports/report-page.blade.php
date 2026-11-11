<div>
    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">التقارير</h1>
        <p class="text-sm text-gray-500 mt-1">توليد وتصدير تقارير النظام</p>
    </div>

    {{-- Filter Panel --}}
    <div class="bg-white rounded-lg border border-gray-200 p-5 mb-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">خيارات التقرير</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Report Type --}}
            <div class="lg:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">نوع التقرير <span class="text-red-500">*</span></label>
                <select wire:model.live="reportType"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                    <option value="">— اختر نوع التقرير —</option>
                    @foreach ($reportTypeLabels as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Vehicle Selector (conditional) --}}
            @if ($needsVehicle || $reportType === \App\Livewire\Reports\ReportPage::TRIPS_IN_PERIOD || $reportType === \App\Livewire\Reports\ReportPage::TOTAL_REPAIR_EXPENSES_IN_PERIOD || $reportType === \App\Livewire\Reports\ReportPage::TOTAL_FUEL_EXPENSES_IN_PERIOD)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        المركبة
                        @if ($needsVehicle)
                            <span class="text-red-500">*</span>
                        @else
                            <span class="text-gray-400 text-xs">(اختياري)</span>
                        @endif
                    </label>
                    <select wire:model.live="vehicleId"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        <option value="">— جميع المركبات —</option>
                        @foreach ($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}">
                                {{ $vehicle->institutional_number }} — {{ $vehicle->manufacturer }} {{ $vehicle->model }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif

            {{-- Date Range (conditional) --}}
            @if ($needsDates || $reportType === \App\Livewire\Reports\ReportPage::TRIPS_IN_PERIOD || $reportType === \App\Livewire\Reports\ReportPage::TOTAL_REPAIR_EXPENSES_IN_PERIOD || $reportType === \App\Livewire\Reports\ReportPage::TOTAL_FUEL_EXPENSES_IN_PERIOD)
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        من تاريخ
                        @if ($needsDates)
                            <span class="text-red-500">*</span>
                        @else
                            <span class="text-gray-400 text-xs">(اختياري)</span>
                        @endif
                    </label>
                    <input type="date"
                           wire:model.live="dateFrom"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        إلى تاريخ
                        @if ($needsDates)
                            <span class="text-red-500">*</span>
                        @else
                            <span class="text-gray-400 text-xs">(اختياري)</span>
                        @endif
                    </label>
                    <input type="date"
                           wire:model.live="dateTo"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            @endif
        </div>

        {{-- Validation Error --}}
        @if ($validationError)
            <div class="mt-3 bg-red-50 border border-red-200 text-red-700 px-4 py-2.5 rounded-lg text-sm flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $validationError }}
            </div>
        @endif

        {{-- Generate Button --}}
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <button wire:click="generateReport"
                    wire:loading.attr="disabled"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 disabled:opacity-60 transition-colors">
                <span wire:loading.remove wire:target="generateReport">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                </span>
                <span wire:loading wire:target="generateReport">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                </span>
                توليد التقرير
            </button>

            {{-- Export buttons — shown after report generated --}}
            @if ($reportGenerated && count($reportData) > 0)
                <a href="{{ route('reports.export.pdf', array_filter([
                        'type' => $reportType,
                        'vehicle_id' => $vehicleId,
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                    ])) }}"
                   target="_blank"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg hover:bg-red-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    تصدير PDF
                </a>

                <a href="{{ route('reports.export.excel', array_filter([
                        'type' => $reportType,
                        'vehicle_id' => $vehicleId,
                        'date_from' => $dateFrom,
                        'date_to' => $dateTo,
                    ])) }}"
                   class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    تصدير Excel
                </a>
            @endif
        </div>
    </div>

    {{-- Results --}}
    @if ($reportGenerated)
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            {{-- Results header --}}
            <div class="px-5 py-3 border-b border-gray-200 flex items-center justify-between bg-gray-50">
                <span class="text-sm font-semibold text-gray-700">
                    {{ $reportTypeLabels[$reportType] ?? '' }}
                </span>
                <span class="text-xs text-gray-500">
                    {{ count($reportData) }} {{ count($reportData) === 1 ? 'سجل' : 'سجلات' }}
                </span>
            </div>

            @if (empty($reportData))
                <div class="text-center py-12 text-gray-400">
                    <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-sm">لا توجد بيانات لهذا التقرير</p>
                </div>
            @else
                @php
                    $columns = array_keys(reset($reportData));
                    $columnLabels = [
                        'institutional_number' => 'الرقم المؤسسي',
                        'category' => 'الفئة',
                        'manufacturer' => 'الصانع',
                        'model' => 'الموديل',
                        'year' => 'سنة الصنع',
                        'plate_number' => 'رقم اللوحة',
                        'color' => 'اللون',
                        'current_odometer' => 'العداد الحالي (كم)',
                        'status' => 'الحالة',
                        'trip_number' => 'رقم الرحلة',
                        'trip_date' => 'تاريخ الرحلة',
                        'driver' => 'السائق',
                        'destination' => 'الوجهة',
                        'purpose' => 'الغرض',
                        'odometer_start' => 'العداد عند المغادرة',
                        'odometer_end' => 'العداد عند العودة',
                        'distance' => 'المسافة (كم)',
                        'vehicle' => 'المركبة',
                        'repair_date' => 'تاريخ الإصلاح',
                        'odometer' => 'قراءة العداد',
                        'problem' => 'المشكلة',
                        'description' => 'الوصف',
                        'location' => 'موقع الإصلاح',
                        'workshop_name' => 'اسم الورشة',
                        'invoice_number' => 'رقم الفاتورة',
                        'total_cost' => 'التكلفة الإجمالية',
                        'type' => 'النوع',
                        'quantity' => 'الكمية',
                        'unit_price' => 'سعر الوحدة',
                        'total' => 'المجموع',
                        'date' => 'التاريخ',
                        'liters' => 'الكمية (لتر)',
                        'price_per_liter' => 'سعر اللتر',
                        'notes' => 'ملاحظات',
                        'repair_total' => 'مصاريف الإصلاح',
                        'fuel_total' => 'مصاريف الوقود',
                        'grand_total' => 'الإجمالي الكلي',
                        'period' => 'الفترة',
                    ];
                    $sumColumns = array_intersect($columns, ['total_cost', 'total', 'repair_total', 'fuel_total', 'grand_total', 'distance']);
                @endphp
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                @foreach ($columns as $col)
                                    <th class="px-4 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wide whitespace-nowrap">
                                        {{ $columnLabels[$col] ?? $col }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($reportData as $row)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    @foreach ($columns as $col)
                                        <td class="px-4 py-3 text-sm text-gray-700 whitespace-nowrap">
                                            {{ $row[$col] ?? '—' }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach

                            {{-- Totals row --}}
                            @if (count($sumColumns) > 0)
                                <tr class="bg-blue-50 border-t-2 border-blue-200 font-semibold">
                                    @foreach ($columns as $col)
                                        @if ($col === $columns[0])
                                            <td class="px-4 py-3 text-sm text-blue-800">الإجمالي</td>
                                        @elseif (in_array($col, $sumColumns))
                                            <td class="px-4 py-3 text-sm text-blue-800">
                                                {{ number_format(collect($reportData)->sum(fn($r) => (float) str_replace(',', '', $r[$col] ?? 0)), 2) }}
                                            </td>
                                        @else
                                            <td class="px-4 py-3"></td>
                                        @endif
                                    @endforeach
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    @endif
</div>
