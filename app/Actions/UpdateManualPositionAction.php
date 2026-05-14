<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\Position;

final class UpdateManualPositionAction
{
    /**
     * Update a manual line: normalize decimals and set total = quantity × unit price.
     *
     * @param  array{title: string, quantity: mixed, unit_price: mixed}  $attributes
     */
    public function execute(Position $position, array $attributes): Position
    {
        $qty = (string) $attributes['quantity'];
        $unit = (string) $attributes['unit_price'];
        $total = bcmul(bcadd($qty, '0', 4), bcadd($unit, '0', 4), 2);

        $position->update([
            'title' => $attributes['title'],
            'quantity' => bcadd($qty, '0', 2),
            'unit_price' => bcadd($unit, '0', 2),
            'total' => $total,
        ]);

        return $position->refresh();
    }
}
