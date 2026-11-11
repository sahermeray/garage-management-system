<?php

namespace Database\Factories;

use App\Models\Repair;
use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Repair>
 */
class RepairFactory extends Factory
{
    public function definition(): array
    {
        $location = $this->faker->randomElement(['institution', 'external']);

        return [
            'vehicle_id' => Vehicle::factory(),
            'repair_date' => $this->faker->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'odometer' => $this->faker->optional()->numberBetween(10000, 200000),
            'problem' => $this->faker->sentence(),
            'description' => $this->faker->paragraph(),
            'location' => $location,
            'workshop_name' => $location === 'external' ? $this->faker->company() : null,
            'invoice_number' => $this->faker->optional()->bothify('INV-######'),
            'notes' => $this->faker->optional()->sentence(),
            'total_cost' => $this->faker->randomFloat(2, 50, 5000),
        ];
    }
}
