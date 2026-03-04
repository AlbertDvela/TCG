<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProductInventory extends Model
{
    protected $table = 'product_inventory';

    protected $fillable = [
        'sku', 'ygo_product_id', 'condition', 'language', 'quantity',
        'custom_image_path', 'price_at_purchase', 'market_price_snapshot',
        'selling_price', 'customer_id', 'registered_by', 'notes',
    ];

    protected $casts = [
        'price_at_purchase'     => 'decimal:2',
        'market_price_snapshot' => 'decimal:2',
        'selling_price'         => 'decimal:2',
        'quantity'              => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (ProductInventory $item) {
            do {
                $sku = 'P-' . strtoupper(Str::random(8));
            } while (self::where('sku', $sku)->exists());
            $item->sku = $sku;

            if ($item->ygo_product_id) {
                $product = YgoProduct::find($item->ygo_product_id);
                if (is_null($item->market_price_snapshot)) $item->market_price_snapshot = $product?->market_price;
                if (is_null($item->selling_price))         $item->selling_price = $product?->market_price;
            }

            if (is_null($item->registered_by)) {
                $item->registered_by = auth()->id();
            }
        });
    }

    public function product(): BelongsTo     { return $this->belongsTo(YgoProduct::class, 'ygo_product_id'); }
    public function customer(): BelongsTo    { return $this->belongsTo(Customer::class); }
    public function registeredBy(): BelongsTo { return $this->belongsTo(User::class, 'registered_by'); }

    public function getEstimatedMarginAttribute(): ?float
    {
        if (is_null($this->selling_price) || is_null($this->price_at_purchase)) return null;
        return (float) $this->selling_price - (float) $this->price_at_purchase;
    }
}
