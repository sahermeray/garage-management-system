<?php

namespace App\Livewire\Fuel;

use App\Models\Attachment;
use App\Models\FuelRecord;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class FuelForm extends Component
{
    use WithFileUploads;

    public ?int $fuelId = null;

    public string $vehicleId = '';

    public string $date = '';

    public string $liters = '';

    public string $pricePerLiter = '';

    public float $totalCost = 0;

    public string $odometer = '';

    public string $notes = '';

    /** @var array<int, TemporaryUploadedFile> */
    public array $attachments = [];

    /**
     * @var array<int, array{id: int, original_name: string, file_path: string}>
     */
    public array $existingAttachments = [];

    /** @var Collection<int, Vehicle> */
    public $vehicles;

    public function mount(?int $fuelId = null): void
    {
        $this->vehicles = Vehicle::query()->orderBy('institutional_number')->get();
        $this->fuelId = $fuelId;

        if ($this->fuelId !== null) {
            $record = FuelRecord::with('attachments')->findOrFail($this->fuelId);
            $this->vehicleId = (string) $record->vehicle_id;
            $this->date = $record->date->format('Y-m-d');
            $this->liters = (string) $record->liters;
            $this->pricePerLiter = (string) $record->price_per_liter;
            $this->totalCost = (float) $record->total_cost;
            $this->odometer = $record->odometer !== null ? (string) $record->odometer : '';
            $this->notes = $record->notes ?? '';

            $this->existingAttachments = $record->attachments->map(fn ($att) => [
                'id' => $att->id,
                'original_name' => $att->original_name,
                'file_path' => $att->file_path,
            ])->toArray();
        } else {
            $this->date = now()->format('Y-m-d');
        }
    }

    public function updatedLiters(): void
    {
        $this->recomputeTotalCost();
    }

    public function updatedPricePerLiter(): void
    {
        $this->recomputeTotalCost();
    }

    private function recomputeTotalCost(): void
    {
        $liters = (float) $this->liters;
        $price = (float) $this->pricePerLiter;
        $this->totalCost = ($liters > 0 && $price > 0) ? round($liters * $price, 2) : 0;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'vehicleId' => 'required|exists:vehicles,id',
            'date' => 'required|date',
            'liters' => 'required|numeric|min:0.01',
            'pricePerLiter' => 'required|numeric|min:0.01',
            'odometer' => 'nullable|integer|min:0',
            'notes' => 'nullable',
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
            'date.required' => 'تاريخ التزود بالوقود مطلوب.',
            'date.date' => 'تاريخ التزود بالوقود غير صحيح.',
            'liters.required' => 'كمية الوقود بالليتر مطلوبة.',
            'liters.numeric' => 'كمية الوقود يجب أن تكون رقماً.',
            'liters.min' => 'كمية الوقود يجب أن تكون أكبر من صفر.',
            'pricePerLiter.required' => 'سعر اللتر مطلوب.',
            'pricePerLiter.numeric' => 'سعر اللتر يجب أن يكون رقماً.',
            'pricePerLiter.min' => 'سعر اللتر يجب أن يكون أكبر من صفر.',
            'odometer.integer' => 'قراءة العداد يجب أن تكون رقماً صحيحاً.',
            'odometer.min' => 'قراءة العداد لا يمكن أن تكون سالبة.',
            'attachments.*.mimes' => 'المرفقات يجب أن تكون ملفات PDF أو صور (JPG, JPEG, PNG).',
            'attachments.*.max' => 'حجم المرفق يجب أن لا يتجاوز 5 ميغابايت.',
        ];
    }

    public function save(): void
    {
        $this->validate();

        $totalCost = round((float) $this->liters * (float) $this->pricePerLiter, 2);

        $data = [
            'vehicle_id' => (int) $this->vehicleId,
            'date' => $this->date,
            'liters' => (float) $this->liters,
            'price_per_liter' => (float) $this->pricePerLiter,
            'total_cost' => $totalCost,
            'odometer' => $this->odometer !== '' ? (int) $this->odometer : null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->fuelId !== null) {
            $record = FuelRecord::findOrFail($this->fuelId);
            $record->update($data);
        } else {
            $record = FuelRecord::create($data);
        }

        foreach ($this->attachments as $file) {
            $path = $file->store("attachments/fuel/{$record->id}");
            $record->attachments()->create([
                'file_path' => $path,
                'original_name' => $file->getClientOriginalName(),
            ]);
        }

        $this->dispatch('fuel-saved');
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
        return view('livewire.fuel.fuel-form');
    }
}
