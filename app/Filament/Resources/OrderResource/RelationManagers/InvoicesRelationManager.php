<?php

namespace App\Filament\Resources\OrderResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';
    protected static ?string $title = 'Tagihan';

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
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Pengguna'),
                Tables\Columns\TextColumn::make('status'),
                Tables\Columns\TextColumn::make('paymentMethod.name')
                    ->label('Jenis Pembayaran')
                    ->placeholder('-'),
                Tables\Columns\TextColumn::make('amount')
                    ->label('Jumlah')
                    ->alignRight()
                    ->prefix('Rp ')
                    ->numeric(0, ',', '.')
                    ->summarize(Tables\Columns\Summarizers\Sum::make())
            ]);
    }
}
