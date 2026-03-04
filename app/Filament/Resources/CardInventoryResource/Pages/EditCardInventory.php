<?php
namespace App\Filament\Resources\CardInventoryResource\Pages;
use App\Filament\Resources\CardInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCardInventory extends EditRecord
{
    protected static string $resource = CardInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
