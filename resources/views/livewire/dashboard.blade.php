<div class="space-y-6">

    {{-- Row 1: Vehicle Stats + Month Trips --}}
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
        <div class="bg-white rounded-lg border border-gray-200 p-4 text-center">
            <p class="text-xs text-gray-500 mb-1">إجمالي المركبات</p>
            <p class="text-3xl font-bold text-gray-800">{{ $totalVehicles }}</p>
        </div>
        <div class="bg-green-50 border border-green-200 rounded-lg p-4 text-center">
            <p class="text-xs text-green-600 mb-1">نشطة</p>
            <p class="text-3xl font-bold text-green-700">{{ $activeVehicles }}</p>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 text-center">
            <p class="text-xs text-yellow-600 mb-1">تحت الإصلاح</p>
            <p class="text-3xl font-bold text-yellow-700">{{ $underRepairVehicles }}</p>
        </div>
        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-center">
            <p class="text-xs text-red-600 mb-1">خارج الخدمة</p>
            <p class="text-3xl font-bold text-red-700">{{ $outOfServiceVehicles }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 text-center">
            <p class="text-xs text-blue-600 mb-1">رحلات الشهر</p>
            <p class="text-3xl font-bold text-blue-700">{{ $monthTrips }}</p>
        </div>
    </div>

    {{-- Row 2: Month Expenses --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 text-center">
            <p class="text-xs text-orange-600 mb-1">مصاريف الإصلاحات هذا الشهر</p>
            <p class="text-2xl font-bold text-orange-700">{{ number_format($monthRepairExpenses, 2) }}</p>
            <p class="text-xs text-orange-500 mt-1">{{ $currency }}</p>
        </div>
        <div class="bg-cyan-50 border border-cyan-200 rounded-lg p-4 text-center">
            <p class="text-xs text-cyan-600 mb-1">مصاريف الوقود هذا الشهر</p>
            <p class="text-2xl font-bold text-cyan-700">{{ number_format($monthFuelExpenses, 2) }}</p>
            <p class="text-xs text-cyan-500 mt-1">{{ $currency }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4 text-center">
            <p class="text-xs text-purple-600 mb-1">إجمالي المصاريف هذا الشهر</p>
            <p class="text-2xl font-bold text-purple-700">{{ number_format($monthTotalExpenses, 2) }}</p>
            <p class="text-xs text-purple-500 mt-1">{{ $currency }}</p>
        </div>
    </div>

    {{-- Recent Trips --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">آخر الرحلات</h2>
            <a href="{{ route('trips.index') }}" class="text-sm text-blue-600 hover:underline">عرض الكل</a>
        </div>
        @if ($recentTrips->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">رقم الرحلة</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">التاريخ</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">الوجهة</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">المركبة</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">السائق</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recentTrips as $trip)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $trip->trip_number }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $trip->trip_date->format('Y-m-d') }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $trip->destination }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $trip->vehicle?->institutional_number ?? '—' }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $trip->driver?->name ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-gray-400 py-8 text-sm">لا توجد رحلات مسجلة</p>
        @endif
    </div>

    {{-- Recent Repairs --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">آخر الإصلاحات</h2>
            <a href="{{ route('repairs.index') }}" class="text-sm text-blue-600 hover:underline">عرض الكل</a>
        </div>
        @if ($recentRepairs->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">التاريخ</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">المشكلة</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">التكلفة</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">المركبة</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($recentRepairs as $repair)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 text-gray-700">{{ $repair->repair_date->format('Y-m-d') }}</td>
                                <td class="px-4 py-2 text-gray-700">{{ $repair->problem }}</td>
                                <td class="px-4 py-2 font-medium text-gray-800">
                                    {{ number_format((float) $repair->total_cost, 2) }} {{ $currency }}
                                </td>
                                <td class="px-4 py-2 text-gray-700">{{ $repair->vehicle?->institutional_number ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-gray-400 py-8 text-sm">لا توجد إصلاحات مسجلة</p>
        @endif
    </div>

    {{-- Top Vehicles by Expenses --}}
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-800">أعلى المركبات مصاريفاً هذا الشهر</h2>
        </div>
        @if ($topVehiclesByExpenses->where('total_expenses', '>', 0)->isNotEmpty())
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">الرقم المؤسسي</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">المركبة</th>
                            <th class="px-4 py-2 text-start font-medium text-gray-600">إجمالي المصاريف</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($topVehiclesByExpenses as $v)
                            @if ((float) $v->total_expenses > 0)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-2 text-gray-700">{{ $v->institutional_number }}</td>
                                    <td class="px-4 py-2 text-gray-700">{{ $v->manufacturer }} {{ $v->model }}</td>
                                    <td class="px-4 py-2 font-medium text-gray-800">
                                        {{ number_format((float) $v->total_expenses, 2) }} {{ $currency }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <p class="text-center text-gray-400 py-8 text-sm">لا توجد مصاريف هذا الشهر</p>
        @endif
    </div>

</div>
