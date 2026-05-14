<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\HoaiPosition;
use App\Models\OfferGroup;
use App\Services\HoaiService\Contracts\HoaiCalculatorContract;

final class CreateHoaiPositionAction
{
    public function __construct(
        private HoaiCalculatorContract $calculator,
    ) {}

    /**
     * Persist a new HOAI line for the group: normalize amounts, run calculator, save total.
     *
     * @param OfferGroup $group
     * @param  array $attributes
     */
    public function execute(OfferGroup $group, array $attributes): HoaiPosition
    {
        $phases = array_values(array_map('intval', (array) $attributes['phases']));

        $hoai = new HoaiPosition([
            'offer_group_id' => $group->id,
            'title' => $attributes['title'],
            'costs' => bcadd((string) $attributes['costs'], '0', 2),
            'zone' => $attributes['zone'],
            'rate' => $attributes['rate'],
            'phases' => $phases,
            'construction_markup' => bcadd((string) $attributes['construction_markup'], '0', 2),
            'additional_costs' => bcadd((string) $attributes['additional_costs'], '0', 2),
            'vat' => bcadd((string) $attributes['vat'], '0', 2),
            'total' => '0.00',
        ]);

        $total = $this->calculator->calculate($hoai)['total'];
        $hoai->total = $total;
        $hoai->save();

        return $hoai;
    }
}