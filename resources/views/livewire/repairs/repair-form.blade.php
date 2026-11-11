<div>
    <form wire:submit="save" class="space-y-6">

        {{-- Section 1: Repair Header --}}
        <div class="space-y-4">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide border-b border-gray-200 pb-2">
                بيانات الإصلاح
            </h3>

            {{-- Row 1: Vehicle + Date --}}
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
                    <label class="block text-sm font-medium text-gray-700 mb-1">تاريخ الإصلاح <span class="text-red-500">*</span></label>
                    <input type="date"
                           wire:model="repairDate"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('repairDate')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 2: Odometer + Location --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">قراءة العداد (كم)</label>
                    <input type="number"
                           wire:model="odometer"
                           min="0"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="اختياري">
                    @error('odometer')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">موقع الإصلاح <span class="text-red-500">*</span></label>
                    <select wire:model="location"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="institution">داخلي (ورشة المؤسسة)</option>
                        <option value="external">خارجي (ورشة خارجية)</option>
                    </select>
                    @error('location')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 3: Workshop Name + Invoice Number --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">اسم الورشة</label>
                    <input type="text"
                           wire:model="workshopName"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="اختياري">
                    @error('workshopName')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">رقم الفاتورة</label>
                    <input type="text"
                           wire:model="invoiceNumber"
                           class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           placeholder="اختياري">
                    @error('invoiceNumber')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Row 4: Problem --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">وصف المشكلة <span class="text-red-500">*</span></label>
                <textarea wire:model="problem"
                          rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                          placeholder="صف المشكلة التي تواجهها المركبة..."></textarea>
                @error('problem')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Row 5: Description --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">وصف الإصلاح <span class="text-red-500">*</span></label>
                <textarea wire:model="description"
                          rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                          placeholder="صف الإصلاح الذي تم إجراؤه..."></textarea>
                @error('description')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Row 6: Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ملاحظات</label>
                <textarea wire:model="notes"
                          rows="2"
                          class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                          placeholder="أي ملاحظات إضافية..."></textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Section 2: Repair Items --}}
        <div class="space-y-3">
            <div class="flex items-center justify-between border-b border-gray-200 pb-2">
                <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide">
                    عناصر الإصلاح
                </h3>
                <button type="button"
                        wire:click="addItem"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-blue-700 bg-blue-50 border border-blue-200 rounded-lg hover:bg-blue-100 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    إضافة عنصر
                </button>
            </div>

            @error('items')
                <p class="text-red-500 text-xs">{{ $message }}</p>
            @enderror
            @error('items.min')
                <p class="text-red-500 text-xs">{{ $message }}</p>
            @enderror

            {{-- Items Table --}}
            <div class="overflow-x-auto rounded-lg border border-gray-200">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-3 py-2 text-start font-medium text-gray-600 w-5/12">الوصف</th>
                            <th class="px-3 py-2 text-start font-medium text-gray-600 w-2/12">النوع</th>
                            <th class="px-3 py-2 text-start font-medium text-gray-600 w-1/12">الكمية</th>
                            <th class="px-3 py-2 text-start font-medium text-gray-600 w-2/12">سعر الوحدة</th>
                            <th class="px-3 py-2 text-start font-medium text-gray-600 w-2/12">الإجمالي</th>
                            <th class="px-3 py-2 w-8"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach ($items as $index => $item)
                            <tr wire:key="item-{{ $index }}">
                                <td class="px-3 py-2">
                                    <input type="text"
                                           wire:model.live="items.{{ $index }}.description"
                                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                           placeholder="وصف العنصر">
                                    @error('items.'.$index.'.description')
                                        <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-3 py-2">
                                    <select wire:model.live="items.{{ $index }}.type"
                                            class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500">
                                        <option value="spare_part">قطعة غيار</option>
                                        <option value="labor">أجرة عمل</option>
                                        <option value="service">خدمة</option>
                                        <option value="other">أخرى</option>
                                    </select>
                                    @error('items.'.$index.'.type')
                                        <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number"
                                           wire:model.live="items.{{ $index }}.quantity"
                                           min="0.01"
                                           step="0.01"
                                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                           placeholder="1">
                                    @error('items.'.$index.'.quantity')
                                        <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-3 py-2">
                                    <input type="number"
                                           wire:model.live="items.{{ $index }}.unit_price"
                                           min="0"
                                           step="1"
                                           class="w-full border border-gray-300 rounded px-2 py-1.5 text-sm focus:outline-none focus:ring-1 focus:ring-blue-500"
                                           placeholder="0">
                                    @error('items.'.$index.'.unit_price')
                                        <p class="text-red-500 text-xs mt-0.5">{{ $message }}</p>
                                    @enderror
                                </td>
                                <td class="px-3 py-2">
                                    <span class="text-sm font-medium text-gray-800">
                                        {{ number_format($item['total'] ?? 0, 0) }}
                                    </span>
                                </td>
                                <td class="px-3 py-2 text-center">
                                    @if (count($items) > 1)
                                        <button type="button"
                                                wire:click="removeItem({{ $index }})"
                                                class="text-red-400 hover:text-red-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Grand Total --}}
            <div class="flex justify-end">
                <div class="bg-blue-50 border border-blue-200 rounded-lg px-6 py-3 flex items-center gap-3">
                    <span class="text-sm font-medium text-blue-700">المجموع الكلي:</span>
                    <span class="text-xl font-bold text-blue-800">{{ number_format($totalCost, 0) }} ل.ع</span>
                </div>
            </div>
        </div>

        {{-- Section 3: Attachments --}}
        <div class="space-y-3">
            <h3 class="text-sm font-semibold text-gray-700 uppercase tracking-wide border-b border-gray-200 pb-2">
                المرفقات
            </h3>

            {{-- Existing Attachments --}}
            @if (count($existingAttachments) > 0)
                <div class="space-y-2">
                    <p class="text-xs text-gray-500 font-medium">المرفقات الحالية:</p>
                    @foreach ($existingAttachments as $attachment)
                        <div class="flex items-center justify-between bg-gray-50 border border-gray-200 rounded-lg px-3 py-2">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="text-sm text-gray-700">{{ $attachment['original_name'] }}</span>
                            </div>
                            <button type="button"
                                    wire:click="deleteAttachment({{ $attachment['id'] }})"
                                    wire:confirm="هل أنت متأكد من حذف هذا المرفق؟"
                                    class="text-red-400 hover:text-red-600 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- New Attachments Upload --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    إرفاق ملفات جديدة
                    <span class="text-gray-400 font-normal text-xs">(PDF أو صور، بحد أقصى 5 ميغابايت لكل ملف)</span>
                </label>
                <input type="file"
                       wire:model="attachments"
                       multiple
                       accept=".pdf,.jpg,.jpeg,.png"
                       class="w-full border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500 file:me-3 file:border-0 file:bg-blue-50 file:text-blue-700 file:text-xs file:font-medium file:py-1 file:px-3 file:rounded">
                @error('attachments.*')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror

                <div wire:loading wire:target="attachments" class="mt-2 text-xs text-blue-600">
                    جاري رفع الملفات...
                </div>
            </div>
        </div>

        {{-- Actions --}}
        <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
            <button type="button"
                    wire:click="$dispatch('close-modal')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                إلغاء
            </button>
            <button type="submit"
                    class="px-6 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors">
                <span wire:loading.remove wire:target="save">حفظ الإصلاح</span>
                <span wire:loading wire:target="save">جاري الحفظ...</span>
            </button>
        </div>

    </form>
</div>
