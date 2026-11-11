<div>
    <form wire:submit="save" class="space-y-6">

        {{-- Row 1: Trip Number (read-only) + Trip Date --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">رقم الرحلة</label>
                <input type="text"
                       value="{{ $tripNumber }}"
                       readonly
                       class="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm bg-gray-50 text-gray-500 cursor-not-allowed">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الرحلة <span class="text-red-500">*</span></label>
                <input type="date"
                       wire:model="tripDate"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('tripDate')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Row 2: Vehicle + Driver --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">المركبة <span class="text-red-500">*</span></label>
                <select wire:model="vehicleId"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— اختر المركبة —</option>
                    @foreach ($vehicles as $vehicle)
                        <option value="{{ $vehicle->id }}">{{ $vehicle->institutional_number }} — {{ $vehicle->manufacturer }} {{ $vehicle->model }}</option>
                    @endforeach
                </select>
                @error('vehicleId')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">السائق <span class="text-red-500">*</span></label>
                <select wire:model="driverId"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— اختر السائق —</option>
                    @foreach ($drivers as $driver)
                        <option value="{{ $driver->id }}">{{ $driver->name }}</option>
                    @endforeach
                </select>
                @error('driverId')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Row 3: Destination + Purpose --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الوجهة <span class="text-red-500">*</span></label>
                <input type="text"
                       wire:model="destination"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="مثال: وزارة المالية، المدينة">
                @error('destination')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الغرض من الرحلة <span class="text-red-500">*</span></label>
                <input type="text"
                       wire:model="purpose"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="مثال: اجتماع رسمي، توصيل مستندات">
                @error('purpose')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Row 4: Departure Time + Return Time --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">وقت المغادرة</label>
                <input type="time"
                       wire:model="departureTime"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('departureTime')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">وقت العودة</label>
                <input type="time"
                       wire:model="returnTime"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('returnTime')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Row 5: Odometer Start + Odometer End --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">قراءة العداد عند المغادرة (كم) <span class="text-red-500">*</span></label>
                <input type="number"
                       wire:model.live="odometerStart"
                       min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="0">
                @error('odometerStart')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    قراءة العداد عند العودة (كم)
                    <span class="text-gray-400 font-normal text-xs">— اتركه فارغاً لرحلة مفتوحة</span>
                </label>
                <input type="number"
                       wire:model.live="odometerEnd"
                       min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="اختياري">
                @error('odometerEnd')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Computed Distance --}}
        @if ($computedDistance !== null)
            <div class="bg-blue-50 border border-blue-200 rounded-lg px-4 py-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                </svg>
                <span class="text-sm text-blue-700">
                    المسافة المحسوبة: <strong>{{ number_format($computedDistance) }} كم</strong>
                </span>
            </div>
        @else
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg px-4 py-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-yellow-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="text-sm text-yellow-700">رحلة مفتوحة — أدخل قراءة العداد عند العودة لإغلاق الرحلة وحساب المسافة.</span>
            </div>
        @endif

        {{-- Notes --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
            <textarea wire:model="notes"
                      rows="3"
                      class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                      placeholder="أي ملاحظات إضافية..."></textarea>
            @error('notes')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
            <button type="button"
                    wire:click="$dispatch('close-modal')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                إلغاء
            </button>
            <button type="submit"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                <span wire:loading.remove wire:target="save">حفظ</span>
                <span wire:loading wire:target="save">جاري الحفظ...</span>
            </button>
        </div>

    </form>
</div>
