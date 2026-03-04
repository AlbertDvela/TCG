<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class YgoProduct extends Model
{
    protected $fillable = [
        'product_type_id',
        'name',
        'description',
        'image_url',
        'market_price',
    ];

    protected $casts = [
        'market_price' => 'decimal:2',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(ProductType::class, 'product_type_id');
    }

    public function inventory(): HasMany
    {
        return $this->hasMany(ProductInventory::class);
    }

    public function getTotalStockAttribute(): int
    {
        return $this->inventory()->sum('quantity');
    }
}
