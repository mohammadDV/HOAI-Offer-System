<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default zone key
    |--------------------------------------------------------------------------
    |
    | Used when a position omits a zone value or when resolving lookups.
    | Stored values use Roman numerals (I–V); lookups normalize to lowercase.
    |
    */
    'default_zone' => 'II',

    /*
    |--------------------------------------------------------------------------
    | Zone / rate multipliers
    |--------------------------------------------------------------------------
    |
    | Each zone defines simplified multipliers for minimum, middle, and
    | maximum rate tiers. Keys are lowercase Roman numerals (i–v). Values are
    | decimal strings applied as: base_fee = costs × multiplier
    |
    | @var array<string, array<string, string>>
    */
    'zones' => [
        'i' => [
            'minimum' => '0.78',
            'middle' => '0.92',
            'maximum' => '1.02',
        ],
        'ii' => [
            'minimum' => '0.85',
            'middle' => '1.00',
            'maximum' => '1.15',
        ],
        'iii' => [
            'minimum' => '0.87',
            'middle' => '1.05',
            'maximum' => '1.18',
        ],
        'iv' => [
            'minimum' => '0.90',
            'middle' => '1.10',
            'maximum' => '1.25',
        ],
        'v' => [
            'minimum' => '0.95',
            'middle' => '1.15',
            'maximum' => '1.35',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Phase percentages (simplified, not legally binding)
    |--------------------------------------------------------------------------
    |
    | Percentage points for HOAI-style phases 1–9. Selected phases on a
    | position are summed before applying to the base fee.
    |
    | @var array<int, string>
    */
    'phases' => [
        1 => '2.0',
        2 => '7.0',
        3 => '8.0',
        4 => '10.0',
        5 => '12.0',
        6 => '14.0',
        7 => '16.0',
        8 => '15.0',
        9 => '16.0',
    ],

];
