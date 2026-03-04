<?php

namespace App\Console\Commands\Yugioh;

use Illuminate\Console\Command;
use App\Models\YgoCard;
use App\Services\YugiohService;
use Illuminate\Support\Facades\Log;

class SyncSet extends Command
{
    // El comando se usará como: php artisan ygo:sync-set "Legend of Blue Eyes White Dragon"
    protected $signature = 'ygo:sync-set {set_name}';
    protected $description = 'Importa todas las cartas de un set específico desde la API';

    public function handle(YugiohService $service)
    {
        $setName = $this->argument('set_name');
        $this->info("Buscando el set: {$setName}...");

        $cards = $service->getCardFromApi($setName); // Reutilizamos tu servicio

        if (!$cards) {
            $this->error("No se encontraron cartas para este set.");
            return;
        }

        $bar = $this->output->createProgressBar(count($cards));
        $bar->start();

        foreach ($cards as $cardData) {
            // Buscamos si existe por Konami ID o la creamos
            YgoCard::updateOrCreate(
                ['konami_id' => $cardData['id']],
                [
                    'name' => $cardData['name'],
                    'type' => $cardData['type'],
                    'attribute' => $cardData['attribute'] ?? null,
                    'level' => $cardData['level'] ?? null,
                    'atk' => $cardData['atk'] ?? null,
                    'def' => $cardData['def'] ?? null,
                    'description' => $cardData['desc'],
                    // Guardamos el código del set que coincida con lo que buscamos
                    'set_code' => collect($cardData['card_sets'])
                        ->firstWhere('set_name', $setName)['set_code'] ?? 'N/A',
                    'rarity' => collect($cardData['card_sets'])
                        ->firstWhere('set_name', $setName)['set_rarity'] ?? 'Common',
                ]
            );
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("¡Éxito! Set '{$setName}' sincronizado correctamente.");
    }
}