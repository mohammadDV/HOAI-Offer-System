<?php

declare(strict_types=1);

use App\Models\HoaiPosition;
use App\Models\Offer;
use App\Models\OfferGroup;
use App\Models\Position;

describe('OfferGroup model', function () {
    test('can persist an offer group for an offer', function () {
        $offer = Offer::factory()->create();

        $group = OfferGroup::query()->create([
            'offer_id' => $offer->id,
            'title' => 'Architecture',
            'sort_order' => 1,
        ]);

        expect($group->exists)->toBeTrue()
            ->and($group->id)->toBeGreaterThan(0);

        $this->assertDatabaseHas('offer_groups', [
            'offer_id' => $offer->id,
            'title' => 'Architecture',
            'sort_order' => 1,
        ]);
    });

    test('can update an offer group', function () {
        $group = OfferGroup::factory()->for(Offer::factory())->create([
            'title' => 'Original',
            'sort_order' => 0,
        ]);

        $group->update([
            'title' => 'Revised',
            'sort_order' => 5,
        ]);

        $group->refresh();

        expect($group->title)->toBe('Revised')
            ->and($group->sort_order)->toBe(5);

        $this->assertDatabaseHas('offer_groups', [
            'id' => $group->id,
            'title' => 'Revised',
            'sort_order' => 5,
        ]);
    });

    test('belongs to its parent offer', function () {
        $offer = Offer::factory()->create(['title' => 'Client Project']);
        $group = OfferGroup::factory()->for($offer)->create();

        $group->load('offer');

        expect($group->offer)->toBeInstanceOf(Offer::class)
            ->and($group->offer->is($offer))->toBeTrue()
            ->and($group->offer->title)->toBe('Client Project');
    });

    test('parent offer has many offer groups', function () {
        $offer = Offer::factory()->create();
        OfferGroup::factory()->for($offer)->create(['title' => 'Group A', 'sort_order' => 1]);
        OfferGroup::factory()->for($offer)->create(['title' => 'Group B', 'sort_order' => 2]);

        $offer->load('offerGroups');

        expect($offer->offerGroups)->toHaveCount(2)
            ->and($offer->offerGroups->pluck('title')->sort()->values()->all())->toBe(['Group A', 'Group B']);
    });

    test('has many manual positions', function () {
        $group = OfferGroup::factory()->for(Offer::factory())->create();
        Position::factory()->count(2)->for($group)->create();

        $group->load('positions');

        expect($group->positions)->toHaveCount(2)
            ->and($group->positions->first()->offer_group_id)->toBe($group->id);
    });

    test('has many hoai positions', function () {
        $group = OfferGroup::factory()->for(Offer::factory())->create();
        HoaiPosition::factory()->count(2)->for($group)->create();

        $group->load('hoaiPositions');

        expect($group->hoaiPositions)->toHaveCount(2)
            ->and($group->hoaiPositions->first()->offer_group_id)->toBe($group->id);
    });

    test('deleting parent offer cascades to offer groups', function () {
        $offer = Offer::factory()->create();
        $group = OfferGroup::factory()->for($offer)->create();

        $offer->delete();

        $this->assertDatabaseMissing('offer_groups', ['id' => $group->id]);
    });

    test('deleting offer group cascades to positions and hoai positions', function () {
        $group = OfferGroup::factory()->for(Offer::factory())->create();
        $position = Position::factory()->for($group)->create();
        $hoai = HoaiPosition::factory()->for($group)->create();

        $group->delete();

        $this->assertDatabaseMissing('positions', ['id' => $position->id]);
        $this->assertDatabaseMissing('hoai_positions', ['id' => $hoai->id]);
    });
});
