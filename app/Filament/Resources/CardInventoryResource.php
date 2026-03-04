<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CardInventoryResource\Pages;
use App\Models\CardCondition;
use App\Models\CardEdition;
use App\Models\CardInventory;
use App\Models\Customer;
use App\Models\YgoCard;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CardInventoryResource extends Resource
{
    protected static ?string $model = CardInventory::class;

    protected static ?string $navigationIcon      = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel     = 'Inventario de Cartas';
    protected static ?string $navigationGroup     = 'YuGiOh!';
    protected static ?string $slug                = 'yugioh/inventario-cartas';
    protected static ?string $recordTitleAttribute = 'sku';

    // ── FORMULARIO ────────────────────────────────────────────────────────────
    public static function form(Form $form): Form
    {
        return $form->schema([

            // ── Toggle: modo de ingreso ───────────────────────────────────────
            Forms\Components\Section::make()
                ->schema([
                    Forms\Components\Toggle::make('is_manual_entry')
                        ->label('Ingresar carta manualmente (no está en el catálogo)')
                        ->helperText('Activa esto si la carta no aparece en la búsqueda del catálogo.')
                        ->live()
                        ->afterStateUpdated(function (Set $set, bool $state) {
                            // Al cambiar de modo, limpiamos los campos del otro modo
                            if ($state) {
                                $set('ygo_card_id', null);
                                $set('_catalog_name', null);
                                $set('_catalog_image', null);
                            } else {
                                $set('manual_name', null);
                                $set('manual_set_code', null);
                                $set('manual_rarity', null);
                                $set('manual_type', null);
                                $set('manual_attribute', null);
                                $set('manual_atk', null);
                                $set('manual_def', null);
                                $set('manual_description', null);
                                $set('manual_image_url', null);
                            }
                        })
                        ->default(false),
                ])
                ->extraAttributes(['style' => 'background: transparent; box-shadow: none; padding: 0;']),

            // ── MODO CATÁLOGO ─────────────────────────────────────────────────
            Forms\Components\Section::make('Carta del Catálogo')
                ->description('Selecciona la carta. Los datos se importan automáticamente.')
                ->icon('heroicon-o-magnifying-glass')
                ->visible(fn (Get $get): bool => !$get('is_manual_entry'))
                ->schema([

                    Forms\Components\Select::make('ygo_card_id')
                        ->label('Buscar carta')
                        ->options(YgoCard::query()->pluck('name', 'id'))
                        ->searchable()
                        ->required(fn (Get $get): bool => !$get('is_manual_entry'))
                        ->live()
                        ->afterStateUpdated(function (Set $set, ?int $state) {
                            if (!$state) return;
                            $card = YgoCard::find($state);
                            if (!$card) return;

                            $set('_catalog_name',        $card->name);
                            $set('_catalog_set_code',    $card->set_code);
                            $set('_catalog_rarity',      $card->rarity);
                            $set('_catalog_type',        $card->type);
                            $set('_catalog_attribute',   $card->attribute ?? '—');
                            $set('_catalog_atk',         $card->atk ?? '—');
                            $set('_catalog_def',         $card->def ?? '—');
                            $set('_catalog_description', $card->description);
                            $set('_catalog_image',       $card->image_path);
                            $set('market_price_snapshot', $card->market_price);
                            $set('selling_price',         $card->market_price);
                        }),

                    // Vista previa del catálogo
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Placeholder::make('_catalog_image')
                            ->label('Imagen')
                            ->content(fn (Get $get): \Illuminate\Support\HtmlString =>
                                new \Illuminate\Support\HtmlString(
                                    $get('_catalog_image')
                                        ? '<img src="' . e($get('_catalog_image')) . '" style="height:160px;border-radius:8px;">'
                                        : '<span style="color:#9ca3af">Sin imagen</span>'
                                )
                            ),

                        Forms\Components\Grid::make(1)->schema([
                            Forms\Components\Placeholder::make('_catalog_name')
                                ->label('Nombre')
                                ->content(fn (Get $get) => $get('_catalog_name') ?? '—'),
                            Forms\Components\Placeholder::make('_catalog_set_code')
                                ->label('Set / Rareza')
                                ->content(fn (Get $get) =>
                                    ($get('_catalog_set_code') ?? '—') . '  ·  ' . ($get('_catalog_rarity') ?? '—')
                                ),
                            Forms\Components\Placeholder::make('_catalog_type')
                                ->label('Tipo / Atributo')
                                ->content(fn (Get $get) =>
                                    ($get('_catalog_type') ?? '—') . '  ·  ' . ($get('_catalog_attribute') ?? '—')
                                ),
                            Forms\Components\Placeholder::make('_catalog_stats')
                                ->label('ATK / DEF')
                                ->content(fn (Get $get) =>
                                    'ATK: ' . ($get('_catalog_atk') ?? '—') . '  /  DEF: ' . ($get('_catalog_def') ?? '—')
                                ),
                        ]),
                    ]),

                    Forms\Components\Placeholder::make('_catalog_description')
                        ->label('Efecto / Descripción')
                        ->content(fn (Get $get) => $get('_catalog_description') ?? '—')
                        ->columnSpanFull(),
                ]),

            // ── MODO MANUAL ───────────────────────────────────────────────────
            Forms\Components\Section::make('Datos de la Carta (ingreso manual)')
                ->description('Completa los datos de la carta ya que no se encontró en el catálogo.')
                ->icon('heroicon-o-pencil-square')
                ->visible(fn (Get $get): bool => (bool) $get('is_manual_entry'))
                ->columns(3)
                ->schema([

                    Forms\Components\TextInput::make('manual_name')
                        ->label('Nombre de la carta')
                        ->required(fn (Get $get): bool => (bool) $get('is_manual_entry'))
                        ->columnSpan(2),

                    Forms\Components\TextInput::make('manual_set_code')
                        ->label('Código del Set (ej: LOB-001)')
                        ->placeholder('LOB-001'),

                    Forms\Components\TextInput::make('manual_rarity')
                        ->label('Rareza')
                        ->placeholder('Ultra Rare, Common...'),

                    Forms\Components\TextInput::make('manual_type')
                        ->label('Tipo')
                        ->placeholder('Dragon / Effect Monster...'),

                    Forms\Components\TextInput::make('manual_attribute')
                        ->label('Atributo')
                        ->placeholder('DARK, LIGHT, EARTH...'),

                    Forms\Components\TextInput::make('manual_atk')
                        ->label('ATK')
                        ->numeric()
                        ->placeholder('2500'),

                    Forms\Components\TextInput::make('manual_def')
                        ->label('DEF')
                        ->numeric()
                        ->placeholder('2100'),

                    Forms\Components\TextInput::make('manual_image_url')
                        ->label('URL de imagen (opcional)')
                        ->url()
                        ->placeholder('https://...')
                        ->columnSpan(3),

                    Forms\Components\Textarea::make('manual_description')
                        ->label('Efecto / Descripción')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),

            // ── Características del ejemplar físico ───────────────────────────
            Forms\Components\Section::make('Características del Ejemplar')
                ->icon('heroicon-o-identification')
                ->columns(3)
                ->schema([

                    Forms\Components\Select::make('card_condition_id')
                        ->label('Condición')
                        ->options(CardCondition::query()->pluck('description', 'id'))
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('card_edition_id')
                        ->label('Edición')
                        ->options(CardEdition::query()->pluck('description', 'id'))
                        ->required()
                        ->searchable(),

                    Forms\Components\Select::make('language')
                        ->label('Idioma')
                        ->options([
                            'Español'   => 'Español',
                            'Inglés'    => 'Inglés',
                            'Japonés'   => 'Japonés',
                            'Portugués' => 'Portugués',
                            'Francés'   => 'Francés',
                            'Alemán'    => 'Alemán',
                            'Italiano'  => 'Italiano',
                            'Coreano'   => 'Coreano',
                        ])
                        ->default('Español')
                        ->required(),

                    Forms\Components\TextInput::make('quantity')
                        ->label('Cantidad')
                        ->numeric()
                        ->default(1)
                        ->minValue(1)
                        ->required(),

                    Forms\Components\FileUpload::make('custom_image_path')
                        ->label('Imagen propia del ejemplar')
                        ->helperText('Opcional. Sube una foto de la carta física.')
                        ->image()
                        ->disk('public')
                        ->directory('card-inventory')
                        ->columnSpan(2),
                ]),

            // ── Precios ───────────────────────────────────────────────────────
            Forms\Components\Section::make('Precios')
                ->icon('heroicon-o-currency-dollar')
                ->columns(3)
                ->schema([

                    Forms\Components\TextInput::make('price_at_purchase')
                        ->label('Precio de adquisición (USD)')
                        ->numeric()
                        ->prefix('$')
                        ->placeholder('0.00'),

                    Forms\Components\TextInput::make('market_price_snapshot')
                        ->label('Precio de mercado al registrar (USD)')
                        ->numeric()
                        ->prefix('$')
                        ->helperText('Auto-capturado del catálogo. En modo manual puedes ingresarlo.')
                        ->readOnly(fn (Get $get): bool => !$get('is_manual_entry')),

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
                        ->label('Cliente origen (¿De quién se compró?)')
                        ->options(Customer::query()->pluck('name', 'id'))
                        ->searchable()
                        ->nullable()
                        ->placeholder('Compra directa / Sin cliente'),

                    Forms\Components\Placeholder::make('registered_by_label')
                        ->label('Registrado por')
                        ->content(fn () => auth()->user()?->name ?? '—'),

                    Forms\Components\Textarea::make('notes')
                        ->label('Observaciones del ejemplar')
                        ->placeholder('Rasguño en la esquina inferior, foil en buen estado...')
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

                Tables\Columns\ImageColumn::make('card.image_path')
                    ->label('')
                    ->disk('url')
                    ->width(40)
                    ->height(56)
                    ->defaultImageUrl(url('/images/card-placeholder.png')),

                // Nombre: usa accessor unificado (catálogo o manual)
                Tables\Columns\TextColumn::make('effective_name')
                    ->label('Carta')
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($q) use ($search) {
                            $q->whereHas('card', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                              ->orWhere('manual_name', 'like', "%{$search}%");
                        });
                    })
                    ->description(fn (CardInventory $r): string =>
                        ($r->effective_set_code ?? '') . ' · ' . ($r->effective_rarity ?? '')
                    ),

                // Badge de ingreso manual
                Tables\Columns\IconColumn::make('is_manual_entry')
                    ->label('Manual')
                    ->boolean()
                    ->trueIcon('heroicon-o-pencil-square')
                    ->falseIcon('heroicon-o-check-circle')
                    ->trueColor('warning')
                    ->falseColor('success')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('sku')
                    ->label('SKU')
                    ->searchable()
                    ->badge()
                    ->color('gray')
                    ->fontFamily('mono'),

                Tables\Columns\TextColumn::make('condition.code')
                    ->label('Condición')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'NM'  => 'success',
                        'LP'  => 'info',
                        'MP'  => 'warning',
                        'HP', 'DMG' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('edition.code')
                    ->label('Edición')
                    ->badge()
                    ->color('primary'),

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

                Tables\Columns\TextColumn::make('card.market_price')
                    ->label('Mercado actual')
                    ->money('USD')
                    ->sortable()
                    ->placeholder('N/A')
                    ->color(fn (CardInventory $r): string =>
                        (float) ($r->card?->market_price ?? 0) > (float) ($r->price_at_purchase ?? 0)
                            ? 'success' : 'danger'
                    ),

                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Mi precio')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('estimated_margin')
                    ->label('Margen est.')
                    ->getStateUsing(fn (CardInventory $r): string =>
                        is_null($r->estimated_margin)
                            ? '—'
                            : ($r->estimated_margin >= 0 ? '+' : '') . '$' . number_format($r->estimated_margin, 2)
                    )
                    ->color(fn (CardInventory $r): string =>
                        ($r->estimated_margin ?? 0) >= 0 ? 'success' : 'danger'
                    )
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente origen')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('registeredBy.name')
                    ->label('Registrado por')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])

            ->filters([
                Tables\Filters\SelectFilter::make('card_condition_id')
                    ->label('Condición')
                    ->relationship('condition', 'description'),

                Tables\Filters\SelectFilter::make('card_edition_id')
                    ->label('Edición')
                    ->relationship('edition', 'description'),

                Tables\Filters\SelectFilter::make('language')
                    ->label('Idioma')
                    ->options([
                        'Español' => 'Español',
                        'Inglés'  => 'Inglés',
                        'Japonés' => 'Japonés',
                    ]),

                Tables\Filters\TernaryFilter::make('is_manual_entry')
                    ->label('Tipo de ingreso')
                    ->trueLabel('Solo manuales')
                    ->falseLabel('Solo del catálogo')
                    ->placeholder('Todos'),
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
            'index'  => Pages\ListCardInventory::route('/'),
            'create' => Pages\CreateCardInventory::route('/create'),
            'edit'   => Pages\EditCardInventory::route('/{record}/edit'),
        ];
    }
}
