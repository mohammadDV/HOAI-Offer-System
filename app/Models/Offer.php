<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\OfferFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['title', 'client_name', 'notes'])]
class Offer extends Model
{
    /** @use HasFactory<OfferFactory> */
    use HasFactory;

    /**
     * Get the offer groups for the offer
     *
     * @return HasMany<OfferGroup, $this>
     */
    public function offerGroups(): HasMany
    {
        return $this->hasMany(OfferGroup::class);
    }
}
