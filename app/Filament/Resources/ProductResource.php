<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use App\Services\Deliveree\Deliveree;
use Filament\Forms;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Support\RawJs;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    use Translatable;

    protected static ?string $model = Product::class;

    protected static ?string $navigationGroup = 'Manajemen Produk';
    protected static ?string $label = 'Produk';
    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Foto Produk')
                    ->schema([
                        FileUpload::make('images')
                            ->label('Gambar Produk')
                            ->required()
                            ->image()
                            ->multiple()
                            ->openable()
                            ->reorderable()
                            ->minFiles(1)
                            ->maxFiles(10)
                            ->directory('products')
                            ->panelLayout('grid')
                    ]),
                Forms\Components\Section::make('Informasi Utama')
                    ->schema([
                        Forms\Components\TextInput::make('name_trans')
                            ->label('Nama Produk')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Product Name'),
                        Forms\Components\TextInput::make('id_pallet')
                            ->label('ID Pallet')
                            ->maxLength(255)
                            ->placeholder('Product Pallet'),
                        Forms\Components\TextInput::make('price_before_discount')
                            ->label('Harga Sebelum Diskon')
                            ->required()
                            ->prefix('Rp ')
                            ->placeholder('Harga Sebelum Diskon')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 0)
                            JS))
                            ->dehydrateStateUsing(fn(string $state) => str($state)->remove('.')->toInteger()),
                        Forms\Components\TextInput::make('price')
                            ->label('Harga Produk')
                            ->required()
                            ->prefix('Rp ')
                            ->placeholder('Harga Produk')
                            ->mask(RawJs::make(<<<'JS'
                                $money($input, ',', '.', 0)
                            JS))
                            ->dehydrateStateUsing(fn(string $state) => str($state)->remove('.')->toInteger()),
                        Forms\Components\TextInput::make('total_quantity')
                            ->label('Total Quantity')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Product Quantity'),
                        Forms\Components\Radio::make('packaging_type')
                            ->label('Tipe Pengemasan')
                            ->options([
                                'palet' => 'Palet',
                                'truck_load' => 'Truck Load',
                                'container' => 'Kontainer',
                            ])
                            ->reactive()
                            ->required(),
                    ]),

                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\FileUpload::make('pdf_file')
                            ->label('PDF File')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(1000),
                        Forms\Components\Select::make('truck_load_vehicle_type_id')
                            ->label('Jenis Kendaraan Truck Load')
                            ->options([
                                2725 => 'CDD Long Liquid8',
                                2704 => 'Fuso Box Liquid8 (8 Tons)',
                                2726 => 'Fuso Pickup Liquid8 (8 Ton)',
                                2727 => 'Fuso Box Liquid8 (10 Ton)',
                                2728 => 'Fuso Pickup Liquid8 (10 Ton)',
                                2723 => 'CDD Box Liquid8',
                                2705 => 'Tronton Wing Box Liquid8',
                                2703 => 'CDE Box Liquid8',
                                2722 => 'CDE Bak Liquid8',
                                2724 => 'CDD Bak Liquid8',
                                2702 => 'Small Pickup Liquid8',
                                2701 => 'Van Liquid8',
                                2719 => 'Mobil Liquid8',
                                2721 => 'Box Kecil Liquid8',
                                2720 => 'Mobil XL Liquid8',
                            ])
                            ->native(false)
                            ->searchable()
                            ->visible(fn($get) => $get('packaging_type') === 'truck_load')
                            ->required(fn($get) => $get('packaging_type') === 'truck_load'),
                        Forms\Components\RichEditor::make('description_trans')
                            ->label('Deskripsi')
                            ->required()
                            ->columnSpanFull()
                            ->placeholder('Enter Product Description'),
                    ]),

                Forms\Components\Section::make('Status dan Kategori')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Diaktifkan')
                            ->default(true),
                        Forms\Components\Select::make('warehouse_id')
                            ->label('Gudang')
                            ->relationship('warehouse', 'name')
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('product_category_id')
                            ->label('Kategori')
                            ->relationship('productCategory', 'name_trans')
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('brands')
                            ->label('Merk')
                            ->relationship('brands', 'name')
                            ->multiple()
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('product_condition_id')
                            ->label('Kondisi Produk')
                            ->relationship('productCondition', 'title')
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('product_status_id')
                            ->label('Status Produk')
                            ->relationship('productStatus', 'status')
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        Forms\Components\Select::make('status_package_id')
                            ->label('Status Paket')
                            ->relationship('statusPackage', 'status')
                            ->required()
                            ->native(false)
                            ->preload()
                            ->searchable(),
                        // Note: value stored as integer 1..100 representing percent
                        Forms\Components\TextInput::make('note_discrepancy')
                            ->label('Catatan perbedaan (%)')
                            ->type('number')
                            ->minValue(1)
                            ->maxValue(100)
                            ->step(1)
                            ->required()
                            ->default(0)
                            ->helperText('Masukkan persentase (1 - 100). Nilai ini akan ditampilkan sebagai persen.')
                            ->suffix('%'),
                        //                        Forms\Components\Select::make('vehicle_type_id')
                        //                            ->label('Jenis Kendaraan')
                        //                            ->options(function (){
                        //                                $vehicles = Deliveree::getVehicleTypes();
                        //                                if (collect($vehicles)->has('error')) {
                        //                                    Notification::make('error_deliveree')
                        //                                        ->title($vehicles['error'])
                        //                                        ->danger()
                        //                                        ->send();
                        //
                        //                                    return [];
                        //                                } else {
                        //                                    return collect($vehicles['data'])
                        //                                        ->when(app()->environment('production'), function ($collection) {
                        //                                            return $collection->filter(function ($item) {
                        //                                                return str_contains($item['name'], 'Liquid8');
                        //                                            });
                        //                                        })
                        //                                        ->mapWithKeys(function ($item) {
                        //                                            return [
                        //                                                $item['id'] => "<span>{$item['name']}</span><br>{$item['cargo_length']} x {$item['cargo_height']} x {$item['cargo_width']}"
                        //                                            ];
                        //                                        });
                        //                                }
                        //                            })
                        //                            ->allowHtml()
                        //                            ->native(false)
                    ]),

                Forms\Components\Section::make('Dimensi & Berat')
                    ->schema([
                        Forms\Components\TextInput::make('length_cm')
                            ->label('Panjang')
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('Panjang dalam cm')
                            ->reactive(),

                        Forms\Components\TextInput::make('width_cm')
                            ->label('Lebar')
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('Lebar dalam cm')
                            ->reactive(),

                        Forms\Components\TextInput::make('height_cm')
                            ->label('Tinggi')
                            ->numeric()
                            ->suffix('cm')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('Tinggi dalam cm')
                            ->reactive(),

                        Forms\Components\TextInput::make('weight_kg')
                            ->label('Berat')
                            ->numeric()
                            ->suffix('kg')
                            ->step(0.01)
                            ->minValue(0)
                            ->placeholder('Berat dalam kg')
                            ->reactive(),

                        Forms\Components\Placeholder::make('volume_m3')
                            ->label('Volume (m³)')
                            ->content(fn($get) => ($get('length_cm') && $get('width_cm') && $get('height_cm')) ? number_format((($get('length_cm') * $get('width_cm') * $get('height_cm')) / 1000000), 6, ',', '.') . ' m³' : '-')
                            ->columnSpanFull()
                            ->helperText('Volume dihitung otomatis dari panjang × lebar × tinggi (cm).'),
                    ])->columns(2),
            ])->inlineLabel()->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.0')
                    ->label('Gambar')
                    ->searchable()
                    ->rounded(),

                Tables\Columns\TextColumn::make('name_trans')
                    ->label('Nama Produk')
                    ->searchable(query: function ($query, $search) {
                        $query->whereRaw('LOWER(name_trans) LIKE ?', ['%' . strtolower($search) . '%']);
                    })
                    ->sortable()
                    ->wrap(),

                Tables\Columns\TextColumn::make('productCategory.name')
                    ->label('Kategori')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('productStatus.status')
                    ->label('Status')
                    ->colors([
                        'danger' => fn($state) => strtolower($state) === 'unavailable' || strtolower($state) === 'inactive',
                        'warning' => fn($state) => strtolower($state) === 'pending',
                        'success' => fn($state) => strtolower($state) === 'available' || strtolower($state) === 'active',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('statusPackage.status')
                    ->label('Status Paket')
                    ->colors([
                        'danger' => fn($state) => strtolower($state) === 'unavailable' || strtolower($state) === 'inactive',
                        'warning' => fn($state) => strtolower($state) === 'pending',
                        'success' => fn($state) => strtolower($state) === 'available' || strtolower($state) === 'active',
                    ])
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('note_discrepancy')
                    ->label('Catatan perbedaan')
                    ->formatStateUsing(fn($state) => ($state ?? 0) . '%')
                    ->colors([
                        'danger' => fn($state) => $state !== null && $state <= 30,
                        'warning' => fn($state) => $state !== null && $state > 30 && $state < 70,
                        'success' => fn($state) => $state !== null && $state >= 70,
                    ])
                    ->sortable(),

                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Produk')
                    ->formatStateUsing(fn($state) => 'Rp ' . number_format($state, 0, ',', '.'))
                    ->sortable(),

                Tables\Columns\TextColumn::make('weight_kg')
                    ->label('Berat')
                    ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 2, ',', '.') . ' kg' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('volume_m3')
                    ->label('Volume')
                    ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 6, ',', '.') . ' m³' : '-')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Quantity')
                    ->suffix(' item')
                    ->sortable(),

                Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Diaktifkan')
                    ->onIcon('heroicon-o-check')
                    ->onColor('success')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\IconColumn::make('sold_out')
                    ->label('Terjual')
                    ->boolean()
                    ->alignCenter(),

                Tables\Columns\ToggleColumn::make('is_new')
                    ->label('Produk Baru')
                    ->onIcon('heroicon-o-check')
                    ->onColor('success')
                    ->alignCenter()
                    ->sortable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
