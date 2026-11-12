<div>
    <x-slot name="actions">
        <button onclick="Livewire.dispatch('open-create-driver')"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            إضافة سائق
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

        {{-- Search --}}
        <div class="bg-white rounded-lg border border-gray-200 p-4">
            <div class="relative max-w-md">
                <svg class="absolute top-1/2 -translate-y-1/2 end-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="بحث بالاسم..."
                       class="w-full border border-gray-300 rounded-lg pe-9 ps-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 border-b border-gray-200">
                        <tr>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الاسم</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">ملاحظات</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">عدد الرحلات</th>
                            <th class="px-4 py-3 text-start font-medium text-gray-600">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse ($drivers as $driver)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-4 py-3 font-medium text-gray-800">{{ $driver->name }}</td>
                                <td class="px-4 py-3 text-gray-600">{{ $driver->notes ?? '—' }}</td>
                                <td class="px-4 py-3 text-gray-700">{{ number_format($driver->trips_count) }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <button wire:click="editDriver({{ $driver->id }})"
                                                class="text-blue-600 hover:text-blue-800 text-xs font-medium hover:underline">
                                            تعديل
                                        </button>
                                        <span class="text-gray-300">|</span>
                                        <button wire:click="deleteDriver({{ $driver->id }})"
                                                wire:confirm="هل أنت متأكد من حذف هذا السائق؟"
                                                class="text-red-600 hover:text-red-800 text-xs font-medium hover:underline">
                                            حذف
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-12 text-center text-gray-400">
                                    <svg class="w-10 h-10 mx-auto mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    لا يوجد سائقون
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if ($drivers->hasPages())
                <div class="px-4 py-3 border-t border-gray-200">
                    {{ $drivers->links() }}
                </div>
            @endif
        </div>

    </div>

    {{-- Create Modal --}}
    @if ($showCreateModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">إضافة سائق جديد</h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    @livewire('drivers.driver-form', key('create-driver'))
                </div>
            </div>
        </div>
    @endif

    {{-- Edit Modal --}}
    @if ($showEditModal && $editingDriverId)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50"
             wire:click.self="closeModal">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">تعديل بيانات السائق</h2>
                    <button wire:click="closeModal" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="p-6">
                    @livewire('drivers.driver-form', ['driverId' => $editingDriverId], key('edit-driver-' . $editingDriverId))
                </div>
            </div>
        </div>
    @endif

</div>
