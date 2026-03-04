<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'code',
        'name',
        'phone',
        'email',
    ];

    public function cardInventory(): HasMany
    {
        return $this->hasMany(CardInventory::class);
    }
}