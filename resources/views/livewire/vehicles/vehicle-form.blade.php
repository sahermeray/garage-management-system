<div>
    <form wire:submit="save" class="space-y-6">

        {{-- Row 1: Institutional Number + Category --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الرقم المؤسسي <span class="text-red-500">*</span></label>
                <input type="text"
                       wire:model="institutionalNumber"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="مثال: VH-0001">
                @error('institutionalNumber')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الفئة <span class="text-red-500">*</span></label>
                <select wire:model="category"
                        class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="administrative">إدارية</option>
                    <option value="passenger">ركاب</option>
                </select>
                @error('category')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Row 2: Manufacturer + Model --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الصانع <span class="text-red-500">*</span></label>
                <input type="text"
                       wire:model="manufacturer"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="مثال: Toyota">
                @error('manufacturer')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">الموديل <span class="text-red-500">*</span></label>
                <input type="text"
                       wire:model="model"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="مثال: Land Cruiser">
                @error('model')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Row 3: Year + Plate Number --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">سنة الصنع <span class="text-red-500">*</span></label>
                <input type="number"
                       wire:model="year"
                       min="1980"
                       max="{{ now()->year + 1 }}"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="مثال: 2022">
                @error('year')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">رقم اللوحة <span class="text-red-500">*</span></label>
                <input type="text"
                       wire:model="plateNumber"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="مثال: أ ب ج 1234">
                @error('plateNumber')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Row 4: Engine Number + Chassis Number --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">رقم المحرك</label>
                <input type="text"
                       wire:model="engineNumber"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('engineNumber')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">رقم الهيكل</label>
                <input type="text"
                       wire:model="chassisNumber"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                @error('chassisNumber')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Row 5: Color + Odometer --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">اللون</label>
                <input type="text"
                       wire:model="color"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="مثال: أبيض">
                @error('color')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">العداد الحالي (كم) <span class="text-red-500">*</span></label>
                <input type="number"
                       wire:model="currentOdometer"
                       min="0"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="0">
                @error('currentOdometer')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Row 6: Status --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">الحالة <span class="text-red-500">*</span></label>
            <select wire:model="status"
                    class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="active">نشطة</option>
                <option value="under_repair">تحت الإصلاح</option>
                <option value="out_of_service">خارج الخدمة</option>
                <option value="retired">متقاعدة</option>
            </select>
            @error('status')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        {{-- Row 7: Notes --}}
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

        {{-- Row 8: Photo --}}
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">صورة المركبة</label>
            <input type="file"
                   wire:model="photo"
                   accept="image/*"
                   class="w-full text-sm text-gray-500 file:me-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            @error('photo')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            @if ($photo)
                <div class="mt-2">
                    <img src="{{ $photo->temporaryUrl() }}" class="h-24 w-auto rounded-lg object-cover" alt="معاينة الصورة">
                </div>
            @endif
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
