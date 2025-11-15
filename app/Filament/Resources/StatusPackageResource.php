<?php

namespace App\Filament\Resources;

use App\Filament\Resources\StatusPackageResource\Pages;
use App\Models\StatusPackage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class StatusPackageResource extends Resource
{
    use Translatable;

    protected static ?string $model = StatusPackage::class;

    protected static ?string $navigationGroup = 'Manajemen Produk';
    protected static ?string $label = 'Status Paket';
    protected static ?int $navigationSort = 7;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('status_trans')
                    ->label('Status Kemasan')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Masukkan status paket...'),
            ])->inlineLabel()->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status_trans')->label('Status')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('created_at')->label('Dibuat')->dateTime()->sortable(),
                Tables\Columns\TextColumn::make('updated_at')->label('Diubah')->dateTime()->sortable(),
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
            'index' => Pages\ManageStatusPackages::route('/'),
            'create' => Pages\CreateStatusPackage::route('/create'),
            'edit' => Pages\EditStatusPackage::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([
            SoftDeletingScope::class,
        ]);
    }
}
