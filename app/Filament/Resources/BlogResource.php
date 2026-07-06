<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BlogResource\Pages\CreateBlog;
use App\Filament\Resources\BlogResource\Pages\EditBlog;
use App\Filament\Resources\BlogResource\Pages\ListBlogs;
use App\Models\Blog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BlogResource extends Resource
{
    protected static ?string $model = Blog::class;

    protected static ?string $navigationGroup = 'Manajemen Konten';
    protected static ?string $navigationLabel = 'Blog';
    protected static ?string $modelLabel = 'Blog';
    protected static ?string $pluralModelLabel = 'Blog';
    protected static ?int $navigationSort = 2;
    // protected static ?string $navigationIcon = 'heroicon-o-document-text';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Konten Utama')
                    ->schema([
                        Forms\Components\TextInput::make('judul_id')
                            ->label('Judul (ID)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\TextInput::make('judul_en')
                            ->label('Judul (EN)')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('kategori_id')
                            ->label('Kategori')
                            ->relationship('kategori', 'nama_id')
                            ->required()
                            ->searchable()
                            ->preload(),
                        Forms\Components\TextInput::make('featured_image_url')
                            ->label('Featured Image URL')
                            ->url()
                            ->maxLength(2048),
                        Forms\Components\RichEditor::make('konten_id')
                            ->label('Konten (ID)')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\RichEditor::make('konten_en')
                            ->label('Konten (EN)')
                            ->required()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('highlight_id')
                            ->label('Highlight (ID)')
                            ->rows(3)
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('highlight_en')
                            ->label('Highlight (EN)')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('SEO')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title_id')
                            ->label('Meta Title (ID)')
                            ->maxLength(255),
                        Forms\Components\TextInput::make('meta_title_en')
                            ->label('Meta Title (EN)')
                            ->maxLength(255),
                        Forms\Components\Textarea::make('meta_description_id')
                            ->label('Meta Description (ID)')
                            ->rows(3),
                        Forms\Components\Textarea::make('meta_description_en')
                            ->label('Meta Description (EN)')
                            ->rows(3),
                        Forms\Components\TextInput::make('meta_keywords')
                            ->label('Meta Keywords')
                            ->maxLength(255)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Publikasi')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(false),
                        Forms\Components\DateTimePicker::make('published_at')
                            ->label('Tanggal Publish')
                            ->seconds(false),
                        Forms\Components\TextInput::make('view_count')
                            ->label('View Count')
                            ->numeric()
                            ->default(0)
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('judul_id')
                    ->label('Judul (ID)')
                    ->searchable()
                    ->limit(50),
                Tables\Columns\TextColumn::make('judul_en')
                    ->label('Judul (EN)')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),
                Tables\Columns\TextColumn::make('kategori.nama_id')
                    ->label('Kategori')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('view_count')
                    ->label('Views')
                    ->sortable()
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('published_at')
                    ->label('Published At')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Terakhir Diubah')
                    ->since()
                    ->sortable(),
            ])
            ->defaultSort('updated_at', 'desc')
            ->filters([
                Tables\Filters\SelectFilter::make('kategori_id')
                    ->label('Kategori')
                    ->relationship('kategori', 'nama_id'),
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
            'index' => ListBlogs::route('/'),
            'create' => CreateBlog::route('/create'),
            'edit' => EditBlog::route('/{record}/edit'),
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
