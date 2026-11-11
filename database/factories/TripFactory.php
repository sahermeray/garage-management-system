<?php

namespace Database\Factories;

use App\Models\Driver;
use App\Models\Trip;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Trip>
 */
class TripFactory extends Factory
{
    public function definition(): array
    {
        $year = now()->year;
        $odometerStart = $this->faker->numberBetween(10000, 150000);
        $odometerEnd = $this->faker->optional(0.8)->numberBetween($odometerStart + 10, $odometerStart + 500);

        return [
            'trip_number' => 'TRIP-'.$year.'-'.str_pad((string) $this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'vehicle_id' => Vehicle::factory(),
            'driver_id' => Driver::factory(),
            'trip_date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'destination' => $this->faker->city(),
            'purpose' => $this->faker->sentence(),
            'departure_time' => $this->faker->optional()->time('H:i:s'),
            'return_time' => $this->faker->optional()->time('H:i:s'),
            'odometer_start' => $odometerStart,
            'odometer_end' => $odometerEnd,
            'distance' => $odometerEnd !== null ? $odometerEnd - $odometerStart : null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
