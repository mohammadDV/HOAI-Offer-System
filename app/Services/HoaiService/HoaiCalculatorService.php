<?php

declare(strict_types=1);

namespace App\Services\HoaiService;

use App\Models\HoaiPosition;
use App\Services\HoaiService\Contracts\HoaiCalculatorContract;
use InvalidArgumentException;

final class HoaiCalculatorService implements HoaiCalculatorContract
{
    private const int BCM_SCALE = 8;

    private const int MONEY_SCALE = 2;

    /**
     * Create a new calculator using in-memory HOAI rules
     *
     * @param  array<string, mixed>  $config
     */
    public function __construct(
        private readonly array $config,
    ) {}

    /**
     * Calculate HOAI-style totals for a position
     *
     * @param HoaiPosition $position
     * @return array
     */
    public function calculate(HoaiPosition $position): array
    {
        $costs = $this->toDecimalString($position->costs);
        $zoneRate = $this->resolveZoneRate((string) $position->zone, (string) $position->rate);
        $baseFee = $this->multiply($costs, $zoneRate);

        $phasePercentage = $this->sumSelectedPhasePercentages($position->phases ?? []);
        $phaseFee = $this->percentageOf($baseFee, $phasePercentage);

        $afterPhases = $this->add($baseFee, $phaseFee);
        $constructionMarkupAmount = $this->percentageOf(
            $afterPhases,
            $this->toDecimalString($position->construction_markup),
        );

        $afterMarkup = $this->add($afterPhases, $constructionMarkupAmount);
        $additionalCostsAmount = $this->percentageOf(
            $afterMarkup,
            $this->toDecimalString($position->additional_costs),
        );

        $subtotal = $this->add($afterMarkup, $additionalCostsAmount);
        $vatAmount = $this->percentageOf(
            $subtotal,
            $this->toDecimalString($position->vat),
        );

        $total = $this->add($subtotal, $vatAmount);

        return [
            'base_fee' => $this->roundMoney($baseFee),
            'phase_percentage' => $this->roundMoney($phasePercentage),
            'phase_fee' => $this->roundMoney($phaseFee),
            'construction_markup_amount' => $this->roundMoney($constructionMarkupAmount),
            'additional_costs_amount' => $this->roundMoney($additionalCostsAmount),
            'subtotal' => $this->roundMoney($subtotal),
            'vat_amount' => $this->roundMoney($vatAmount),
            'total' => $this->roundMoney($total),
        ];
    }

    /**
     * Resolve the combined zone and rate multiplier
     *
     * @param  string  $zone
     * @param  string  $rate
     * @return string
     */
    private function resolveZoneRate(string $zone, string $rate): string
    {
        $zones = $this->config['zones'] ?? [];
        if ($zones === [] || ! is_array($zones)) {
            throw new InvalidArgumentException('HOAI configuration is missing zone rates.');
        }

        $defaultZone = (string) ($this->config['default_zone'] ?? 'II');
        $zoneKey = $zone !== '' ? strtolower($zone) : strtolower($defaultZone);
        if (! isset($zones[$zoneKey]) || ! is_array($zones[$zoneKey])) {
            throw new InvalidArgumentException(sprintf('Unknown HOAI zone [%s].', $zoneKey));
        }

        $tier = $rate !== '' ? strtolower($rate) : 'middle';
        $tierRates = $zones[$zoneKey];
        if (! isset($tierRates[$tier])) {
            throw new InvalidArgumentException(sprintf('Unknown HOAI rate tier [%s] for zone [%s].', $rate, $zoneKey));
        }

        return $this->toDecimalString($tierRates[$tier]);
    }

    /**
     * Sum configured percentages for the selected phases
     *
     * @param  array<int|string, mixed>  $phases
     * @return string
     */
    private function sumSelectedPhasePercentages(array $phases): string
    {
        $configured = $this->config['phases'] ?? [];
        if ($configured === [] || ! is_array($configured)) {
            throw new InvalidArgumentException('HOAI configuration is missing phase percentages.');
        }

        $sum = '0';
        foreach ($phases as $phase) {
            $key = $this->normalizePhaseKey($phase);
            if ($key === null) {
                continue;
            }

            if (! array_key_exists($key, $configured)) {
                throw new InvalidArgumentException(sprintf('Unknown HOAI phase [%s].', (string) $key));
            }

            $sum = $this->add($sum, $this->toDecimalString($configured[$key]));
        }

        return $sum;
    }

    /**
     * Normalize a phase entry from the position payload
     *
     * @param  mixed  $phase
     * @return int|null
     */
    private function normalizePhaseKey(mixed $phase): ?int
    {
        if (is_int($phase)) {
            return $phase;
        }

        if (is_string($phase) && is_numeric($phase)) {
            return (int) $phase;
        }

        return null;
    }

    /**
     * Convert a scalar amount to a BCMath-compatible decimal string
     *
     * @param  mixed  $value
     * @return string
     */
    private function toDecimalString(mixed $value): string
    {
        if (is_string($value) && $value !== '') {
            return $value;
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            return sprintf('%.8F', $value);
        }

        return '0';
    }

    /**
     * Multiply two decimal strings
     *
     * @param  string  $left
     * @param  string  $right
     * @return string
     */
    private function multiply(string $left, string $right): string
    {
        return bcmul($left, $right, self::BCM_SCALE);
    }

    /**
     * Add two decimal strings
     *
     * @param  string  $left
     * @param  string  $right
     * @return string
     */
    private function add(string $left, string $right): string
    {
        return bcadd($left, $right, self::BCM_SCALE);
    }

    /**
     * Calculate percentage of an amount where the percentage uses whole points (e.g. 19 = 19%)
     *
     * @param  string  $amount
     * @param  string  $percentPoints
     * @return string
     */
    private function percentageOf(string $amount, string $percentPoints): string
    {
        if (bccomp($percentPoints, '0', self::BCM_SCALE) === 0) {
            return '0';
        }

        $fraction = bcdiv($percentPoints, '100', self::BCM_SCALE);

        return bcmul($amount, $fraction, self::BCM_SCALE);
    }

    /**
     * Round a monetary BCMath string to two decimals
     *
     * @param  string  $amount
     * @return string
     */
    private function roundMoney(string $amount): string
    {
        return bcadd($amount, '0', self::MONEY_SCALE);
    }
}