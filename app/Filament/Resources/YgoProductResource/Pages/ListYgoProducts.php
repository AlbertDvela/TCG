<?php
namespace App\Filament\Resources\YgoProductResource\Pages;
use App\Filament\Resources\YgoProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListYgoProducts extends ListRecords
{
    protected static string $resource = YgoProductResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()->label('Nuevo producto')];
    }
}
