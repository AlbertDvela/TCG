<?php

namespace App\Filament\Resources\YgoCardResource\Pages;

use App\Filament\Resources\YgoCardResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListYgoCards extends ListRecords
{
    protected static string $resource = YgoCardResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
