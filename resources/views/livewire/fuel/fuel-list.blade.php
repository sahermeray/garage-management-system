<div>
    <x-slot name="actions">
        <button onclick="Livewire.dispatch('open-create-fuel')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إضافة سجل وقود
        </button>
    </x-slot>

    <div class="space-y-4">

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
                           placeholder="بحث برقم المركبة أو الملاحظات..."
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
                            <th class="px-4 py-3 text-start font-medium text-gray-600">رقم المركبة</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">التاريخ</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">اللترات</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">سعر اللتر</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">التكلفة الإجمالية</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">قراءة العداد</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($fuelRecords as $record)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-800">
                                    {{ $record->vehicle?->institutional_number ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $record->date->format('Y/m/d') }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ number_format((float) $record->liters, 2) }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ number_format((float) $record->price_per_liter, 0) }} ل.س</td>
                                <td class="px-4 py-3 text-gray-700 font-medium">
                                    {{ number_format((float) $record->total_cost, 0) }} ل.س
                                </td>
                                <td class="px-4 py-3 text-gray-700">
                                    {{ $record->odometer !== null ? number_format($record->odometer) . ' كم' : '—' }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button wire:click="editFuel({{ $record->id }})"
                                                class="text-blue-600 hover:text-blue-800 text-xs font-medium hover:underline">
                                            تعديل
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        <button wire:click="deleteFuel({{ $record->id }})"
                                                wire:confirm="هل أنت متأكد من حذف هذا السجل؟"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium hover:underline">
                                            حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M3 10h2l1 2h13l1-2h2M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7M9 21v-5a1 1 0 011-1h4a1 1 0 011 1v5"/>
                                    </svg>
                                    لا توجد سجلات وقود
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($fuelRecords->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $fuelRecords->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- Create Modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4 bg-black/50 overflow-y-auto"
             wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl my-4">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">إضافة سجل وقود جديد</h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    @livewire('fuel.fuel-form', key('create-fuel'))
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Modal --}}
    @if ($showEditModal && $editingFuelId)
        <div class="fixed inset-0 z-50 flex items-start justify-center p-4 bg-black/50 overflow-y-auto"
             wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl my-4">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">تعديل سجل الوقود</h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    @livewire('fuel.fuel-form', ['fuelId' => $editingFuelId], key('edit-fuel-' . $editingFuelId))
                </div>
            </div>
        </div>
    @endif

</div>
