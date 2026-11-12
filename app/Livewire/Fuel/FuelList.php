<?php

namespace App\Livewire\Fuel;

use App\Models\FuelRecord;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('سجلات الوقود')]
class FuelList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $vehicleFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingFuelId = null;

    /** @var array<string, string> */
    protected $listeners = [
        'fuel-saved' => 'fuelSaved',
        'open-create-fuel' => 'createFuel',
        'close-modal' => 'closeModal',
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingVehicleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingDateFrom(): void
    {
        $this->resetPage();
    }

    public function updatingDateTo(): void
    {
        $this->resetPage();
    }

    public function createFuel(): void
    {
        $this->showCreateModal = true;
        $this->showEditModal = false;
        $this->editingFuelId = null;
    }

    public function editFuel(int $id): void
    {
        $this->editingFuelId = $id;
        $this->showEditModal = true;
        $this->showCreateModal = false;
    }

    public function deleteFuel(int $id): void
    {
        FuelRecord::findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->editingFuelId = null;
    }

    public function fuelSaved(): void
    {
        $this->closeModal();
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<FuelRecord>
     */
    public function getFuelRecordsProperty(): LengthAwarePaginator
    {
        return FuelRecord::query()
            ->with(['vehicle'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('notes', 'like', '%'.$this->search.'%')
                        ->orWhereHas('vehicle', fn ($vq) => $vq->where('institutional_number', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->vehicleFilter, fn ($query) => $query->where('vehicle_id', $this->vehicleFilter))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('date', '<=', $this->dateTo))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(15);
    }

    /**
     * @return Collection<int, Vehicle>
     */
    public function getVehiclesProperty(): Collection
    {
        return Vehicle::query()->orderBy('institutional_number')->get();
    }

    public function render(): View
    {
        return view('livewire.fuel.fuel-list', [
            'fuelRecords' => $this->fuelRecords,
            'vehicles' => $this->vehicles,
        ]);
    }
}
