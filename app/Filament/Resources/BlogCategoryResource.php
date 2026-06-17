<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogCategoryResource\Pages\CreateBlogCategory;
use App\Filament\Resources\BlogCategoryResource\Pages\EditBlogCategory;
use App\Filament\Resources\BlogCategoryResource\Pages\ListBlogCategories;
use App\Models\BlogCategory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BlogCategoryResource extends Resource
{
    protected static ?string $model = BlogCategory::class;

    protected static ?string $navigationGroup = 'Manajemen Konten';
    protected static ?string $navigationLabel = 'Kategori Blog';
    protected static ?string $modelLabel = 'Kategori Blog';
    protected static ?string $pluralModelLabel = 'Kategori Blog';
    protected static ?int $navigationSort = 1;
    // protected static ?string $navigationIcon = 'heroicon-o-tag';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Kategori')
                    ->schema([
                        Forms\Components\TextInput::make('nama_id')
                            ->label('Nama (ID)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('nama_en')
                            ->label('Nama (EN)')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Pengaturan')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('order_column')
            ->columns([
                Tables\Columns\TextColumn::make('nama_id')
                    ->label('Nama (ID)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('nama_en')
                    ->label('Nama (EN)')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('slug_id')
                    ->label('Slug (ID)')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('slug_en')
                    ->label('Slug (EN)')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('blogs_count')
                    ->label('Jumlah Blog')
                    ->counts('blogs')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->since()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Status Aktif'),
                Tables\Filters\TrashedFilter::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\RestoreAction::make(),
                Tables\Actions\ForceDeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\RestoreBulkAction::make(),
                    Tables\Actions\ForceDeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListBlogCategories::route('/'),
            'create' => CreateBlogCategory::route('/create'),
            'edit' => EditBlogCategory::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->orderBy('order_column')
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
