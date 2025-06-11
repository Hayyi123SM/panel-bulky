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
                    ]),

                Forms\Components\Section::make('Informasi Tambahan')
                    ->schema([
                        Forms\Components\FileUpload::make('pdf_file')
                            ->label('PDF File')
                            ->acceptedFileTypes(['application/pdf'])
                            ->maxSize(150),
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
            ])->inlineLabel()->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('images.0')
                    ->label('Gambar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_trans')
                    ->label('Nama Produk')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Produk')
                    ->prefix('Rp ')
                    ->numeric(0, ',', '.')
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_pallet')
                    ->label('ID Pallet')
                    ->searchable(),
                Tables\Columns\TextColumn::make('total_quantity')
                    ->label('Quantity')
                    ->suffix(' item'),
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
                Tables\Columns\TextColumn::make('productCategory.name')
                    ->label('Kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('productCondition.title')
                    ->label('Kondisi Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('productStatus.status')
                    ->label('Status Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
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
