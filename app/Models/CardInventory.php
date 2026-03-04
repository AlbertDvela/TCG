<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CardInventory extends Model
{
    protected $table = 'card_inventory';

    protected $fillable = [
        'sku', 'ygo_card_id', 'card_condition_id', 'card_edition_id',
        'language', 'quantity', 'custom_image_path', 'price_at_purchase',
        'market_price_snapshot', 'selling_price', 'customer_id', 'registered_by', 'notes',
        'is_manual_entry', 'manual_name', 'manual_set_code', 'manual_rarity',
        'manual_type', 'manual_attribute', 'manual_atk', 'manual_def',
        'manual_description', 'manual_image_url',
    ];

    protected $casts = [
        'price_at_purchase'     => 'decimal:2',
        'market_price_snapshot' => 'decimal:2',
        'selling_price'         => 'decimal:2',
        'quantity'              => 'integer',
        'is_manual_entry'       => 'boolean',
        'manual_atk'            => 'integer',
        'manual_def'            => 'integer',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (CardInventory $item) {
            do {
                $sku = strtoupper(Str::random(10));
            } while (self::where('sku', $sku)->exists());
            $item->sku = $sku;

            if (!$item->is_manual_entry && $item->ygo_card_id) {
                $card = YgoCard::find($item->ygo_card_id);
                if (is_null($item->market_price_snapshot)) $item->market_price_snapshot = $card?->market_price;
                if (is_null($item->selling_price))         $item->selling_price = $card?->market_price;
            }

            if (is_null($item->registered_by)) {
                $item->registered_by = auth()->id();
            }
        });
    }

    public function card(): BelongsTo       { return $this->belongsTo(YgoCard::class, 'ygo_card_id'); }
    public function condition(): BelongsTo  { return $this->belongsTo(CardCondition::class, 'card_condition_id'); }
    public function edition(): BelongsTo    { return $this->belongsTo(CardEdition::class, 'card_edition_id'); }
    public function customer(): BelongsTo   { return $this->belongsTo(Customer::class); }
    public function registeredBy(): BelongsTo { return $this->belongsTo(User::class, 'registered_by'); }

    public function getEffectiveNameAttribute(): string
    {
        return $this->is_manual_entry ? ($this->manual_name ?? 'Sin nombre') : ($this->card?->name ?? 'Sin nombre');
    }

    public function getEffectiveImageAttribute(): ?string
    {
        return $this->custom_image_path ?? ($this->is_manual_entry ? $this->manual_image_url : $this->card?->image_path);
    }

    public function getEffectiveSetCodeAttribute(): ?string
    {
        return $this->is_manual_entry ? $this->manual_set_code : $this->card?->set_code;
    }

    public function getEffectiveRarityAttribute(): ?string
    {
        return $this->is_manual_entry ? $this->manual_rarity : $this->card?->rarity;
    }

    public function getEstimatedMarginAttribute(): ?float
    {
        if (is_null($this->selling_price) || is_null($this->price_at_purchase)) return null;
        return (float) $this->selling_price - (float) $this->price_at_purchase;
    }

    public function getMarketGainAttribute(): ?float
    {
        if ($this->is_manual_entry) return null;
        $marketPrice = $this->card?->market_price;
        if (is_null($marketPrice) || is_null($this->price_at_purchase)) return null;
        return (float) $marketPrice - (float) $this->price_at_purchase;
    }
}
