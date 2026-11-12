<div>
    <x-slot name="actions">
        <button onclick="Livewire.dispatch('open-create-vehicle')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إضافة مركبة
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
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="relative">
                    <svg class="absolute top-1/2 -translate-y-1/2 end-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                    </svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="بحث بالرقم أو الصانع أو الموديل..."
                           class="w-full border border-gray-300 rounded-lg pe-9 ps-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <select wire:model.live="statusFilter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الحالات</option>
                    <option value="active">نشطة</option>
                    <option value="under_repair">تحت الإصلاح</option>
                    <option value="out_of_service">خارج الخدمة</option>
                    <option value="retired">متقاعدة</option>
                </select>

                <select wire:model.live="categoryFilter"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">جميع الفئات</option>
                    <option value="administrative">إدارية</option>
                    <option value="passenger">ركاب</option>
                </select>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الرقم المؤسسي</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الصانع</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الموديل</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">السنة</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">اللوحة</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الفئة</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الحالة</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">العداد الحالي</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($vehicles as $vehicle)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3">
                                    <a href="{{ route('vehicles.show', $vehicle) }}"
                                       class="font-medium text-blue-600 hover:text-blue-800 hover:underline">
                                        {{ $vehicle->institutional_number }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ $vehicle->manufacturer }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $vehicle->model }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $vehicle->year }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ $vehicle->plate_number }}</td>
                                <td class="px-4 py-3">
                                    <span class="px-2 py-0.5 text-xs rounded-full
                                        {{ $vehicle->category === 'administrative' ? 'bg-purple-100 text-purple-700' : 'bg-cyan-100 text-cyan-700' }}">
                                        {{ $vehicle->category === 'administrative' ? 'إدارية' : 'ركاب' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3">
                                    @php
                                        $statusConfig = match($vehicle->status) {
                                            'active'         => ['bg-green-100 text-green-700', 'نشطة'],
                                            'under_repair'   => ['bg-yellow-100 text-yellow-700', 'تحت الإصلاح'],
                                            'out_of_service' => ['bg-red-100 text-red-700', 'خارج الخدمة'],
                                            'retired'        => ['bg-gray-100 text-gray-600', 'متقاعدة'],
                                            default          => ['bg-gray-100 text-gray-600', $vehicle->status],
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 text-xs rounded-full {{ $statusConfig[0] }}">
                                        {{ $statusConfig[1] }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-700">{{ number_format($vehicle->current_odometer) }} كم</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button wire:click="editVehicle({{ $vehicle->id }})"
                                                class="text-blue-600 hover:text-blue-800 text-xs font-medium hover:underline">
                                            تعديل
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        <button wire:click="deleteVehicle({{ $vehicle->id }})"
                                                wire:confirm="هل أنت متأكد من حذف هذه المركبة؟"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium hover:underline">
                                            حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-12 text-center text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l1 1m9-11h4l3 4v4h-2m-5-8v8"/>
                                    </svg>
                                    لا توجد مركبات
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($vehicles->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $vehicles->links() }}
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
                    <h2 class="text-lg font-semibold text-gray-800">إضافة مركبة جديدة</h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="overflow-y-auto p-6">
                    @livewire('vehicles.vehicle-form', key('create-vehicle'))
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Modal --}}
    @if ($showEditModal && $editingVehicleId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] flex flex-col">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200 shrink-0">
                    <h2 class="text-lg font-semibold text-gray-800">تعديل بيانات المركبة</h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="overflow-y-auto p-6">
                    @livewire('vehicles.vehicle-form', ['vehicleId' => $editingVehicleId], key('edit-vehicle-' . $editingVehicleId))
                </div>
            </div>
        </div>
    @endif

</div>
