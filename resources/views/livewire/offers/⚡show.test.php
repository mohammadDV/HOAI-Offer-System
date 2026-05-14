<?php

declare(strict_types=1);

/**
 * Browser (Playwright) tests for the offer workspace page.
 *
 */

use App\Models\Offer;
use App\Models\OfferGroup;

it('renders the offer workspace in the browser', function () {

    $offer = Offer::factory()->create([
        'title' => 'E2E Offer Workspace',
        'client_name' => 'Browser Client',
    ]);

    OfferGroup::factory()->create([
        'offer_id' => $offer->id,
        'title' => 'Visible Group',
        'sort_order' => 0,
    ]);

    visit(route('offers.show', $offer))
        ->assertSourceHas('E2E Offer Workspace')
        ->assertSee('All offers')
        ->assertSee('Visible Group')
        ->assertSee('Offer total')
        ->assertNoJavaScriptErrors();
})->skip((bool) env('PEST_SKIP_BROWSER', false), 'Set PEST_SKIP_BROWSER=1 to skip Playwright tests.');