<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YgoCard extends Model
{
    protected $fillable = [
        'konami_id',
        'name',
        'type',
        'description',
        'set_code',
        'rarity',
        'image_path',
        'attribute',
        'level',
        'atk',
        'def',
        'market_price',
    ];

    /**
     * Todos los ejemplares físicos de esta carta en el inventario.
     */
    public function inventory(): HasMany
    {
        return $this->hasMany(CardInventory::class, 'ygo_card_id');
    }

    /**
     * Cantidad total en stock sumando todos los ejemplares.
     */
    public function getTotalStockAttribute(): int
    {
        return $this->inventory()->sum('quantity');
    }
}
