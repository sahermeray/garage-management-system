<div>
    <x-slot name="actions">
        <button wire:click="createTrip"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إضافة رحلة
        </button>
    </x-slot>

    <div class="space-y-4">

        {{-- Delete Error --}}
        @if ($deleteError)
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-center justify-between">
                <span class="text-sm">{{ $deleteError }}</span>
                <button wire:click="$set('deleteError', '')" class="text-red-500 hover:text-red-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        @endif

        {{-- Filters --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                {{-- Search --}}
                <div class="relative">
                    <svg class="absolute top-1/2 -translate-y-1/2 end-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="بحث برقم الرحلة أو الوجهة..."
                           class="w-full border border-gray-300 rounded-lg pe-9 ps-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                {{-- Vehicle Filter --}}
                <select wire:model.live="vehicleFilter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع المركبات</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->institutional_number }}</option>
                    @endforeach
                </select>

                {{-- Driver Filter --}}
                <select wire:model.live="driverFilter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع السائقين</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                    @endforeach
                </select>

                {{-- Date Range --}}
                <div class="flex gap-2">
                    <input type="date"
                           wire:model.live="dateFrom"
                           title="من تاريخ"
                           class="w-1/2 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <input type="date"
                           wire:model.live="dateTo"
                           title="إلى تاريخ"
                           class="w-1/2 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">رقم الرحلة</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">التاريخ</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">المركبة</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">السائق</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الوجهة</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">المسافة</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الحالة</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($trips as $trip)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $trip->trip_number }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $trip->trip_date->format('Y/m/d') }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $trip->vehicle?->institutional_number ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $trip->driver?->name ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $trip->destination }}</td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $trip->distance !== null ? number_format($trip->distance).' كم' : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    @if ($trip->odometer_end === null)
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-yellow-100 text-yellow-700">مفتوحة</span>
                                    @else
                                        <span class="px-2 py-0.5 text-xs rounded-full bg-green-100 text-green-700">مغلقة</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button wire:click="editTrip({{ $trip->id }})"
                                                class="text-blue-600 hover:text-blue-800 text-xs font-medium hover:underline">
                                            تعديل
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        <button wire:click="deleteTrip({{ $trip->id }})"
                                                wire:confirm="هل أنت متأكد من حذف هذه الرحلة؟"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium hover:underline">
                                            حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-12 text-center text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    لا توجد رحلات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($trips->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $trips->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- Create Modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
                    <h2 class="text-lg font-semibold text-gray-800">إضافة رحلة جديدة</h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="overflow-y-auto p-6">
                    @livewire('trips.trip-form', key('create-trip'))
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Modal --}}
    @if ($showEditModal && $editingTripId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
                    <h2 class="text-lg font-semibold text-gray-800">تعديل بيانات الرحلة</h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="overflow-y-auto p-6">
                    @livewire('trips.trip-form', ['tripId' => $editingTripId], key('edit-trip-' . $editingTripId))
                </div>
            </div>
        </div>
    @endif

</div>
