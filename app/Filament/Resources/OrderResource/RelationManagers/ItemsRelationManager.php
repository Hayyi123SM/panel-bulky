<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'items';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('id')
                    ->required()
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->paginated(false)
            ->columns([
                Tables\Columns\TextColumn::make('product.name_trans')
                    ->label('Nama Produk'),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Jumlah')
                    ->alignRight(),
                Tables\Columns\TextColumn::make('price')
                    ->label('Harga')
                    ->prefix('Rp ')
                    ->numeric(0, ',','.')
                    ->alignRight()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()),
            ]);
    }
}
