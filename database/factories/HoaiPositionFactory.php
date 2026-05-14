<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\HoaiZone;
use App\Models\HoaiPosition;
use App\Models\OfferGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<HoaiPosition>
 */
class HoaiPositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'offer_group_id' => OfferGroup::factory(),
            'title' => fake()->words(3, true).' Fee',
            'costs' => fake()->numerify('#####').'.00',
            'zone' => HoaiZone::II->value,
            'rate' => 'middle',
            'phases' => [1, 2],
            'construction_markup' => '0',
            'additional_costs' => '0',
            'vat' => '19',
            'total' => '0.00',
        ];
    }
}