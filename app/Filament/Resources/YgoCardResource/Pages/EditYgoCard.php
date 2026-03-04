<?php

namespace App\Filament\Resources\YgoCardResource\Pages;

use App\Filament\Resources\YgoCardResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditYgoCard extends EditRecord
{
    protected static string $resource = YgoCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // ESTO DEBE SER PUBLIC PARA QUE NO DE ERROR FATAL
    public function getTitle(): string
    {
        return 'Editando: '.$this->record->name;
    }
}
