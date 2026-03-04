<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProductType extends Model
{
    protected $fillable = ['code', 'description', 'image_url'];

    public function products(): HasMany
    {
        return $this->hasMany(YgoProduct::class);
    }
}
