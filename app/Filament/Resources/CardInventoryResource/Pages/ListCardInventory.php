<?php
namespace App\Filament\Resources\CardInventoryResource\Pages;
use App\Filament\Resources\CardInventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCardInventory extends ListRecords
{
    protected static string $resource = CardInventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Registrar carta')];
    }
}
