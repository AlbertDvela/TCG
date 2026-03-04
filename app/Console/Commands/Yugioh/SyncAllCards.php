<?php

namespace App\Console\Commands\Yugioh;

use App\Models\YgoCard;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http; // <--- Importante para desactivar el log

class SyncAllCards extends Command
{
    /**
     * El nombre y firma del comando de consola.
     */
    protected $signature = 'ygo:sync-all';

    /**
     * La descripción del comando.
     */
    protected $description = 'Descarga el catálogo completo de Yu-Gi-Oh! optimizando memoria';

    /**
     * Ejecuta el comando de consola.
     */
    public function handle()
    {
        // 1. Forzamos a PHP a usar más RAM (4GB es suficiente para 13k cartas)
        ini_set('memory_limit', '4096M');

        // 2. Evitamos que Laravel guarde en memoria cada consulta SQL
        DB::connection()->disableQueryLog();

        $this->info('Conectando con la API de YGOPRODeck (esto puede tardar unos segundos)...');

        // 3. Hacemos la petición con un tiempo de espera largo (timeout)
        $response = Http::timeout(300)->get('https://db.ygoprodeck.com/api/v7/cardinfo.php');

        if ($response->successful()) {
            $cards = $response->json()['data'];
            $total = count($cards);

            $this->info("Se encontraron {$total} cartas. Iniciando importación...");

            // 4. Usamos la barra de progreso integrada de Laravel
            $this->withProgressBar($cards, function ($cardData) {
                // Intentamos buscar si la carta pertenece a LOB, si no, tomamos el primero que aparezca
                $prioritySet = collect($cardData['card_sets'] ?? [])
                    ->first(fn ($set) => str_contains($set['set_name'], 'Legend of Blue Eyes'));

                $displaySet = $prioritySet ?: ($cardData['card_sets'][0] ?? null);

                YgoCard::updateOrCreate(
                    ['konami_id' => $cardData['id']],
                    [
                        'name' => $cardData['name'],
                        'type' => $cardData['type'],
                        'description' => $cardData['desc'],
                        'set_code' => $displaySet['set_code'] ?? 'N/A',
                        'rarity' => $displaySet['set_rarity'] ?? 'Common',
                        'market_price' => $cardData['card_prices'][0]['tcgplayer_price'] ?? 0,
                        'image_path' => $cardData['card_images'][0]['image_url'] ?? null,
                        'market_price' => $cardData['card_prices'][0]['tcgplayer_price'] ?? 0,
                    ]
                );
            });

            $this->newLine();
            $this->info('¡Catálogo sincronizado exitosamente!');
        } else {
            $this->error('No se pudo obtener respuesta de la API. Verifica tu conexión.');
        }

        return self::SUCCESS;
    }
}
