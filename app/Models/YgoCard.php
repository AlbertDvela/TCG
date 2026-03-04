<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class YgoCard extends Model
{
    /**
     * Los atributos que se pueden asignar masivamente.
     * Esto permite que el comando sync-all guarde los datos.
     */
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
}