<?php

declare(strict_types=1);

namespace App\Enums;

enum HoaiRate: string
{
    case Minimum = 'minimum';
    case Middle = 'middle';
    case Maximum = 'maximum';

    /**
     * Human-readable label for selects
     */
    public function label(): string
    {
        return match ($this) {
            self::Minimum => 'Minimum',
            self::Middle => 'Middle',
            self::Maximum => 'Maximum',
        };
    }
}
