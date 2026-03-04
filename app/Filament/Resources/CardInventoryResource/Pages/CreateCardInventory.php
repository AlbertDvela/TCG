<?php

namespace App\Filament\Resources\CardInventoryResource\Pages;

use App\Filament\Resources\CardInventoryResource;
use App\Models\YgoCard;
use Filament\Resources\Pages\CreateRecord;

class CreateCardInventory extends CreateRecord
{
    protected static string $resource = CardInventoryResource::class;

    /**
     * mount() se ejecuta al cargar la página.
     * Aquí leemos el ?card_id= y llenamos el formulario antes de que se renderice.
     */
    public function mount(): void
    {
        parent::mount();

        $cardId = request()->query('card_id');

        if (!$cardId) return;

        $card = YgoCard::find($cardId);

        if (!$card) return;

        $this->form->fill([
            'ygo_card_id'           => $card->id,
            'is_manual_entry'       => false,
            'market_price_snapshot' => $card->market_price,
            'selling_price'         => $card->market_price,

            // Campos de vista previa (Placeholders reactivos)
            '_catalog_name'         => $card->name,
            '_catalog_set_code'     => $card->set_code,
            '_catalog_rarity'       => $card->rarity,
            '_catalog_type'         => $card->type,
            '_catalog_attribute'    => $card->attribute ?? '-',
            '_catalog_atk'          => $card->atk ?? '-',
            '_catalog_def'          => $card->def ?? '-',
            '_catalog_description'  => $card->description,
            '_catalog_image'        => $card->image_path,
        ]);
    }
}
