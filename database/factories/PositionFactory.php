<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\OfferGroup;
use App\Models\Position;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->randomFloat(2, 1, 5);
        $unitPrice = fake()->randomFloat(2, 100, 800);
        $total = bcmul((string) $quantity, (string) $unitPrice, 2);

        return [
            'offer_group_id' => OfferGroup::factory(),
            'title' => fake()->words(2, true),
            'quantity' => bcadd((string) $quantity, '0', 2),
            'unit_price' => bcadd((string) $unitPrice, '0', 2),
            'total' => $total,
        ];
    }
}