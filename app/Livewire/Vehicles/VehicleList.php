<?php

namespace App\Livewire\Vehicles;

use App\Models\Vehicle;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('المركبات')]

class VehicleList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = '';

    public string $categoryFilter = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingVehicleId = null;

    public string $deleteError = '';

    /** @var array<string, string> */
    protected $listeners = [
        'vehicle-saved' => 'vehicleSaved',
        'open-create-vehicle' => 'createVehicle',
        'close-modal' => 'closeModal',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatusFilter(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function createVehicle(): void
    {
        $this->showCreateModal = true;
        $this->showEditModal = false;
        $this->editingVehicleId = null;
    }

    public function editVehicle(int $id): void
    {
        $this->editingVehicleId = $id;
        $this->showEditModal = true;
        $this->showCreateModal = false;
        $this->deleteError = '';
    }

    public function deleteVehicle(int $id): void
    {
        $vehicle = Vehicle::findOrFail($id);

        if (! $vehicle->canBeDeleted()) {
            $this->deleteError = 'لا يمكن حذف المركبة لأن لديها سجلات مرتبطة بها';

            return;
        }

        $vehicle->delete();
        $this->deleteError = '';
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->editingVehicleId = null;
        $this->deleteError = '';
    }

    public function vehicleSaved(): void
    {
        $this->closeModal();
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<Vehicle>
     */
    public function getVehiclesProperty(): LengthAwarePaginator
    {
        return Vehicle::query()
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('institutional_number', 'like', '%'.$this->search.'%')
                        ->orWhere('plate_number', 'like', '%'.$this->search.'%')
                        ->orWhere('manufacturer', 'like', '%'.$this->search.'%')
                        ->orWhere('model', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->categoryFilter, fn ($query) => $query->where('category', $this->categoryFilter))
            ->orderBy('institutional_number')
            ->paginate(15);
    }

    public function render(): View
    {
        return view('livewire.vehicles.vehicle-list', [
            'vehicles' => $this->vehicles,
        ]);
    }
}
