<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Offer;
use App\Models\OfferGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OfferGroup>
 */
class OfferGroupFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'offer_id' => Offer::factory(),
            'title' => fake()->words(3, true),
            'sort_order' => fake()->numberBetween(0, 5),
        ];
    }
}