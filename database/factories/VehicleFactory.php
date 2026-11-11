<?php

namespace Database\Factories;

use App\Models\Vehicle;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Vehicle>
 */
class VehicleFactory extends Factory
{
    public function definition(): array
    {
        $manufacturers = ['Toyota', 'Hyundai', 'Kia', 'Nissan', 'Mitsubishi', 'Ford', 'Chevrolet'];
        $models = ['Camry', 'Corolla', 'Land Cruiser', 'Tucson', 'Sportage', 'Patrol', 'Pajero', 'Explorer'];
        $colors = ['White', 'Black', 'Silver', 'Gray', 'Blue', 'Red'];

        return [
            'institutional_number' => 'VH-'.$this->faker->unique()->numerify('####'),
            'category' => $this->faker->randomElement(['administrative', 'passenger']),
            'manufacturer' => $this->faker->randomElement($manufacturers),
            'model' => $this->faker->randomElement($models),
            'year' => $this->faker->numberBetween(2010, 2024),
            'plate_number' => $this->faker->unique()->bothify('??-###-??'),
            'engine_number' => $this->faker->optional()->bothify('ENG-########'),
            'chassis_number' => $this->faker->optional()->bothify('CHS-#################'),
            'color' => $this->faker->randomElement($colors),
            'current_odometer' => $this->faker->numberBetween(0, 200000),
            'status' => $this->faker->randomElement(['active', 'under_repair', 'out_of_service', 'retired']),
            'notes' => $this->faker->optional()->sentence(),
            'image_path' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(['status' => 'active']);
    }

    public function underRepair(): static
    {
        return $this->state(['status' => 'under_repair']);
    }
}
