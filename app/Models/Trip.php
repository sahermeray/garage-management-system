<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Trip extends Model
{
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'trip_number',
        'vehicle_id',
        'driver_id',
        'trip_date',
        'destination',
        'purpose',
        'departure_time',
        'return_time',
        'odometer_start',
        'odometer_end',
        'distance',
        'notes',
    ];

    /** @var array<string, string> */
    protected $casts = [
        'trip_date' => 'date',
        'departure_time' => 'string',
        'return_time' => 'string',
        'odometer_start' => 'integer',
        'odometer_end' => 'integer',
        'distance' => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::saving(function (Trip $trip): void {
            if ($trip->odometer_end !== null) {
                $trip->distance = $trip->odometer_end - $trip->odometer_start;

                $vehicle = $trip->vehicle;
                if ($vehicle && $trip->odometer_end > $vehicle->current_odometer) {
                    $vehicle->current_odometer = $trip->odometer_end;
                    $vehicle->save();
                }
            }
        });
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    /**
     * Generate the next trip number for the current year in format TRIP-YYYY-####.
     */
    public static function generateTripNumber(): string
    {
        $year = now()->year;
        $prefix = "TRIP-{$year}-";

        $lastTrip = static::query()
            ->whereYear('created_at', $year)
            ->where('trip_number', 'like', "{$prefix}%")
            ->orderByDesc('trip_number')
            ->value('trip_number');

        $nextNumber = 1;
        if ($lastTrip !== null) {
            $parts = explode('-', $lastTrip);
            $lastSequence = (int) end($parts);
            $nextNumber = $lastSequence + 1;
        }

        return $prefix.str_pad((string) $nextNumber, 4, '0', STR_PAD_LEFT);
    }
}
