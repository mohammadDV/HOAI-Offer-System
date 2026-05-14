<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OfferGroupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['offer_id', 'title', 'sort_order'])]
class OfferGroup extends Model
{
    /** @use HasFactory<OfferGroupFactory> */
    use HasFactory;

    /**
     * Get the parent offer
     *
     * @return BelongsTo<Offer, $this>
     */
    public function offer(): BelongsTo
    {
        return $this->belongsTo(Offer::class);
    }

    /**
     * Get the manual positions for the group
     *
     * @return HasMany<Position, $this>
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Get the HOAI positions for the group
     *
     * @return HasMany<HoaiPosition, $this>
     */
    public function hoaiPositions(): HasMany
    {
        return $this->hasMany(HoaiPosition::class);
    }
}
