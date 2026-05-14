<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\HoaiZone;
use App\Models\HoaiPosition;
use App\Models\Offer;
use App\Models\OfferGroup;
use App\Models\Position;
use App\Services\HoaiService\Contracts\HoaiCalculatorContract;
use Illuminate\Database\Seeder;

class OfferDemoSeeder extends Seeder
{
    /**
     * Seed demonstration offers with groups, manual positions, and HOAI lines
     */
    public function run(): void
    {
        $calculator = app(HoaiCalculatorContract::class);

        foreach ($this->offerBlueprints() as $blueprint) {
            $this->seedOffer($calculator, $blueprint);
        }
    }

    /**
     * Persist one offer tree from a blueprint
     *
     * @param  array{offer: array<string, mixed>, groups: list<array{title: string, sort_order: int, positions?: list<array<string, mixed>>, hoai?: array<string, mixed>}>}  $blueprint
     */
    private function seedOffer(HoaiCalculatorContract $calculator, array $blueprint): void
    {
        $offer = Offer::factory()->create($blueprint['offer']);

        foreach ($blueprint['groups'] as $groupData) {
            $group = OfferGroup::factory()->create([
                'offer_id' => $offer->id,
                'title' => $groupData['title'],
                'sort_order' => $groupData['sort_order'],
            ]);

            foreach ($groupData['positions'] ?? [] as $positionAttrs) {
                Position::factory()->create(array_merge(
                    ['offer_group_id' => $group->id],
                    $positionAttrs,
                ));
            }

            if (isset($groupData['hoai'])) {
                $this->createHoaiPositionWithTotal($calculator, $group->id, $groupData['hoai']);
            }
        }
    }

    /**
     * Create a HOAI position and set total from the calculator
     *
     * @param  array<string, mixed>  $attributes
     */
    private function createHoaiPositionWithTotal(HoaiCalculatorContract $calculator, int $offerGroupId, array $attributes): void
    {
        /** @var HoaiPosition $hoai */
        $hoai = HoaiPosition::factory()->create(array_merge(
            [
                'offer_group_id' => $offerGroupId,
                'vat' => '19.00',
                'total' => '0.00',
            ],
            $attributes,
        ));

        $hoai->update([
            'total' => $calculator->calculate($hoai)['total'],
        ]);
    }

