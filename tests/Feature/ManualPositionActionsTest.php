<?php

declare(strict_types=1);

use App\Actions\CreateManualPositionAction;
use App\Actions\UpdateManualPositionAction;
use App\Models\Offer;
use App\Models\OfferGroup;
use App\Models\Position;

test('create manual position persists quantity unit price and computed total', function () {
    $group = OfferGroup::factory()->for(Offer::factory())->create();
    $action = app(CreateManualPositionAction::class);

    $position = $action->execute($group, [
        'title' => 'Site Visit',
        'quantity' => '2.5',
        'unit_price' => '400.00',
    ]);

    expect($position->exists)->toBeTrue()
        ->and($position->offer_group_id)->toBe($group->id)
        ->and((string) $position->title)->toBe('Site Visit')
        ->and((string) $position->quantity)->toBe('2.50')
        ->and((string) $position->unit_price)->toBe('400.00')
        ->and((string) $position->total)->toBe('1000.00');
});

test('update manual position recalculates total', function () {
    $position = Position::factory()->create([
        'title' => 'Old',
        'quantity' => '1.00',
        'unit_price' => '100.00',
        'total' => '100.00',
    ]);
    $action = app(UpdateManualPositionAction::class);

    $updated = $action->execute($position, [
        'title' => 'Revised line',
        'quantity' => 3,
        'unit_price' => '50.5',
    ]);

    expect($updated->id)->toBe($position->id)
        ->and((string) $updated->title)->toBe('Revised line')
        ->and((string) $updated->quantity)->toBe('3.00')
        ->and((string) $updated->unit_price)->toBe('50.50')
        ->and((string) $updated->total)->toBe('151.50');
});
