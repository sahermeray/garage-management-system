<div>
    <x-slot name="actions">
        <a href="{{ route('vehicles.index') }}"
           class="inline-flex items-center gap-2 px-4 py-2 bg-white text-gray-700 text-sm font-medium rounded-lg border border-gray-300 hover:bg-gray-50 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            العودة للمركبات
        </a>
    </x-slot>

    <div class="space-y-4">

        {{-- Vehicle Header Card --}}
        <div class="bg-white rounded-lg border border-gray-200 p-5">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-center gap-4">
                    @if ($vehicle->image_path)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($vehicle->image_path) }}"
                             alt="{{ $vehicle->institutional_number }}"
                             class="w-20 h-16 object-cover rounded-lg border border-gray-200">
                    @else
                        <div class="w-20 h-16 bg-gray-100 rounded-lg border border-gray-200 flex items-center justify-center">
                            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                      d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l1 1m9-11h4l3 4v4h-2m-5-8v8"/>
                            </svg>
                        </div>
                    @endif
                    <div>
                        <h2 class="text-xl font-bold text-gray-900">{{ $vehicle->manufacturer }} {{ $vehicle->model }}</h2>
                        <p class="text-gray-500 text-sm mt-0.5">{{ $vehicle->institutional_number }} | {{ $vehicle->plate_number }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $statusConfig = match($vehicle->status) {
                            'active'         => ['bg-green-100 text-green-700', 'نشطة'],
                            'under_repair'   => ['bg-yellow-100 text-yellow-700', 'تحت الإصلاح'],
                            'out_of_service' => ['bg-red-100 text-red-700', 'خارج الخدمة'],
                            'retired'        => ['bg-gray-100 text-gray-600', 'متقاعدة'],
                            default          => ['bg-gray-100 text-gray-600', $vehicle->status],
                        };
                    @endphp
                    <span class="px-3 py-1 text-sm rounded-full font-medium {{ $statusConfig[0] }}">
                        {{ $statusConfig[1] }}
                    </span>
                </div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-200">
                <nav class="flex overflow-x-auto">
                    @php
                        $tabs = [
                            'info'     => 'المعلومات الأساسية',
                            'trips'    => 'الرحلات',
                            'repairs'  => 'الإصلاحات',
                            'fuel'     => 'الوقود',
                            'expenses' => 'ملخص المصاريف',
                        ];
                    @endphp
                    @foreach ($tabs as $key => $label)
                        <button wire:click="setTab('{{ $key }}')"
                                class="px-5 py-3 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
                                    {{ $activeTab === $key
                                        ? 'border-blue-600 text-blue-600'
                                        : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                            {{ $label }}
                        </button>
                    @endforeach
                </nav>
            </div>

            <div class="p-6">

                {{-- Tab: Basic Info --}}
                @if ($activeTab === 'info')
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">الرقم المؤسسي</p>
                            <p class="font-medium text-gray-800">{{ $vehicle->institutional_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">الفئة</p>
                            <p class="font-medium text-gray-800">{{ $vehicle->category === 'administrative' ? 'إدارية' : 'ركاب' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">الصانع</p>
                            <p class="font-medium text-gray-800">{{ $vehicle->manufacturer }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">الموديل</p>
                            <p class="font-medium text-gray-800">{{ $vehicle->model }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">سنة الصنع</p>
                            <p class="font-medium text-gray-800">{{ $vehicle->year }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">رقم اللوحة</p>
                            <p class="font-medium text-gray-800">{{ $vehicle->plate_number }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">رقم المحرك</p>
                            <p class="font-medium text-gray-800">{{ $vehicle->engine_number ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">رقم الهيكل</p>
                            <p class="font-medium text-gray-800">{{ $vehicle->chassis_number ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">اللون</p>
                            <p class="font-medium text-gray-800">{{ $vehicle->color ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">العداد الحالي</p>
                            <p class="font-medium text-gray-800">{{ number_format($vehicle->current_odometer) }} كم</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">الحالة</p>
                            <span class="inline-block px-2 py-0.5 text-xs rounded-full font-medium {{ $statusConfig[0] }}">
                                {{ $statusConfig[1] }}
                            </span>
                        </div>
                        @if ($vehicle->notes)
                            <div class="md:col-span-2 lg:col-span-3">
                                <p class="text-xs text-gray-400 uppercase tracking-wide mb-1">ملاحظات</p>
                                <p class="text-gray-700">{{ $vehicle->notes }}</p>
                            </div>
                        @endif
                    </div>
                @endif

                {{-- Tab: Trips --}}
                @if ($activeTab === 'trips')
                    @if ($trips && $trips->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">رقم الرحلة</th>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">التاريخ</th>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">الوجهة</th>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">السائق</th>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">المسافة</th>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">الحالة</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($trips as $trip)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 text-gray-700 font-mono text-xs">{{ $trip->trip_number }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $trip->trip_date->format('Y-m-d') }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $trip->destination }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $trip->driver?->name ?? '—' }}</td>
                                            <td class="px-3 py-2 text-gray-700">
                                                {{ $trip->distance !== null ? number_format($trip->distance).' كم' : '—' }}
                                            </td>
                                            <td class="px-3 py-2">
                                                @if ($trip->return_time)
                                                    <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">مغلقة</span>
                                                @else
                                                    <span class="px-2 py-0.5 text-xs rounded-full bg-blue-100 text-blue-700">مفتوحة</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($trips->hasPages())
                            <div class="mt-4">
                                {{ $trips->links() }}
                            </div>
                        @endif
                    @else
                        <p class="text-center text-gray-400 py-8">لا توجد رحلات مسجلة</p>
                    @endif
                @endif

                {{-- Tab: Repairs --}}
                @if ($activeTab === 'repairs')
                    @if ($repairs && $repairs->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">التاريخ</th>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">المشكلة</th>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">الموقع</th>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">التكلفة الإجمالية</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($repairs as $repair)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 text-gray-700">{{ $repair->repair_date->format('Y-m-d') }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ $repair->problem }}</td>
                                            <td class="px-3 py-2 text-gray-700">
                                                {{ $repair->location === 'institution' ? 'ورشة المؤسسة' : 'ورشة خارجية' }}
                                            </td>
                                            <td class="px-3 py-2 font-medium text-gray-800">
                                                {{ number_format((float) $repair->total_cost, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($repairs->hasPages())
                            <div class="mt-4">
                                {{ $repairs->links() }}
                            </div>
                        @endif
                    @else
                        <p class="text-center text-gray-400 py-8">لا توجد إصلاحات مسجلة</p>
                    @endif
                @endif

                {{-- Tab: Fuel --}}
                @if ($activeTab === 'fuel')
                    @if ($fuelRecords && $fuelRecords->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead class="bg-gray-50 border-b border-gray-200">
                                    <tr>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">التاريخ</th>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">الكمية (لتر)</th>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">سعر اللتر</th>
                                        <th class="px-3 py-2 text-start font-medium text-gray-600">التكلفة الإجمالية</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    @foreach ($fuelRecords as $record)
                                        <tr class="hover:bg-gray-50">
                                            <td class="px-3 py-2 text-gray-700">{{ $record->date->format('Y-m-d') }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ number_format((float) $record->liters, 2) }}</td>
                                            <td class="px-3 py-2 text-gray-700">{{ number_format((float) $record->price_per_liter, 2) }}</td>
                                            <td class="px-3 py-2 font-medium text-gray-800">{{ number_format((float) $record->total_cost, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($fuelRecords->hasPages())
                            <div class="mt-4">
                                {{ $fuelRecords->links() }}
                            </div>
                        @endif
                    @else
                        <p class="text-center text-gray-400 py-8">لا توجد سجلات وقود</p>
                    @endif
                @endif

                {{-- Tab: Expenses Summary --}}
                @if ($activeTab === 'expenses' && $expenseSummary)
                    <div class="space-y-6">
                        {{-- Totals --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
                                <p class="text-sm text-orange-600 mb-1">إجمالي تكاليف الإصلاحات</p>
                                <p class="text-2xl font-bold text-orange-700">
                                    {{ number_format((float) $expenseSummary['total_repair_cost'], 2) }}
                                </p>
                            </div>
                            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
                                <p class="text-sm text-blue-600 mb-1">إجمالي تكاليف الوقود</p>
                                <p class="text-2xl font-bold text-blue-700">
                                    {{ number_format((float) $expenseSummary['total_fuel_cost'], 2) }}
                                </p>
                            </div>
                            <div class="bg-gray-50 border border-gray-300 rounded-lg p-4 text-center">
                                <p class="text-sm text-gray-600 mb-1">الإجمالي الكلي</p>
                                <p class="text-2xl font-bold text-gray-800">
                                    {{ number_format((float) $expenseSummary['grand_total'], 2) }}
                                </p>
                            </div>
                        </div>

                        {{-- By Year --}}
                        @if ($expenseSummary['all_years']->count() > 0)
                            <div>
                                <h3 class="text-sm font-semibold text-gray-700 mb-3">التفصيل السنوي</h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full text-sm">
                                        <thead class="bg-gray-50 border-b border-gray-200">
                                            <tr>
                                                <th class="px-3 py-2 text-start font-medium text-gray-600">السنة</th>
                                                <th class="px-3 py-2 text-start font-medium text-gray-600">تكاليف الإصلاحات</th>
                                                <th class="px-3 py-2 text-start font-medium text-gray-600">تكاليف الوقود</th>
                                                <th class="px-3 py-2 text-start font-medium text-gray-600">الإجمالي</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-100">
                                            @foreach ($expenseSummary['all_years'] as $year)
                                                @php
                                                    $repairYear = $expenseSummary['repairs_by_year']->get($year);
                                                    $fuelYear = $expenseSummary['fuel_by_year']->get($year);
                                                    $repairTotal = $repairYear ? (float) $repairYear->total : 0;
                                                    $fuelTotal = $fuelYear ? (float) $fuelYear->total : 0;
                                                @endphp
                                                <tr class="hover:bg-gray-50">
                                                    <td class="px-3 py-2 font-medium text-gray-800">{{ $year }}</td>
                                                    <td class="px-3 py-2 text-gray-700">{{ number_format($repairTotal, 2) }}</td>
                                                    <td class="px-3 py-2 text-gray-700">{{ number_format($fuelTotal, 2) }}</td>
                                                    <td class="px-3 py-2 font-semibold text-gray-800">{{ number_format($repairTotal + $fuelTotal, 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @else
                            <p class="text-center text-gray-400 py-8">لا توجد مصاريف مسجلة</p>
                        @endif
                    </div>
                @endif

            </div>
        </div>

    </div>

</div>
