<?php

declare(strict_types=1);

use App\Models\HoaiPosition;
use App\Services\HoaiService\Contracts\HoaiCalculatorContract;
use App\Services\HoaiService\HoaiCalculatorService;

/**
 * @return array{default_zone: string, zones: array}
 */
function hoaiCalculatorTestConfig(): array
{
    return [
        'default_zone' => 'II',
        'zones' => [
            'ii' => [
                'minimum' => '0.80',
                'middle' => '1.00',
                'maximum' => '1.20',
            ],
            'iv' => [
                'minimum' => '1.00',
                'middle' => '2.00',
                'maximum' => '3.00',
            ],
        ],
        'phases' => [
            1 => '10.0',
            2 => '5.0',
        ],
    ];
}

/**
 * @param  array  $overrides
 */
function hoaiCalculator(array $overrides = []): HoaiCalculatorContract
{
    return new HoaiCalculatorService([...hoaiCalculatorTestConfig(), ...$overrides]);
}

/**
 * @param  array  $attributes
 */
function hoaiPosition(array $attributes = []): HoaiPosition
{
    return HoaiPosition::make(array_merge([
        'costs' => '1000.00',
        'zone' => 'II',
        'rate' => 'middle',
        'phases' => [1],
        'construction_markup' => '0',
        'additional_costs' => '0',
        'vat' => '0',
    ], $attributes));
}

test('calculates base fee from costs and zone middle rate', function () {
    $calculator = hoaiCalculator();
    $position = hoaiPosition(['costs' => '500.00']);

    $result = $calculator->calculate($position);

    expect($result['base_fee'])->toBe('500.00');
});

test('calculates phase fee from summed phase percentages of base fee', function () {
    $calculator = hoaiCalculator();
    $position = hoaiPosition([
        'costs' => '1000.00',
        'phases' => [1, 2],
    ]);

    $result = $calculator->calculate($position);

    expect($result['phase_percentage'])->toBe('15.00');
    expect($result['phase_fee'])->toBe('150.00');
});

test('applies construction markup additional costs and vat in order', function () {
    $calculator = hoaiCalculator();
    $position = hoaiPosition([
        'costs' => '1000.00',
        'phases' => [1],
        'construction_markup' => '10',
        'additional_costs' => '10',
        'vat' => '20',
    ]);

    $result = $calculator->calculate($position);

    expect($result['base_fee'])->toBe('1000.00');
    expect($result['phase_fee'])->toBe('100.00');
    expect($result['construction_markup_amount'])->toBe('110.00');
    expect($result['additional_costs_amount'])->toBe('121.00');
    expect($result['subtotal'])->toBe('1331.00');
    expect($result['vat_amount'])->toBe('266.20');
    expect($result['total'])->toBe('1597.20');
});

test('resolves uppercase roman zone to config key', function () {
    $calculator = hoaiCalculator();
    $position = hoaiPosition([
        'costs' => '100.00',
        'zone' => 'IV',
        'rate' => 'middle',
        'phases' => [],
    ]);

    $result = $calculator->calculate($position);

    expect($result['base_fee'])->toBe('200.00');
});

test('uses default zone when zone is empty', function () {
    $calculator = hoaiCalculator();
    $position = hoaiPosition([
        'costs' => '200.00',
        'zone' => '',
        'rate' => 'minimum',
        'phases' => [],
    ]);

    $result = $calculator->calculate($position);

    expect($result['base_fee'])->toBe('160.00');
});

test('normalizes string phase keys', function () {
    $calculator = hoaiCalculator();
    $position = hoaiPosition([
        'costs' => '1000.00',
        'phases' => ['1'],
    ]);

    $result = $calculator->calculate($position);

    expect($result['phase_fee'])->toBe('100.00');
});

test('throws when zone configuration is missing', function () {
    $calculator = hoaiCalculator(['zones' => []]);
    $position = hoaiPosition();

    expect(fn () => $calculator->calculate($position))
        ->toThrow(InvalidArgumentException::class, 'HOAI configuration is missing zone rates.');
});

test('throws when zone is unknown', function () {
    $calculator = hoaiCalculator();
    $position = hoaiPosition(['zone' => 'III']);

    expect(fn () => $calculator->calculate($position))
        ->toThrow(InvalidArgumentException::class, 'Unknown HOAI zone [iii].');
});

test('throws when rate tier is unknown', function () {
    $calculator = hoaiCalculator();
    $position = hoaiPosition(['rate' => 'invalid']);

    expect(fn () => $calculator->calculate($position))
        ->toThrow(InvalidArgumentException::class, 'Unknown HOAI rate tier [invalid] for zone [ii].');
});

test('throws when phase configuration is missing', function () {
    $calculator = hoaiCalculator(['phases' => []]);
    $position = hoaiPosition();

    expect(fn () => $calculator->calculate($position))
        ->toThrow(InvalidArgumentException::class, 'HOAI configuration is missing phase percentages.');
});

test('throws when phase number is not configured', function () {
    $calculator = hoaiCalculator();
    $position = hoaiPosition(['phases' => [9]]);

    expect(fn () => $calculator->calculate($position))
        ->toThrow(InvalidArgumentException::class, 'Unknown HOAI phase [9].');
});