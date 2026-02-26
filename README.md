# MTG Database Explorer

Buscador de cartas de Magic: The Gathering con más de 540,000 registros.

## Características
- Importación eficiente mediante JSON Streaming (evita errores de memoria).
- Buscador filtrable por nombre, set y rareza.
- Integración con la API de Scryfall para visualización de imágenes.

## Instalación
1. Clonar el repo.
2. `composer install`.
3. Descargar `AllPrintings.json` de MTGJSON y colocarlo en `storage/app/magic_cards.json`.
4. Ejecutar `php artisan import:magic`.
