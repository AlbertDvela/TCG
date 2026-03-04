<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YugiohService
{
    /**
     * Busca cartas en la API. 
     * Puede recibir un código de carta (LOB-001) o un nombre de set (Legend of Blue Eyes...)
     */
    public function getCardFromApi(string $search): ?array
    {
        // Detectamos si es un código de carta (tiene un guion) o un set completo
        $isSetCode = str_contains($search, '-');
        
        $params = $isSetCode 
            ? ['set' => $search] 
            : ['cardset' => $search];

        try {
            $response = Http::get("https://db.ygoprodeck.com/api/v7/cardinfo.php", $params);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? null;

                // Si buscamos por código de carta (LOB-001), devolvemos solo el primer resultado
                // Si es un set completo, devolvemos el array entero de cartas
                return $isSetCode ? ($data[0] ?? null) : $data;
            }
        } catch (\Exception $e) {
            Log::error("Error conectando con YGOPRODeck: " . $e->getMessage());
        }

        return null;
    }
}