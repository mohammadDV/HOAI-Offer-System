<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\HoaiPositionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'offer_group_id',
    'title',
    'costs',
    'zone',
    'rate',
    'phases',
    'construction_markup',
    'additional_costs',
    'vat',
    'total',
])]
class HoaiPosition extends Model
{
    /** @use HasFactory<HoaiPositionFactory> */
    use HasFactory;

    /**
     * Get the attribute casts
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'costs' => 'decimal:2',
            'construction_markup' => 'decimal:2',
            'additional_costs' => 'decimal:2',
            'vat' => 'decimal:2',
            'total' => 'decimal:2',
            'phases' => 'array',
        ];
    }

    /**
     * Get the parent offer group
     *
     * @return BelongsTo<OfferGroup, $this>
     */
    public function offerGroup(): BelongsTo
    {
        return $this->belongsTo(OfferGroup::class);
    }
}
