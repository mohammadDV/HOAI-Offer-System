<?php

declare(strict_types=1);

namespace App\Enums;

enum HoaiZone: string
{
    case I = 'I';
    case II = 'II';
    case III = 'III';
    case IV = 'IV';
    case V = 'V';

    /**
     * Human-readable label for selects
     */
    public function label(): string
    {
        return match ($this) {
            self::I => 'Simple (I)',
            self::II => 'Normal (II)',
            self::III => 'Medium (III)',
            self::IV => 'Complex (IV)',
            self::V => 'Very Complex (V)',
        };
    }
}