    /**
     * Demo offer definitions (titles, clients, and line items)
     *
     * @return list<array{offer: array<string, mixed>, groups: list<array<string, mixed>>}>
     */
    private function offerBlueprints(): array
    {
        return [
            [
                'offer' => [
                    'title' => 'Villa Renovation Berlin',
                    'client_name' => 'Max Mustermann',
                    'notes' => 'Historic villa: architecture and structural engineering.',
                ],
                'groups' => [
                    [
                        'title' => 'Architecture',
                        'sort_order' => 1,
                        'positions' => [
                            ['title' => 'Site Visit', 'quantity' => '2.00', 'unit_price' => '500.00', 'total' => '1000.00'],
                            ['title' => 'Design Consultation', 'quantity' => '1.00', 'unit_price' => '1200.00', 'total' => '1200.00'],
                        ],
                        'hoai' => [
                            'title' => 'Architectural Planning Fee',
                            'costs' => '100000.00',
                            'zone' => HoaiZone::II->value,
                            'rate' => 'middle',
                            'phases' => [1, 2, 3, 4],
                            'construction_markup' => '10.00',
                            'additional_costs' => '5.00',
                        ],
                    ],
                    [
                        'title' => 'Structural Engineering',
                        'sort_order' => 2,
                        'positions' => [
                            ['title' => 'Structural Review', 'quantity' => '3.00', 'unit_price' => '400.00', 'total' => '1200.00'],
                        ],
                        'hoai' => [
                            'title' => 'Engineering Calculation Fee',
                            'costs' => '80000.00',
                            'zone' => HoaiZone::IV->value,
                            'rate' => 'minimum',
                            'phases' => [1, 2, 3],
                            'construction_markup' => '5.00',
                            'additional_costs' => '3.00',
                        ],
                    ],
                ],
            ],
            [
                'offer' => [
                    'title' => 'Office Fit-out Munich',
                    'client_name' => 'Bayerische Büro GmbH',
                    'notes' => 'Open-plan office, two floors, MEP coordination.',
                ],
                'groups' => [
                    [
                        'title' => 'Interior Architecture',
                        'sort_order' => 1,
                        'positions' => [
                            ['title' => 'As-built Survey', 'quantity' => '1.00', 'unit_price' => '2800.00', 'total' => '2800.00'],
                        ],
                        'hoai' => [
                            'title' => 'HOAI Planning LPH 1–5',
                            'costs' => '45000.00',
                            'zone' => HoaiZone::III->value,
                            'rate' => 'middle',
                            'phases' => [1, 2, 5],
                            'construction_markup' => '8.00',
                            'additional_costs' => '4.00',
                        ],
                    ],
                ],
            ],
            [
                'offer' => [
                    'title' => 'Residential Extension Hamburg',
                    'client_name' => 'Familie Schmidt',
                    'notes' => 'Single-storey rear extension; building permit package.',
                ],
                'groups' => [
                    [
                        'title' => 'Architecture',
                        'sort_order' => 1,
                        'positions' => [
                            ['title' => 'Preliminary Sketch', 'quantity' => '1.00', 'unit_price' => '900.00', 'total' => '900.00'],
                            ['title' => 'Building Application Support', 'quantity' => '1.00', 'unit_price' => '1500.00', 'total' => '1500.00'],
                        ],
                        'hoai' => [
                            'title' => 'Design Services',
                            'costs' => '22000.00',
                            'zone' => HoaiZone::II->value,
                            'rate' => 'minimum',
                            'phases' => [1, 2],
                            'construction_markup' => '0.00',
                            'additional_costs' => '0.00',
                        ],
                    ],
                ],
            ],
            [
                'offer' => [
                    'title' => 'Bridge Condition Survey Cologne',
                    'client_name' => 'Stadt Köln – Tiefbau',
                    'notes' => 'Pedestrian bridge: inspection report and strengthening concept.',
                ],
                'groups' => [
                    [
                        'title' => 'Civil / Structural',
                        'sort_order' => 1,
                        'positions' => [
                            ['title' => 'Drone Documentation', 'quantity' => '1.00', 'unit_price' => '3500.00', 'total' => '3500.00'],
                        ],
                        'hoai' => [
                            'title' => 'Structural Assessment Fee',
                            'costs' => '125000.00',
                            'zone' => HoaiZone::V->value,
                            'rate' => 'maximum',
                            'phases' => [1, 3, 4, 6],
                            'construction_markup' => '12.00',
                            'additional_costs' => '6.00',
                        ],
                    ],
                ],
            ],
            [
                'offer' => [
                    'title' => 'School Gymnasium Stuttgart',
                    'client_name' => 'Landkreis Esslingen',
                    'notes' => 'New sports hall, timber primary structure.',
                ],
                'groups' => [
                    [
                        'title' => 'Architecture',
                        'sort_order' => 1,
                        'positions' => [],
                        'hoai' => [
                            'title' => 'Competition & Concept',
                            'costs' => '180000.00',
                            'zone' => HoaiZone::IV->value,
                            'rate' => 'middle',
                            'phases' => [1, 2, 3, 4, 5],
                            'construction_markup' => '10.00',
                            'additional_costs' => '5.00',
                        ],
                    ],
                    [
                        'title' => 'Building Physics',
                        'sort_order' => 2,
                        'positions' => [
                            ['title' => 'Thermal Bridge Review', 'quantity' => '1.00', 'unit_price' => '2400.00', 'total' => '2400.00'],
                        ],
                        'hoai' => [
                            'title' => 'Energy / Acoustics HOAI Share',
                            'costs' => '35000.00',
                            'zone' => HoaiZone::III->value,
                            'rate' => 'minimum',
                            'phases' => [4, 5],
                            'construction_markup' => '5.00',
                            'additional_costs' => '2.00',
                        ],
                    ],
                ],
            ],
            [
                'offer' => [
                    'title' => 'Medical Center Frankfurt',
                    'client_name' => 'RhineHealth AG',
                    'notes' => 'Ambulatory unit renovation; hygiene-critical zones.',
                ],
                'groups' => [
                    [
                        'title' => 'Technical Planning',
                        'sort_order' => 1,
                        'positions' => [
                            ['title' => 'HVAC Concept Workshop', 'quantity' => '2.00', 'unit_price' => '1800.00', 'total' => '3600.00'],
                        ],
                        'hoai' => [
                            'title' => 'Technical HOAI Bundle',
                            'costs' => '95000.00',
                            'zone' => HoaiZone::IV->value,
                            'rate' => 'middle',
                            'phases' => [2, 3, 4, 5, 6],
                            'construction_markup' => '10.00',
                            'additional_costs' => '5.00',
                        ],
                    ],
                ],
            ],
            [
                'offer' => [
                    'title' => 'Warehouse Conversion Leipzig',
                    'client_name' => 'SaxLoft Developers',
                    'notes' => 'Loft apartments; shell upgrade and new cores.',
                ],
                'groups' => [
                    [
                        'title' => 'Shell & Core',
                        'sort_order' => 1,
                        'positions' => [
                            ['title' => 'Structural Probing', 'quantity' => '4.00', 'unit_price' => '650.00', 'total' => '2600.00'],
                            ['title' => 'Fire Strategy Workshop', 'quantity' => '1.00', 'unit_price' => '3200.00', 'total' => '3200.00'],
                        ],
                        'hoai' => [
                            'title' => 'Conversion Planning Fee',
                            'costs' => '210000.00',
                            'zone' => HoaiZone::IV->value,
                            'rate' => 'maximum',
                            'phases' => [1, 2, 3, 4],
                            'construction_markup' => '15.00',
                            'additional_costs' => '7.00',
                        ],
                    ],
                ],
            ],
            [
                'offer' => [
                    'title' => 'Historic Mill Restoration Nuremberg',
                    'client_name' => 'DenkmalFonds Bayern',
                    'notes' => 'Protected façade; minimal intervention philosophy.',
                ],
                'groups' => [
                    [
                        'title' => 'Monument Care Architecture',
                        'sort_order' => 1,
                        'positions' => [
                            ['title' => 'Heritage Photo Report', 'quantity' => '1.00', 'unit_price' => '4100.00', 'total' => '4100.00'],
                        ],
                        'hoai' => [
                            'title' => 'Restoration Design Fee',
                            'costs' => '67000.00',
                            'zone' => HoaiZone::V->value,
                            'rate' => 'middle',
                            'phases' => [1, 2, 3, 4, 5, 6],
                            'construction_markup' => '8.00',
                            'additional_costs' => '4.00',
                        ],
                    ],
                ],
            ],
            [
                'offer' => [
                    'title' => 'Penthouse Interior Düsseldorf',
                    'client_name' => 'Private Client K.D.',
                    'notes' => 'High-end interior; FF&E coordination excluded.',
                ],
                'groups' => [
                    [
                        'title' => 'Interior Design',
                        'sort_order' => 1,
                        'positions' => [
                            ['title' => 'Mood Boards & Samples', 'quantity' => '1.00', 'unit_price' => '5500.00', 'total' => '5500.00'],
                            ['title' => 'Site Supervision Days', 'quantity' => '12.00', 'unit_price' => '850.00', 'total' => '10200.00'],
                        ],
                        'hoai' => [
                            'title' => 'Interior HOAI Share',
                            'costs' => '28000.00',
                            'zone' => HoaiZone::I->value,
                            'rate' => 'maximum',
                            'phases' => [3, 4, 5],
                            'construction_markup' => '6.00',
                            'additional_costs' => '3.00',
                        ],
                    ],
                ],
            ],
            [
                'offer' => [
                    'title' => 'Industrial Park Phase 1 Hannover',
                    'client_name' => 'NordLogistik SE',
                    'notes' => 'Three halls; shared infrastructure and access roads.',
                ],
                'groups' => [
                    [
                        'title' => 'Civil Engineering',
                        'sort_order' => 1,
                        'positions' => [
                            ['title' => 'Topographic Control', 'quantity' => '1.00', 'unit_price' => '5200.00', 'total' => '5200.00'],
                        ],
                        'hoai' => [
                            'title' => 'Outdoor Works HOAI',
                            'costs' => '310000.00',
                            'zone' => HoaiZone::III->value,
                            'rate' => 'middle',
                            'phases' => [1, 2, 3, 4, 5, 6, 7],
                            'construction_markup' => '10.00',
                            'additional_costs' => '5.00',
                        ],
                    ],
                    [
                        'title' => 'Utilities Coordination',
                        'sort_order' => 2,
                        'positions' => [
                            ['title' => 'Utility Provider Meetings', 'quantity' => '6.00', 'unit_price' => '450.00', 'total' => '2700.00'],
                        ],
                        'hoai' => [
                            'title' => 'Coordination Fee',
                            'costs' => '42000.00',
                            'zone' => HoaiZone::II->value,
                            'rate' => 'minimum',
                            'phases' => [2, 3],
                            'construction_markup' => '0.00',
                            'additional_costs' => '0.00',
                        ],
                    ],
                ],
            ],
        ];
    }
}
