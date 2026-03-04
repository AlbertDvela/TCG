<?php

namespace App\Filament\Resources\YgoCardResource\Pages;

use App\Filament\Resources\YgoCardResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

// El nombre de la clase debe ser IGUAL al nombre del archivo
class CreateYgoCard extends CreateRecord
{
    protected static string $resource = YgoCardResource::class;

    public function getTitle(): string
    {
        return 'Registrar Carta de Yu-Gi-Oh';
    }

    /**
     * Redirección tras crear la carta
     * En lugar de ir a la lista, nos quedamos viendo los detalles
     */
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    /**
     * Notificación personalizada al guardar
     */
    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->success()
            ->title('Carta Registrada')
            ->body('La carta ha sido añadida exitosamente al catálogo maestro.');
    }

    /**
     * Hook antes de crear:
     * Aquí podríamos añadir lógica de validación extra si fuera necesario
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Ejemplo: Forzar que el código del set siempre esté en mayúsculas
        $data['set_code'] = strtoupper($data['set_code']);

        return $data;
    }
}
