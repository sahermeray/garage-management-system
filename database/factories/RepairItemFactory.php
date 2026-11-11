<?php

namespace Database\Factories;

use App\Models\Repair;
use App\Models\RepairItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RepairItem>
 */
class RepairItemFactory extends Factory
{
    public function definition(): array
    {
        $quantity = $this->faker->randomFloat(2, 1, 10);
        $unitPrice = $this->faker->randomFloat(2, 10, 1000);

        return [
            'repair_id' => Repair::factory(),
            'description' => $this->faker->words(3, true),
            'type' => $this->faker->randomElement(['spare_part', 'labor', 'service', 'other']),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => round($quantity * $unitPrice, 2),
        ];
    }
}
