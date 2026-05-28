<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReviewResource\Pages;
use App\Filament\Resources\ReviewResource\RelationManagers;
use App\Models\Review;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Colors\Color;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mokhosh\FilamentRating\Columns\RatingColumn;

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static ?string $navigationGroup = 'Manajemen Pesanan';
    protected static ?string $label = 'Ulasan';

    public static function getNavigationBadgeColor(): string|array|null
    {
        return Color::Blue;
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Ulasan')
                    ->schema([
                        Forms\Components\TextInput::make('user.name')
                            ->label('Nama Pengguna')
                            ->disabled()
                            ->placeholder('-'),
                        Forms\Components\TextInput::make('order.order_number')
                            ->label('Nomor Pesanan')
                            ->disabled()
                            ->placeholder('-'),
                        Forms\Components\TextInput::make('product.name')
                            ->label('Nama Produk')
                            ->disabled()
                            ->placeholder('-'),
                        Forms\Components\TextInput::make('rating')
                            ->label('Rating')
                            ->disabled()
                            ->numeric(),
                    ])->columns(2),
                Forms\Components\Section::make('Edit Ulasan')
                    ->schema([
                        Forms\Components\Textarea::make('comment')
                            ->label('Komentar')
                            ->disabled()
                            ->placeholder('-')
                            ->columnSpanFull(),
                        Forms\Components\Toggle::make('approved')
                            ->label('Setujui Ulasan')
                            ->default(false),
                    ]),
                Forms\Components\Section::make('Gambar Ulasan')
                    ->schema([
                        Forms\Components\FileUpload::make('review_images')
                            ->label('Gambar')
                            ->disk('public')
                            ->visibility('public')
                            ->image()
                            ->multiple()
                            ->reorderable()
                            ->openable()
                            ->directory('reviews')
                            ->panelLayout('grid')
                            ->fetchFileInformation(false),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->placeholder('-')->searchable(),
                Tables\Columns\TextColumn::make('order.order_number')->placeholder('-')->searchable(),
                Tables\Columns\TextColumn::make('product.name')->placeholder('-')->searchable(),
                Tables\Columns\IconColumn::make('approved')
                    ->label('Disetujui')
                    ->boolean()
                    ->alignCenter(),
                RatingColumn::make('rating')->label('Rating')->sortable(),
                TextColumn::make('comment')->label('Komentar')->limit(30)->placeholder('-'),
                TextColumn::make('created_at')->label('Tanggal')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListReviews::route('/'),
            'create' => Pages\CreateReview::route('/create'),
            'view' => Pages\ViewReview::route('/{record}'),
            'edit' => Pages\EditReview::route('/{record}/edit'),
        ];
    }
}
