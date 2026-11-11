<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use Illuminate\View\View;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

class VehicleForm extends Component
{
    use WithFileUploads;

    public ?int $vehicleId = null;

    public string $institutionalNumber = '';

    public string $category = 'administrative';

    public string $manufacturer = '';

    public string $model = '';

    public string $year = '';

    public string $plateNumber = '';

    public string $engineNumber = '';

    public string $chassisNumber = '';

    public string $color = '';

    public string $currentOdometer = '0';

    public string $status = 'active';

    public string $notes = '';

    /** @var TemporaryUploadedFile|null */
    public $photo = null;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'institutionalNumber' => [
                'required',
                'max:50',
                'unique:vehicles,institutional_number'.($this->vehicleId ? ','.$this->vehicleId : ''),
            ],
            'category' => 'required|in:administrative,passenger',
            'manufacturer' => 'required|max:100',
            'model' => 'required|max:100',
            'year' => [
                'required',
                'integer',
                'min:1980',
                'max:'.(now()->year + 1),
            ],
            'plateNumber' => [
                'required',
                'max:50',
                'unique:vehicles,plate_number'.($this->vehicleId ? ','.$this->vehicleId : ''),
            ],
            'engineNumber' => 'nullable|max:100',
            'chassisNumber' => 'nullable|max:100',
            'color' => 'nullable|max:50',
            'currentOdometer' => 'required|integer|min:0',
            'status' => 'required|in:active,under_repair,out_of_service,retired',
            'notes' => 'nullable',
            'photo' => 'nullable|image|max:5120',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'institutionalNumber.required' => 'الرقم المؤسسي مطلوب.',
            'institutionalNumber.max' => 'الرقم المؤسسي يجب أن لا يتجاوز 50 حرفاً.',
            'institutionalNumber.unique' => 'الرقم المؤسسي مستخدم مسبقاً.',
            'category.required' => 'الفئة مطلوبة.',
            'category.in' => 'الفئة المختارة غير صحيحة.',
            'manufacturer.required' => 'الصانع مطلوب.',
            'manufacturer.max' => 'الصانع يجب أن لا يتجاوز 100 حرف.',
            'model.required' => 'الموديل مطلوب.',
            'model.max' => 'الموديل يجب أن لا يتجاوز 100 حرف.',
            'year.required' => 'سنة الصنع مطلوبة.',
            'year.integer' => 'سنة الصنع يجب أن تكون رقماً صحيحاً.',
            'year.min' => 'سنة الصنع لا يمكن أن تكون قبل 1980.',
            'year.max' => 'سنة الصنع لا يمكن أن تتجاوز السنة القادمة.',
            'plateNumber.required' => 'رقم اللوحة مطلوب.',
            'plateNumber.max' => 'رقم اللوحة يجب أن لا يتجاوز 50 حرفاً.',
            'plateNumber.unique' => 'رقم اللوحة مستخدم مسبقاً.',
            'engineNumber.max' => 'رقم المحرك يجب أن لا يتجاوز 100 حرف.',
            'chassisNumber.max' => 'رقم الهيكل يجب أن لا يتجاوز 100 حرف.',
            'color.max' => 'اللون يجب أن لا يتجاوز 50 حرفاً.',
            'currentOdometer.required' => 'قراءة العداد الحالية مطلوبة.',
            'currentOdometer.integer' => 'قراءة العداد يجب أن تكون رقماً صحيحاً.',
            'currentOdometer.min' => 'قراءة العداد لا يمكن أن تكون سالبة.',
            'status.required' => 'الحالة مطلوبة.',
            'status.in' => 'الحالة المختارة غير صحيحة.',
            'photo.image' => 'يجب أن يكون الملف صورة.',
            'photo.max' => 'حجم الصورة يجب أن لا يتجاوز 5 ميغابايت.',
        ];
    }

    public function mount(?int $vehicleId = null): void
    {
        $this->vehicleId = $vehicleId;

        if ($this->vehicleId !== null) {
            $vehicle = Vehicle::findOrFail($this->vehicleId);
            $this->institutionalNumber = $vehicle->institutional_number;
            $this->category = $vehicle->category;
            $this->manufacturer = $vehicle->manufacturer;
            $this->model = $vehicle->model;
            $this->year = (string) $vehicle->year;
            $this->plateNumber = $vehicle->plate_number;
            $this->engineNumber = $vehicle->engine_number ?? '';
            $this->chassisNumber = $vehicle->chassis_number ?? '';
            $this->color = $vehicle->color ?? '';
            $this->currentOdometer = (string) $vehicle->current_odometer;
            $this->status = $vehicle->status;
            $this->notes = $vehicle->notes ?? '';
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'institutional_number' => $this->institutionalNumber,
            'category' => $this->category,
            'manufacturer' => $this->manufacturer,
            'model' => $this->model,
            'year' => (int) $this->year,
            'plate_number' => $this->plateNumber,
            'engine_number' => $this->engineNumber ?: null,
            'chassis_number' => $this->chassisNumber ?: null,
            'color' => $this->color ?: null,
            'current_odometer' => (int) $this->currentOdometer,
            'status' => $this->status,
            'notes' => $this->notes ?: null,
        ];

        if ($this->photo !== null) {
            $data['image_path'] = $this->photo->store('vehicles', 'public');
        }

        if ($this->vehicleId !== null) {
            $vehicle = Vehicle::findOrFail($this->vehicleId);
            $vehicle->update($data);
        } else {
            Vehicle::create($data);
        }

        $this->dispatch('vehicle-saved');
        $this->reset(['institutionalNumber', 'category', 'manufacturer', 'model', 'year', 'plateNumber', 'engineNumber', 'chassisNumber', 'color', 'currentOdometer', 'status', 'notes', 'photo', 'vehicleId']);
        $this->category = 'administrative';
        $this->currentOdometer = '0';
        $this->status = 'active';
    }

    public function render(): View
    {
        return view('livewire.vehicles.vehicle-form');
    }
}
