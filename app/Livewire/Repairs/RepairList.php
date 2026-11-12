<?php

namespace App\Livewire\Repairs;

use App\Models\Repair;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('الإصلاحات والصيانة')]
class RepairList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $vehicleFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingRepairId = null;

    /** @var array<string, string> */
    protected $listeners = [
        'repair-saved' => 'repairSaved',
        'open-create-repair' => 'createRepair',
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

    public function createRepair(): void
    {
        $this->showCreateModal = true;
        $this->showEditModal = false;
        $this->editingRepairId = null;
    }

    public function editRepair(int $id): void
    {
        $this->editingRepairId = $id;
        $this->showEditModal = true;
        $this->showCreateModal = false;
    }

    public function deleteRepair(int $id): void
    {
        Repair::findOrFail($id)->delete();
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->editingRepairId = null;
    }

    public function repairSaved(): void
    {
        $this->closeModal();
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<Repair>
     */
    public function getRepairsProperty(): LengthAwarePaginator
    {
        return Repair::query()
            ->with(['vehicle'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('problem', 'like', '%'.$this->search.'%')
                        ->orWhere('invoice_number', 'like', '%'.$this->search.'%')
                        ->orWhereHas('vehicle', fn ($vq) => $vq->where('institutional_number', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->vehicleFilter, fn ($query) => $query->where('vehicle_id', $this->vehicleFilter))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('repair_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('repair_date', '<=', $this->dateTo))
            ->orderByDesc('repair_date')
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
        return view('livewire.repairs.repair-list', [
            'repairs' => $this->repairs,
            'vehicles' => $this->vehicles,
        ]);
    }
}
