<?php

namespace App\Filament\Resources;

use App\Filament\Resources\YgoCardResource\Pages;
use App\Models\YgoCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class YgoCardResource extends Resource
{
    protected static ?string $model = YgoCard::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $navigationLabel = 'Catalogo';

    protected static ?string $navigationGroup = 'YuGiOh!';

    protected static ?string $slug = 'yugioh/catalogo';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'konami_id', 'set_code'];
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('konami_id')
                    ->label('ID de Konami')
                    ->required(),
                Forms\Components\TextInput::make('name')
                    ->label('Nombre de la Carta')
                    ->required(),
                Forms\Components\TextInput::make('set_code')
                    ->label('Codigo del Set'),
                Forms\Components\TextInput::make('rarity')
                    ->label('Rareza'),
                Forms\Components\Textarea::make('description')
                    ->label('Descripcion')
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Imagen')
                    ->disk('url')
                    ->width(50)
                    ->height(70),

                Tables\Columns\TextColumn::make('name')
                    ->label('Nombre de la Carta')
                    ->searchable()
                    ->sortable()
                    ->description(fn (YgoCard $record): string => "Rareza: {$record->rarity}"),

                Tables\Columns\TextColumn::make('set_code')
                    ->label('Set Original')
                    ->searchable()
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('market_price')
                    ->label('TCGPlayer ($)')
                    ->money('USD')
                    ->sortable()
                    ->color(fn ($state): string => (float) $state > 20 ? 'success' : 'gray')
                    ->weight(fn ($state): string => (float) $state > 50 ? 'bold' : 'normal'),

                Tables\Columns\TextColumn::make('rarity')
                    ->label('Rareza')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->label('Ver Detalle'),
            ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListYgoCards::route('/'),
            'create' => Pages\CreateYgoCard::route('/create'),
            'edit'   => Pages\EditYgoCard::route('/{record}/edit'),
        ];
    }

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make()
                    ->schema([
                        \Filament\Infolists\Components\ImageEntry::make('image_path')
                            ->label(false)
                            ->disk('url')
                            ->height(400)
                            ->alignCenter(),

                        \Filament\Infolists\Components\Grid::make(3)
                            ->schema([
                                \Filament\Infolists\Components\TextEntry::make('name')->label('Nombre'),
                                \Filament\Infolists\Components\TextEntry::make('konami_id')->label('ID Konami'),
                                \Filament\Infolists\Components\TextEntry::make('set_code')->label('Set'),
                            ]),

                        \Filament\Infolists\Components\TextEntry::make('description')
                            ->label('Efecto / Descripcion')
                            ->columnSpanFull(),

                        \Filament\Infolists\Components\Actions::make([
                            \Filament\Infolists\Components\Actions\Action::make('addToInventory')
                                ->label('Agregar al Inventario')
                                ->icon('heroicon-m-plus-circle')
                                ->color('success')
                                ->url(fn ($record): string =>
                                    \App\Filament\Resources\CardInventoryResource::getUrl('create', [
                                        'card_id' => $record->id,
                                    ])
                                ),
                        ])->alignCenter(),
                    ]),
            ]);
    }
}
