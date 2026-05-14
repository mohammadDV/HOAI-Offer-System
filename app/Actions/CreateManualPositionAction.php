<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\OfferGroup;
use App\Models\Position;

final class CreateManualPositionAction
{
    /**
     * Create a manual line: normalize decimals and set total = quantity × unit price.
     *
     * @param  array{title: string, quantity: mixed, unit_price: mixed}  $attributes
     */
    public function execute(OfferGroup $group, array $attributes): Position
    {
        $qty = (string) $attributes['quantity'];
        $unit = (string) $attributes['unit_price'];
        $total = bcmul(bcadd($qty, '0', 4), bcadd($unit, '0', 4), 2);

        return Position::query()->create([
            'offer_group_id' => $group->id,
            'title' => $attributes['title'],
            'quantity' => bcadd($qty, '0', 2),
            'unit_price' => bcadd($unit, '0', 2),
            'total' => $total,
        ]);
    }
}
