<?php

namespace Database\Factories;

use App\Models\FuelRecord;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FuelRecord>
 */
class FuelRecordFactory extends Factory
{
    public function definition(): array
    {
        $liters = $this->faker->randomFloat(2, 10, 80);
        $pricePerLiter = $this->faker->randomFloat(2, 500, 2000);

        return [
            'vehicle_id' => Vehicle::factory(),
            'date' => $this->faker->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'liters' => $liters,
            'price_per_liter' => $pricePerLiter,
            'total_cost' => round($liters * $pricePerLiter, 2),
            'odometer' => $this->faker->optional()->numberBetween(10000, 200000),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
