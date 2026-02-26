<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Card;
use JsonStreamingParser\Parser;
use JsonStreamingParser\Listener\ListenerInterface;
use Illuminate\Support\Facades\DB;

class ImportMagic extends Command implements ListenerInterface
{
    protected $signature = 'import:magic';
    protected $description = 'Importador por anzuelo de objetos';

    private $stack = [];
    private $key = null;
    private $currentSetCode = null;
    private $totalSaved = 0;

    public function handle()
    {
        DB::disableQueryLog();
        ini_set('memory_limit', '512M');
        
        $path = storage_path('app/magic_cards.json');
        $this->info("Lanzando anzuelo para capturar cartas...");
        
        $stream = fopen($path, 'r');
        try {
            $parser = new Parser($stream, $this);
            $parser->parse();
        } finally {
            fclose($stream);
        }

        $this->info("\n¡Éxito! Total de cartas guardadas: " . $this->totalSaved);
    }

    public function startObject(): void {
        $this->stack[] = [];
    }

    public function endObject(): void {
        $obj = array_pop($this->stack);

        // 1. Si el objeto tiene 'code', actualizamos el set actual
        if (isset($obj['code']) && is_string($obj['code']) && strlen($obj['code']) <= 8) {
            $this->currentSetCode = $obj['code'];
            $this->info(" -> Set: {$this->currentSetCode}");
        }

        // 2. Si el objeto tiene 'name' y NO tiene 'cards', es una CARTA
        // (Los sets tienen 'cards', las cartas no).
        if (isset($obj['name']) && !isset($obj['cards']) && $this->currentSetCode) {
            $this->saveCard($obj);
            return; // Limpieza inmediata de RAM
        }

        // Si hay stack, pasamos el objeto arriba para seguir construyendo
        if (!empty($this->stack)) {
            $this->value($obj);
        }
    }

    private function saveCard($data) {
        Card::create([
            'game' => 'magic',
            'name' => $data['name'],
            'set_code' => $this->currentSetCode,
            'set_name' => $this->currentSetCode,
            'collector_number' => $data['number'] ?? null,
            'rarity' => $data['rarity'] ?? 'common',
            'attributes' => [
                'mana_cost' => $data['manaCost'] ?? null,
                'type' => $data['type'] ?? null,
                'text' => $data['text'] ?? '',
            ],
        ]);
        $this->totalSaved++;
        
        // Feedback visual cada 100 cartas para saber que está vivo
        if ($this->totalSaved % 100 === 0) {
            $this->output->write(".");
        }
    }

    public function key(string $key): void { 
        $this->key = $key; 
    }

    public function value($value): void {
        if (empty($this->stack)) return;

        // Ignoramos campos pesados para que el objeto de la carta sea ligero
        if (in_array($this->key, ['foreignData', 'translations', 'legalities', 'rulings'])) {
            $this->key = null;
            return;
        }

        $index = count($this->stack) - 1;
        if ($this->key !== null) {
            $this->stack[$index][$this->key] = $value;
            $this->key = null;
        } else {
            $this->stack[$index][] = $value;
        }
    }

    public function startArray(): void { $this->stack[] = []; }
    public function endArray(): void {
        $array = array_pop($this->stack);
        if (!empty($this->stack)) {
            $this->value($array);
        }
    }

    public function whitespace(string $whitespace): void {}
    public function startDocument(): void {}
    public function endDocument(): void {}
}