<?php

namespace App\Livewire\Drivers;

use App\Models\Driver;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('السائقون')]

class DriverList extends Component
{
    use WithPagination;

    public string $search = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingDriverId = null;

    public string $deleteError = '';

    /** @var array<string, string> */
    protected $listeners = ['driver-saved' => 'driverSaved'];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function createDriver(): void
    {
        $this->showCreateModal = true;
        $this->showEditModal = false;
        $this->editingDriverId = null;
    }

    public function editDriver(int $id): void
    {
        $this->editingDriverId = $id;
        $this->showEditModal = true;
        $this->showCreateModal = false;
        $this->deleteError = '';
    }

    public function deleteDriver(int $id): void
    {
        $driver = Driver::findOrFail($id);

        if (! $driver->canBeDeleted()) {
            $this->deleteError = 'لا يمكن حذف السائق لأن لديه رحلات مسجلة';

            return;
        }

        $driver->delete();
        $this->deleteError = '';
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->editingDriverId = null;
        $this->deleteError = '';
    }

    public function driverSaved(): void
    {
        $this->closeModal();
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<Driver>
     */
    public function getDriversProperty(): LengthAwarePaginator
    {
        return Driver::query()
            ->when($this->search, fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->withCount('trips')
            ->orderBy('name')
            ->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.drivers.driver-list', [
            'drivers' => $this->drivers,
        ]);
    }
}
