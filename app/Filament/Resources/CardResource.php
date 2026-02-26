<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardResource\Pages;
use App\Filament\Resources\CardResource\RelationManagers;
use App\Models\Card;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\KeyValue;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CardResource extends Resource
{
    protected static ?string $model = Card::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
        ->schema([
            Forms\Components\Section::make('Información Básica')
                ->schema([
                    TextInput::make('name')->required(),
                    Select::make('game')
                        ->options([
                            'magic' => 'Magic',
                            'pokemon' => 'Pokémon',
                            'yugioh' => 'Yu-Gi-Oh!',
                        ])->required(),
                    TextInput::make('set_name'),
                    TextInput::make('set_code'),
                    TextInput::make('rarity'),
                ])->columns(2),

            Forms\Components\Section::make('Inventario y Precios')
                ->schema([
                    TextInput::make('price')->numeric()->prefix('$'),
                    TextInput::make('stock')->numeric()->default(0),
                ])->columns(2),

            Forms\Components\Section::make('Atributos Específicos')
                ->description('Datos como Maná, HP, Tipo, etc.')
                ->schema([
                    // Usamos KeyValue para manejar el campo JSON 'attributes' de forma sencilla
                    KeyValue::make('attributes')
                        ->keyLabel('Propiedad (Ej: Mana)')
                        ->valueLabel('Valor')
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            TextColumn::make('game')
                ->badge()
                ->color(fn (string $state): string => match ($state) {
                    'magic' => 'primary',
                    'pokemon' => 'warning',
                    'yugioh' => 'danger',
                    default => 'gray',
                }),
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('set_code')->label('Set')->sortable(),
            TextColumn::make('rarity')->sortable(),
            TextColumn::make('price')
                ->money('usd')
                ->sortable()
                ->color('success'),
            TextColumn::make('stock')
                ->numeric()
                ->sortable()
                ->label('Stock Disponible'),
        ])
        ->filters([
            Tables\Filters\SelectFilter::make('game')
                ->options([
                    'magic' => 'Magic: The Gathering',
                    'pokemon' => 'Pokémon',
                    'yugioh' => 'Yu-Gi-Oh!',
                ]),
        ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCards::route('/'),
            'create' => Pages\CreateCard::route('/create'),
            'edit' => Pages\EditCard::route('/{record}/edit'),
        ];
    }
}
