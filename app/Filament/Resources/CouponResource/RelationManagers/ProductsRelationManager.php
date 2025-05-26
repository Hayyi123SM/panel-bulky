<?php

namespace App\Filament\Resources\CouponResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';
    protected static ?string $title = 'Produk';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name_trans')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name_trans')
            ->columns([
                Tables\Columns\ImageColumn::make('images.0')
                    ->label('Gambar')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name_trans')
                    ->label('Nama Produk')
                    ->searchable(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga Produk')
                    ->prefix('Rp ')
                    ->numeric(0,',','.')
                    ->sortable(),
                Tables\Columns\TextColumn::make('id_pallet')
                    ->label('ID Pallet')
                    ->searchable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->label('Lampirkan Produk')
                    ->preloadRecordSelect(),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
            ]);
    }
}
