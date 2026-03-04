<?php
namespace App\Filament\Resources\YgoProductResource\Pages;
use App\Filament\Resources\YgoProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditYgoProduct extends EditRecord
{
    protected static string $resource = YgoProductResource::class;
    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
