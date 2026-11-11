<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use HasFactory, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'institutional_number',
        'category',
        'manufacturer',
        'model',
        'year',
        'plate_number',
        'engine_number',
        'chassis_number',
        'color',
        'current_odometer',
        'status',
        'notes',
        'image_path',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'category' => 'string',
        'status' => 'string',
        'current_odometer' => 'integer',
        'year' => 'integer',
    ];

    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function repairs(): HasMany
    {
        return $this->hasMany(Repair::class);
    }

    public function fuelRecords(): HasMany
    {
        return $this->hasMany(FuelRecord::class);
    }

    /**
     * Scope for active vehicles.
     *
     * @param  Builder<Vehicle>  $query
     * @return Builder<Vehicle>
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for vehicles under repair.
     *
     * @param  Builder<Vehicle>  $query
     * @return Builder<Vehicle>
     */
    public function scopeUnderRepair($query)
    {
        return $query->where('status', 'under_repair');
    }

    /**
     * Scope for out of service vehicles.
     *
     * @param  Builder<Vehicle>  $query
     * @return Builder<Vehicle>
     */
    public function scopeOutOfService($query)
    {
        return $query->where('status', 'out_of_service');
    }

    /**
     * Returns false if the vehicle has any associated trips, repairs, or fuel records.
     */
    public function canBeDeleted(): bool
    {
        return ! $this->trips()->exists()
            && ! $this->repairs()->exists()
            && ! $this->fuelRecords()->exists();
    }
}
