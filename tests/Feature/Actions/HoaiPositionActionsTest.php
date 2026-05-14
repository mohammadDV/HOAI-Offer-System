<?php

declare(strict_types=1);

use App\Actions\CreateHoaiPositionAction;
use App\Actions\UpdateHoaiPositionAction;
use App\Enums\HoaiRate;
use App\Enums\HoaiZone;
use App\Models\HoaiPosition;
use App\Models\Offer;
use App\Models\OfferGroup;
use App\Services\HoaiService\Contracts\HoaiCalculatorContract;

test('create hoai position normalizes attributes and persists mocked calculator total', function () {
    $group = OfferGroup::factory()->for(Offer::factory())->create();

    $this->mock(HoaiCalculatorContract::class, function ($mock) use ($group) {
        $mock->shouldReceive('calculate')
            ->once()
            ->withArgs(function ($position) use ($group) {
                return $position instanceof HoaiPosition
                    && $position->offer_group_id === $group->id
                    && $position->title === 'Planning fee'
                    && (string) $position->costs === '1000.00'
                    && $position->zone === HoaiZone::II->value
                    && $position->rate === HoaiRate::Middle->value
                    && $position->phases === [1]
                    && (string) $position->construction_markup === '0.00'
                    && (string) $position->additional_costs === '0.00'
                    && (string) $position->vat === '0.00';
            })
            ->andReturn([
                'base_fee' => '1000.00',
                'phase_percentage' => '2.00',
                'phase_fee' => '20.00',
                'construction_markup_amount' => '0.00',
                'additional_costs_amount' => '0.00',
                'subtotal' => '1020.00',
                'vat_amount' => '0.00',
                'total' => '1111.11',
            ]);
    });

    $action = app(CreateHoaiPositionAction::class);

    $hoai = $action->execute($group, [
        'title' => 'Planning fee',
        'costs' => '1000',
        'zone' => HoaiZone::II->value,
        'rate' => HoaiRate::Middle->value,
        'phases' => ['1'],
        'construction_markup' => '0',
        'additional_costs' => '0',
        'vat' => '0',
    ]);

    expect($hoai->exists)->toBeTrue()
        ->and($hoai->offer_group_id)->toBe($group->id)
        ->and($hoai->title)->toBe('Planning fee')
        ->and((string) $hoai->costs)->toBe('1000.00')
        ->and($hoai->phases)->toBe([1])
        ->and((string) $hoai->construction_markup)->toBe('0.00')
        ->and((string) $hoai->total)->toBe('1111.11');
});

test('update hoai position reapplies attributes and persists mocked calculator total', function () {
    $group = OfferGroup::factory()->for(Offer::factory())->create();
    $hoai = HoaiPosition::factory()->for($group)->create([
        'title' => 'Original',
        'costs' => '500.00',
        'zone' => HoaiZone::II->value,
        'rate' => HoaiRate::Middle->value,
        'phases' => [1],
        'construction_markup' => '0',
        'additional_costs' => '0',
        'vat' => '0',
        'total' => '510.00',
    ]);

    $this->mock(HoaiCalculatorContract::class, function ($mock) use ($hoai) {
        $mock->shouldReceive('calculate')
            ->once()
            ->withArgs(function ($position) use ($hoai) {
                return $position instanceof HoaiPosition
                    && $position->is($hoai)
                    && $position->title === 'Revised fee'
                    && (string) $position->costs === '10000.00'
                    && $position->phases === [1, 2]
                    && (string) $position->construction_markup === '0.00'
                    && (string) $position->additional_costs === '0.00'
                    && (string) $position->vat === '0.00';
            })
            ->andReturn([
                'base_fee' => '10000.00',
                'phase_percentage' => '9.00',
                'phase_fee' => '900.00',
                'construction_markup_amount' => '0.00',
                'additional_costs_amount' => '0.00',
                'subtotal' => '10900.00',
                'vat_amount' => '0.00',
                'total' => '2222.22',
            ]);
    });

    $action = app(UpdateHoaiPositionAction::class);

    $updated = $action->execute($hoai, [
        'title' => 'Revised fee',
        'costs' => '10000',
        'zone' => HoaiZone::II->value,
        'rate' => HoaiRate::Middle->value,
        'phases' => [1, 2],
        'construction_markup' => '0',
        'additional_costs' => '0',
        'vat' => '0',
    ]);

    expect($updated->id)->toBe($hoai->id)
        ->and($updated->title)->toBe('Revised fee')
        ->and((string) $updated->costs)->toBe('10000.00')
        ->and($updated->phases)->toBe([1, 2])
        ->and((string) $updated->total)->toBe('2222.22');
});
