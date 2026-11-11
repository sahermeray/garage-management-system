<?php

namespace App\Livewire\Drivers;

use App\Models\Driver;
use Illuminate\View\View;
use Livewire\Component;

class DriverForm extends Component
{
    public ?int $driverId = null;

    public string $name = '';

    public string $notes = '';

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:150',
            'notes' => 'nullable',
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'اسم السائق مطلوب.',
            'name.max' => 'اسم السائق يجب أن لا يتجاوز 150 حرفاً.',
        ];
    }

    public function mount(?int $driverId = null): void
    {
        $this->driverId = $driverId;

        if ($this->driverId !== null) {
            $driver = Driver::findOrFail($this->driverId);
            $this->name = $driver->name;
            $this->notes = $driver->notes ?? '';
        }
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'notes' => $this->notes ?: null,
        ];

        if ($this->driverId !== null) {
            $driver = Driver::findOrFail($this->driverId);
            $driver->update($data);
        } else {
            Driver::create($data);
        }

        $this->dispatch('driver-saved');
        $this->reset(['name', 'notes', 'driverId']);
    }

    public function render(): View
    {
        return view('livewire.drivers.driver-form');
    }
}
