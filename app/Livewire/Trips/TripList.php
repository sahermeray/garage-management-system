<?php

namespace App\Livewire\Trips;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.app')]
#[Title('الرحلات والمهام')]

class TripList extends Component
{
    use WithPagination;

    public string $search = '';

    public string $vehicleFilter = '';

    public string $driverFilter = '';

    public string $dateFrom = '';

    public string $dateTo = '';

    public bool $showCreateModal = false;

    public bool $showEditModal = false;

    public ?int $editingTripId = null;

    public string $deleteError = '';

    /** @var array<string, string> */
    protected $listeners = [
        'trip-saved' => 'tripSaved',
        'open-create-trip' => 'createTrip',
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

    public function updatingDriverFilter(): void
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

    public function createTrip(): void
    {
        $this->showCreateModal = true;
        $this->showEditModal = false;
        $this->editingTripId = null;
    }

    public function editTrip(int $id): void
    {
        $this->editingTripId = $id;
        $this->showEditModal = true;
        $this->showCreateModal = false;
        $this->deleteError = '';
    }

    public function deleteTrip(int $id): void
    {
        Trip::findOrFail($id)->delete();
        $this->deleteError = '';
    }

    public function closeModal(): void
    {
        $this->showCreateModal = false;
        $this->showEditModal = false;
        $this->editingTripId = null;
        $this->deleteError = '';
    }

    public function tripSaved(): void
    {
        $this->closeModal();
        $this->resetPage();
    }

    /**
     * @return LengthAwarePaginator<Trip>
     */
    public function getTripsProperty(): LengthAwarePaginator
    {
        return Trip::query()
            ->with(['vehicle', 'driver'])
            ->when($this->search, function ($query) {
                $query->where(function ($q) {
                    $q->where('trip_number', 'like', '%'.$this->search.'%')
                        ->orWhere('destination', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->vehicleFilter, fn ($query) => $query->where('vehicle_id', $this->vehicleFilter))
            ->when($this->driverFilter, fn ($query) => $query->where('driver_id', $this->driverFilter))
            ->when($this->dateFrom, fn ($query) => $query->whereDate('trip_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn ($query) => $query->whereDate('trip_date', '<=', $this->dateTo))
            ->orderByDesc('trip_date')
            ->orderByDesc('id')
            ->paginate(15);
    }

    /**
     * @return Collection<int, Vehicle>
     */
    public function getVehiclesProperty()
    {
        return Vehicle::query()->orderBy('institutional_number')->get();
    }

    /**
     * @return Collection<int, Driver>
     */
    public function getDriversProperty()
    {
        return Driver::query()->orderBy('name')->get();
    }

    public function render(): View
    {
        return view('livewire.trips.trip-list', [
            'trips' => $this->trips,
            'vehicles' => $this->vehicles,
            'drivers' => $this->drivers,
        ]);
    }
}
