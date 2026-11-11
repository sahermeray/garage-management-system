<?php

namespace App\Livewire\Repairs;

use App\Models\Attachment;
use App\Models\Repair;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class RepairForm extends Component
{
    use WithFileUploads;

    public ?int $repairId = null;

    public string $vehicleId = '';

    public string $repairDate = '';

    public string $odometer = '';

    public string $problem = '';

    public string $description = '';

    public string $location = 'institution';

    public string $workshopName = '';

    public string $invoiceNumber = '';

    public string $notes = '';

    /**
     * @var array<int, array{description: string, type: string, quantity: float|string, unit_price: float|string, total: float}>
     */
    public array $items = [];

    public float $totalCost = 0;

    /** @var array<int, TemporaryUploadedFile> */
    public array $attachments = [];

    /**
     * @var array<int, array{id: int, original_name: string, file_path: string}>
     */
    public array $existingAttachments = [];

    /** @var Collection<int, Vehicle> */
    public $vehicles;

    public function mount(?int $repairId = null): void
    {
        $this->vehicles = Vehicle::query()->orderBy('institutional_number')->get();
        $this->repairId = $repairId;

        if ($this->repairId !== null) {
            $repair = Repair::with(['items', 'attachments'])->findOrFail($this->repairId);
            $this->vehicleId = (string) $repair->vehicle_id;
            $this->repairDate = $repair->repair_date->format('Y-m-d');
            $this->odometer = $repair->odometer !== null ? (string) $repair->odometer : '';
            $this->problem = $repair->problem;
            $this->description = $repair->description;
            $this->location = $repair->location;
            $this->workshopName = $repair->workshop_name ?? '';
            $this->invoiceNumber = $repair->invoice_number ?? '';
            $this->notes = $repair->notes ?? '';

            $this->items = $repair->items->map(fn ($item) => [
                'description' => $item->description,
                'type' => $item->type,
                'quantity' => (string) $item->quantity,
                'unit_price' => (string) $item->unit_price,
                'total' => (float) $item->total,
            ])->toArray();

            $this->existingAttachments = $repair->attachments->map(fn ($att) => [
                'id' => $att->id,
                'original_name' => $att->original_name,
                'file_path' => $att->file_path,
            ])->toArray();

            $this->recalculateTotalCost();
        } else {
            $this->repairDate = now()->format('Y-m-d');
            $this->items = [$this->emptyItem()];
        }
    }

    /**
     * @return array{description: string, type: string, quantity: string, unit_price: string, total: float}
     */
    private function emptyItem(): array
    {
        return [
            'description' => '',
            'type' => 'spare_part',
            'quantity' => '1',
            'unit_price' => '0',
            'total' => 0,
        ];
    }

    public function updatedItems(): void
    {
        $this->recalculateTotalCost();
    }

    private function recalculateTotalCost(): void
    {
        $total = 0;
        foreach ($this->items as $index => $item) {
            $qty = (float) ($item['quantity'] ?? 0);
            $price = (float) ($item['unit_price'] ?? 0);
            $itemTotal = $qty * $price;
            $this->items[$index]['total'] = $itemTotal;
            $total += $itemTotal;
        }
        $this->totalCost = $total;
    }

    public function addItem(): void
    {
        $this->items[] = $this->emptyItem();
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) <= 1) {
            return;
        }
        array_splice($this->items, $index, 1);
        $this->recalculateTotalCost();
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'vehicleId' => 'required|exists:vehicles,id',
            'repairDate' => 'required|date',
            'odometer' => 'nullable|integer|min:0',
            'problem' => 'required',
            'description' => 'required',
            'location' => 'required|in:institution,external',
            'workshopName' => 'nullable|max:150',
            'invoiceNumber' => 'nullable|max:100',
            'notes' => 'nullable',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|max:200',
            'items.*.type' => 'required|in:spare_part,labor,service,other',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
            'attachments.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vehicleId.required' => 'المركبة مطلوبة.',
            'vehicleId.exists' => 'المركبة المختارة غير موجودة.',
            'repairDate.required' => 'تاريخ الإصلاح مطلوب.',
            'repairDate.date' => 'تاريخ الإصلاح غير صحيح.',
            'odometer.integer' => 'قراءة العداد يجب أن تكون رقماً صحيحاً.',
            'odometer.min' => 'قراءة العداد لا يمكن أن تكون سالبة.',
            'problem.required' => 'وصف المشكلة مطلوب.',
            'description.required' => 'وصف الإصلاح مطلوب.',
            'location.required' => 'موقع الإصلاح مطلوب.',
            'location.in' => 'موقع الإصلاح غير صحيح.',
            'workshopName.max' => 'اسم الورشة يجب أن لا يتجاوز 150 حرفاً.',
            'invoiceNumber.max' => 'رقم الفاتورة يجب أن لا يتجاوز 100 حرف.',
            'items.required' => 'يجب إضافة عنصر واحد على الأقل للإصلاح.',
            'items.min' => 'يجب إضافة عنصر واحد على الأقل للإصلاح.',
            'items.*.description.required' => 'وصف العنصر مطلوب.',
            'items.*.description.max' => 'وصف العنصر يجب أن لا يتجاوز 200 حرف.',
            'items.*.type.required' => 'نوع العنصر مطلوب.',
            'items.*.type.in' => 'نوع العنصر غير صحيح.',
            'items.*.quantity.required' => 'الكمية مطلوبة.',
            'items.*.quantity.numeric' => 'الكمية يجب أن تكون رقماً.',
            'items.*.quantity.min' => 'الكمية يجب أن تكون أكبر من صفر.',
            'items.*.unit_price.required' => 'سعر الوحدة مطلوب.',
            'items.*.unit_price.numeric' => 'سعر الوحدة يجب أن يكون رقماً.',
            'items.*.unit_price.min' => 'سعر الوحدة لا يمكن أن يكون سالباً.',
            'attachments.*.mimes' => 'المرفقات يجب أن تكون ملفات PDF أو صور (JPG, JPEG, PNG).',
            'attachments.*.max' => 'حجم المرفق يجب أن لا يتجاوز 5 ميغابايت.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        DB::transaction(function () {
            $repairData = [
                'vehicle_id' => (int) $this->vehicleId,
                'repair_date' => $this->repairDate,
                'odometer' => $this->odometer !== '' ? (int) $this->odometer : null,
                'problem' => $this->problem,
                'description' => $this->description,
                'location' => $this->location,
                'workshop_name' => $this->workshopName ?: null,
                'invoice_number' => $this->invoiceNumber ?: null,
                'notes' => $this->notes ?: null,
                'total_cost' => 0,
            ];

            if ($this->repairId !== null) {
                $repair = Repair::findOrFail($this->repairId);
                $repair->update($repairData);
                $repair->items()->delete();
            } else {
                $repair = Repair::create($repairData);
            }

            foreach ($this->items as $item) {
                $qty = (float) $item['quantity'];
                $price = (float) $item['unit_price'];
                $repair->items()->create([
                    'description' => $item['description'],
                    'type' => $item['type'],
                    'quantity' => $qty,
                    'unit_price' => $price,
                    'total' => $qty * $price,
                ]);
            }

            $repair->recalculateTotalCost();

            foreach ($this->attachments as $file) {
                $path = $file->store("attachments/repairs/{$repair->id}");
                $repair->attachments()->create([
                    'file_path' => $path,
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }
        });

        $this->dispatch('repair-saved');
    }

    public function deleteAttachment(int $attachmentId): void
    {
        $attachment = Attachment::findOrFail($attachmentId);
        Storage::delete($attachment->file_path);
        $attachment->delete();

        $this->existingAttachments = array_values(
            array_filter($this->existingAttachments, fn ($a) => $a['id'] !== $attachmentId)
        );
    }

    public function render(): View
    {
        return view('livewire.repairs.repair-form');
    }
}
