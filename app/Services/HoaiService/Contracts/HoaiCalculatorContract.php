<?php

declare(strict_types=1);

namespace App\Services\HoaiService\Contracts;

use App\Models\HoaiPosition;

interface HoaiCalculatorContract
{
    /**
     * Calculate HOAI-style totals for a position
     *
     * @param HoaiPosition $position
     * @return array
     */
    public function calculate(HoaiPosition $position): array;
}