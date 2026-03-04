<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductInventoryResource\Pages;
use App\Models\Customer;
use App\Models\ProductInventory;
use App\Models\YgoProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ProductInventoryResource extends Resource
{
    protected static ?string $model = ProductInventory::class;

    protected static ?string $navigationIcon      = 'heroicon-o-archive-box-arrow-down';
    protected static ?string $navigationLabel     = 'Inventario de Productos';
    protected static ?string $navigationGroup     = 'YuGiOh!';
    protected static ?string $slug                = 'yugioh/inventario-productos';
    protected static ?string $recordTitleAttribute = 'sku';

    // ── FORMULARIO ────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Seleccion del producto ────────────────────────────────────────
            Forms\Components\Section::make('Producto')
                ->description('Selecciona el producto del catalogo. Los datos se importan automaticamente.')
                ->icon('heroicon-o-shopping-bag')
                ->schema([

                    Forms\Components\Select::make('ygo_product_id')
                        ->label('Buscar producto')
                        ->options(YgoProduct::query()->with('type')->get()->mapWithKeys(fn ($p) =>
                            [$p->id => "[{$p->type?->code}] {$p->name}"]
                        ))
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?int $state) {
                            if (!$state) return;
                            $product = YgoProduct::with('type')->find($state);
                            if (!$product) return;

                            $set('_product_name',        $product->name);
                            $set('_product_type',        $product->type?->description ?? '-');
                            $set('_product_description', $product->description);
                            $set('_product_image',       $product->image_url);
                            $set('market_price_snapshot', $product->market_price);
                            $set('selling_price',         $product->market_price);
                        }),

                    // Vista previa del producto
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Placeholder::make('_product_image')
                            ->label('Imagen')
                            ->content(fn (Get $get): \Illuminate\Support\HtmlString =>
                                new \Illuminate\Support\HtmlString(
                                    $get('_product_image')
                                        ? '<img src="' . e($get('_product_image')) . '" style="height:140px;border-radius:8px;object-fit:contain;">'
                                        : '<span style="color:#9ca3af">Sin imagen</span>'
                                )
                            ),

                        Forms\Components\Grid::make(1)->schema([
                            Forms\Components\Placeholder::make('_product_name')
                                ->label('Nombre')
                                ->content(fn (Get $get) => $get('_product_name') ?? '-'),
                            Forms\Components\Placeholder::make('_product_type')
                                ->label('Tipo')
                                ->content(fn (Get $get) => $get('_product_type') ?? '-'),
                            Forms\Components\Placeholder::make('_product_description')
                                ->label('Descripcion')
                                ->content(fn (Get $get) => $get('_product_description') ?? '-'),
                        ]),
                    ]),
                ]),

            // ── Caracteristicas del lote ──────────────────────────────────────
            Forms\Components\Section::make('Caracteristicas del Lote')
                ->icon('heroicon-o-cube')
                ->columns(3)
                ->schema([

                    Forms\Components\Select::make('condition')
                        ->label('Estado del producto')
                        ->options([
                            'Sellado' => 'Sellado',
                            'Abierto' => 'Abierto',
                            'Danado'  => 'Danado',
                        ])
                        ->default('Sellado')
                        ->required(),

                    Forms\Components\Select::make('language')
                        ->label('Idioma')
                        ->options([
                            'Espanol'   => 'Espanol',
                            'Ingles'    => 'Ingles',
                            'Japones'   => 'Japones',
                            'Portugues' => 'Portugues',
                            'Frances'   => 'Frances',
                            'Aleman'    => 'Aleman',
                        ])
                        ->default('Espanol')
                        ->required(),

                    Forms\Components\TextInput::make('quantity')
                        ->label('Cantidad')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required(),

                    Forms\Components\FileUpload::make('custom_image_path')
                        ->label('Imagen propia del lote (opcional)')
                        ->helperText('Si esta vacio se usa la imagen del catalogo.')
                        ->image()
                        ->disk('public')
                        ->directory('product-inventory')
                        ->columnSpan(2),
                ]),

            // ── Precios ───────────────────────────────────────────────────────
            Forms\Components\Section::make('Precios')
                ->icon('heroicon-o-currency-dollar')
                ->columns(3)
                ->schema([

                    Forms\Components\TextInput::make('price_at_purchase')
                        ->label('Precio de adquisicion (USD)')
                        ->numeric()
                        ->prefix('$')
                        ->placeholder('0.00'),

                    Forms\Components\TextInput::make('market_price_snapshot')
                        ->label('Precio de mercado al registrar (USD)')
                        ->numeric()
                        ->prefix('$')
                        ->helperText('Auto-capturado del catalogo al seleccionar el producto.')
                        ->readOnly(),

                    Forms\Components\TextInput::make('selling_price')
                        ->label('Mi precio de venta (USD)')
                        ->numeric()
                        ->prefix('$')
                        ->placeholder('0.00'),
                ]),

            // ── Trazabilidad ──────────────────────────────────────────────────
            Forms\Components\Section::make('Trazabilidad')
                ->icon('heroicon-o-user-circle')
                ->columns(2)
                ->schema([

                    Forms\Components\Select::make('customer_id')
                        ->label('Cliente origen (De quien se compro?)')
                        ->options(Customer::query()->pluck('name', 'id'))
                        ->searchable()
                        ->nullable()
                        ->placeholder('Compra directa / Sin cliente'),

                    Forms\Components\Placeholder::make('registered_by_label')
                        ->label('Registrado por')
                        ->content(fn () => auth()->user()?->name ?? '-'),

                    Forms\Components\Textarea::make('notes')
                        ->label('Observaciones')
                        ->placeholder('Caja en buen estado, sin abolladuras...')
                        ->rows(3)
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

                Tables\Columns\ImageColumn::make('product.image_url')
                    ->label('')
                    ->disk('url')
                    ->width(50)
                    ->height(50)
                    ->defaultImageUrl(url('/images/product-placeholder.png')),

                Tables\Columns\TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable()
                    ->description(fn (ProductInventory $r): string =>
                        $r->product?->type?->description ?? ''
                    ),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('condition')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'Sellado' => 'success',
                        'Abierto' => 'warning',
                        'Danado'  => 'danger',
                        default   => 'gray',
                    }),

                Tables\Columns\TextColumn::make('language')
                    ->label('Idioma')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Stock')
                    ->sortable()
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'success' : 'danger'),

                Tables\Columns\TextColumn::make('price_at_purchase')
                    ->label('Compra')
                    ->money('USD')
                    ->sortable()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('product.market_price')
                    ->label('Mercado actual')
                    ->money('USD')
                    ->sortable()
                    ->placeholder('N/A')
                    ->color(fn (ProductInventory $r): string =>
                        (float) ($r->product?->market_price ?? 0) > (float) ($r->price_at_purchase ?? 0)
                            ? 'success' : 'danger'
                    ),

                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Mi precio')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('estimated_margin')
                    ->label('Margen est.')
                    ->getStateUsing(fn (ProductInventory $r): string =>
                        is_null($r->estimated_margin)
                            ? '-'
                            : ($r->estimated_margin >= 0 ? '+' : '') . '$' . number_format($r->estimated_margin, 2)
                    )
                    ->color(fn (ProductInventory $r): string =>
                        ($r->estimated_margin ?? 0) >= 0 ? 'success' : 'danger'
                    )
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente origen')
                    ->placeholder('-')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('registeredBy.name')
                    ->label('Registrado por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('condition')
                    ->label('Estado')
                    ->options([
                        'Sellado' => 'Sellado',
                        'Abierto' => 'Abierto',
                        'Danado'  => 'Danado',
                    ]),

                Tables\Filters\SelectFilter::make('ygo_product_id')
                    ->label('Producto')
                    ->relationship('product', 'name')
                    ->searchable(),
            ])

            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array { return []; }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProductInventory::route('/'),
            'create' => Pages\CreateProductInventory::route('/create'),
            'edit'   => Pages\EditProductInventory::route('/{record}/edit'),
        ];
    }
}
