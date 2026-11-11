<?php

namespace App\Livewire\Trips;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\View\View;
use Livewire\Component;

class TripForm extends Component
{
    public ?int $tripId = null;

    public string $tripNumber = '';

    public string $tripDate = '';

    public string $vehicleId = '';

    public string $driverId = '';

    public string $destination = '';

    public string $purpose = '';

    public string $departureTime = '';

    public string $returnTime = '';

    public string $odometerStart = '0';

    public string $odometerEnd = '';

    public string $notes = '';

    public ?int $computedDistance = null;

    /** @var Collection<int, Vehicle> */
    public $vehicles;

    /** @var Collection<int, Driver> */
    public $drivers;

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tripDate' => 'required|date',
            'vehicleId' => 'required|exists:vehicles,id',
            'driverId' => 'required|exists:drivers,id',
            'destination' => 'required|max:200',
            'purpose' => 'required',
            'departureTime' => 'nullable|date_format:H:i',
            'returnTime' => [
                'nullable',
                'date_format:H:i',
                function ($attribute, $value, $fail) {
                    if ($value !== '' && $this->departureTime !== '' && $value <= $this->departureTime) {
                        $fail('وقت العودة يجب أن يكون بعد وقت المغادرة.');
                    }
                },
            ],
            'odometerStart' => 'required|integer|min:0',
            'odometerEnd' => [
                'nullable',
                'integer',
                function ($attribute, $value, $fail) {
                    if ($value !== null && $value !== '' && (int) $value <= (int) $this->odometerStart) {
                        $fail('قراءة العداد عند العودة يجب أن تكون أكبر من قراءة العداد عند المغادرة.');
                    }
                },
            ],
            'notes' => 'nullable',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'tripDate.required' => 'تاريخ الرحلة مطلوب.',
            'tripDate.date' => 'تاريخ الرحلة غير صحيح.',
            'vehicleId.required' => 'المركبة مطلوبة.',
            'vehicleId.exists' => 'المركبة المختارة غير موجودة.',
            'driverId.required' => 'السائق مطلوب.',
            'driverId.exists' => 'السائق المختار غير موجود.',
            'destination.required' => 'الوجهة مطلوبة.',
            'destination.max' => 'الوجهة يجب أن لا تتجاوز 200 حرف.',
            'purpose.required' => 'الغرض من الرحلة مطلوب.',
            'departureTime.date_format' => 'صيغة وقت المغادرة غير صحيحة (HH:MM).',
            'returnTime.date_format' => 'صيغة وقت العودة غير صحيحة (HH:MM).',
            'odometerStart.required' => 'قراءة العداد عند المغادرة مطلوبة.',
            'odometerStart.integer' => 'قراءة العداد يجب أن تكون رقماً صحيحاً.',
            'odometerStart.min' => 'قراءة العداد لا يمكن أن تكون سالبة.',
            'odometerEnd.integer' => 'قراءة العداد عند العودة يجب أن تكون رقماً صحيحاً.',
        ];
    }

    public function mount(?int $tripId = null): void
    {
        $this->vehicles = Vehicle::query()->orderBy('institutional_number')->get();
        $this->drivers = Driver::query()->orderBy('name')->get();

        $this->tripId = $tripId;

        if ($this->tripId !== null) {
            $trip = Trip::findOrFail($this->tripId);
            $this->tripNumber = $trip->trip_number;
            $this->tripDate = $trip->trip_date->format('Y-m-d');
            $this->vehicleId = (string) $trip->vehicle_id;
            $this->driverId = (string) $trip->driver_id;
            $this->destination = $trip->destination;
            $this->purpose = $trip->purpose;
            $this->departureTime = $trip->departure_time ?? '';
            $this->returnTime = $trip->return_time ?? '';
            $this->odometerStart = (string) $trip->odometer_start;
            $this->odometerEnd = $trip->odometer_end !== null ? (string) $trip->odometer_end : '';
            $this->notes = $trip->notes ?? '';
            $this->computeDistance();
        } else {
            $this->tripNumber = Trip::generateTripNumber();
            $this->tripDate = now()->format('Y-m-d');
        }
    }

    public function updatedOdometerEnd(): void
    {
        $this->computeDistance();
    }

    public function updatedOdometerStart(): void
    {
        $this->computeDistance();
    }

    private function computeDistance(): void
    {
        if ($this->odometerEnd !== '' && $this->odometerEnd !== null
            && $this->odometerStart !== '' && $this->odometerStart !== null
            && (int) $this->odometerEnd > (int) $this->odometerStart) {
            $this->computedDistance = (int) $this->odometerEnd - (int) $this->odometerStart;
        } else {
            $this->computedDistance = null;
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'vehicle_id' => (int) $this->vehicleId,
            'driver_id' => (int) $this->driverId,
            'trip_date' => $this->tripDate,
            'destination' => $this->destination,
            'purpose' => $this->purpose,
            'departure_time' => $this->departureTime ?: null,
            'return_time' => $this->returnTime ?: null,
            'odometer_start' => (int) $this->odometerStart,
            'odometer_end' => $this->odometerEnd !== '' ? (int) $this->odometerEnd : null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->tripId !== null) {
            $trip = Trip::findOrFail($this->tripId);
            $trip->update($data);
        } else {
            $data['trip_number'] = Trip::generateTripNumber();
            Trip::create($data);
        }

        $this->dispatch('trip-saved');
    }

    public function render(): View
    {
        return view('livewire.trips.trip-form');
    }
}
