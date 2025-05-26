<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductCategoryResource\Pages;
use App\Filament\Resources\ProductCategoryResource\RelationManagers;
use App\Models\ProductCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductCategoryResource extends Resource
{
    use Translatable;

    protected static ?string $model = ProductCategory::class;

    protected static ?string $navigationGroup = 'Manajemen Produk';
    protected static ?string $label = 'Kategori';
    protected static ?int $navigationSort = 5;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\FileUpload::make('icon')
                    ->nullable()
                    ->disk('public')
                    ->image()
                    ->directory('product-categories'),
                Forms\Components\TextInput::make('name_trans')
                    ->label('Nama Kategori')
                    ->required()
                    ->maxLength(255)
                    ->live(true),
                Forms\Components\Select::make('coupons')
                    ->label('Kupon')
                    ->relationship('coupons', 'code')
                    ->multiple()
                    ->searchable()
                    ->preload(),
                Forms\Components\TextInput::make('wms_id')
                    ->nullable()
                    ->label('WMS ID')
                    ->unique(ignoreRecord: true)
            ])->inlineLabel()->columns(1);
    }

    /**
     * @throws \Exception
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('icon')
                    ->placeholder('-')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('name_trans')
                    ->label('Nama Kategori')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\TextColumn::make('products_count')
                    ->counts('products')
                    ->label('Jumlah Produk')
                    ->suffix(' Produk')
                    ->alignCenter(),
                Tables\Columns\TextInputColumn::make('wms_id')
                    ->label('WMS ID')
                    ->width('50px'),
                Tables\Columns\TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageProductCategories::route('/'),
            'create' => Pages\CreateProductCategory::route('/create'),
            'edit' => Pages\EditProductCategory::route('/{record}/edit'),
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
