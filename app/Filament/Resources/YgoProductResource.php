<?php

namespace App\Filament\Resources;

use App\Filament\Resources\YgoProductResource\Pages;
use App\Models\ProductType;
use App\Models\YgoProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class YgoProductResource extends Resource
{
    protected static ?string $model = YgoProduct::class;

    protected static ?string $navigationIcon      = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel     = 'Catalogo de Productos';
    protected static ?string $navigationGroup     = 'YuGiOh!';
    protected static ?string $slug                = 'yugioh/productos';
    protected static ?string $recordTitleAttribute = 'name';

    public static function getGloballySearchableAttributes(): array
    {
        return ['name'];
    }

    // ── FORMULARIO (crear / editar producto en el catalogo) ───────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([

            Forms\Components\Section::make('Informacion del Producto')
                ->icon('heroicon-o-shopping-bag')
                ->columns(2)
                ->schema([

                    Forms\Components\TextInput::make('name')
                        ->label('Nombre del producto')
                        ->required()
                        ->placeholder('Legend of Blue Eyes White Dragon Booster Box')
                        ->columnSpanFull(),

                    Forms\Components\Select::make('product_type_id')
                        ->label('Tipo de producto')
                        ->options(ProductType::query()->pluck('description', 'id'))
                        ->searchable()
                        ->required(),

                    Forms\Components\TextInput::make('market_price')
                        ->label('Precio de mercado (USD)')
                        ->numeric()
                        ->prefix('$')
                        ->placeholder('0.00'),

                    Forms\Components\Textarea::make('description')
                        ->label('Descripcion')
                        ->rows(3)
                        ->columnSpanFull(),

                    Forms\Components\TextInput::make('image_url')
                        ->label('URL de imagen')
                        ->url()
                        ->placeholder('https://...')
                        ->columnSpanFull(),
                ]),
        ]);
    }

    // ── TABLA ─────────────────────────────────────────────────────────────────
    public static function table(Table $table): Table
    {
        return $table
            ->deferLoading()
            ->striped()
            ->columns([

                Tables\Columns\ImageColumn::make('image_url')
                    ->label('')
                    ->disk('url')
                    ->width(60)
                    ->height(60)
                    ->defaultImageUrl(url('/images/product-placeholder.png')),

                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->description(fn (YgoProduct $r): string => $r->type?->description ?? ''),

                Tables\Columns\TextColumn::make('type.code')
                    ->label('Tipo')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('market_price')
                    ->label('Precio mercado')
                    ->money('USD')
                    ->sortable()
                    ->color(fn ($state): string => (float) $state > 50 ? 'success' : 'gray'),

                Tables\Columns\TextColumn::make('total_stock')
                    ->label('En stock')
                    ->getStateUsing(fn (YgoProduct $r): int => $r->total_stock)
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('product_type_id')
                    ->label('Tipo')
                    ->relationship('type', 'description'),
            ])

            ->actions([
                Tables\Actions\ViewAction::make()->label('Ver'),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    // ── DETALLE (infolist) ────────────────────────────────────────────────────
    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make()
                ->schema([
                    Infolists\Components\ImageEntry::make('image_url')
                        ->label(false)
                        ->disk('url')
                        ->height(300)
                        ->alignCenter(),

                    Infolists\Components\Grid::make(3)->schema([
                        Infolists\Components\TextEntry::make('name')
                            ->label('Nombre'),
                        Infolists\Components\TextEntry::make('type.description')
                            ->label('Tipo'),
                        Infolists\Components\TextEntry::make('market_price')
                            ->label('Precio de mercado')
                            ->money('USD'),
                    ]),

                    Infolists\Components\TextEntry::make('description')
                        ->label('Descripcion')
                        ->columnSpanFull(),

                    Infolists\Components\Actions::make([
                        Infolists\Components\Actions\Action::make('addToInventory')
                            ->label('Agregar al Inventario')
                            ->icon('heroicon-m-plus-circle')
                            ->color('success')
                            ->url(fn ($record): string =>
                                \App\Filament\Resources\ProductInventoryResource::getUrl('create', [
                                    'product_id' => $record->id,
                                ])
                            ),
                    ])->alignCenter(),
                ]),
        ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListYgoProducts::route('/'),
            'create' => Pages\CreateYgoProduct::route('/create'),
            'edit'   => Pages\EditYgoProduct::route('/{record}/edit'),
        ];
    }
}
