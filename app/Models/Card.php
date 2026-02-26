<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;

    // Definimos los campos que se pueden llenar masivamente
    protected $fillable = [
        'game',
        'name',
        'set_code',
        'set_name',
        'collector_number',
        'rarity',
        'attributes'
    ];

    /**
     * Casts: Esto le dice a Laravel que la columna 'attributes' 
     * es un JSON y debe convertirla automáticamente en un Array.
     */
    protected $casts = [
        'attributes' => 'array',
    ];

    /**
     * ACCESSOR PARA LA IMAGEN
     * Se accede como $card->image_url
     */
    public function getImageUrlAttribute()
    {
        // Limpiamos el set_code (Scryfall prefiere minúsculas)
        $set = strtolower($this->set_code);
        $number = $this->collector_number;

        // Si no tenemos número de coleccionista, usamos búsqueda por nombre
        if (!$number) {
            return "https://api.scryfall.com/cards/named?fuzzy=" . urlencode($this->name) . "&format=image";
        }

        return "https://api.scryfall.com/cards/{$set}/{$number}?format=image&version=normal";
    }

    /**
     * SCOPES DE BÚSQUEDA
     * Esto te permite hacer Card::search($request)->paginate() en el controlador
     */
    public function scopeSearch($query, $params)
    {
        return $query->when($params['name'] ?? null, function ($q, $name) {
            $q->where('name', 'LIKE', "%{$name}%");
        })
        ->when($params['set_code'] ?? null, function ($q, $set) {
            $q->where('set_code', strtoupper($set));
        })
        ->when($params['rarity'] ?? null, function ($q, $rarity) {
            $q->where('rarity', $rarity);
        });
    }
}