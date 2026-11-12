<div class="max-w-lg">
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-base font-semibold text-gray-800">إعدادات النظام</h2>
        </div>

        <div class="p-6 space-y-5">

            @if ($saved)
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg text-sm">
                    تم حفظ الإعدادات بنجاح
                </div>
            @endif

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    اسم الجهة الحكومية
                </label>
                <input type="text"
                       wire:model="institutionName"
                       placeholder="أدخل اسم الجهة الحكومية"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('institutionName')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1.5">
                    وحدة العملة
                </label>
                <input type="text"
                       wire:model="currency"
                       placeholder="مثال: ل.س"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('currency')
                    <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="pt-2">
                <button wire:click="save"
                        class="inline-flex items-center gap-2 px-5 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    حفظ الإعدادات
                </button>
            </div>

        </div>
    </div>
</div>
