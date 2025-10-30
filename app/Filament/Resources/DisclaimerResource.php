<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DisclaimerResource\Pages;
use App\Filament\Resources\DisclaimerResource\RelationManagers;
use App\Models\Disclaimer;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DisclaimerResource extends Resource
{
    use Translatable;

    protected static ?string $model = Disclaimer::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\Section::make('Informasi Umum')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('title')->label('Judul'),
                        \Filament\Infolists\Components\TextEntry::make('slug')->label('Slug'),
                        \Filament\Infolists\Components\IconEntry::make('is_active')
                            ->label('Aktif')
                            ->icon(fn($state) => $state ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle')
                            ->color(fn($state) => $state ? 'success' : 'danger'),
                        \Filament\Infolists\Components\TextEntry::make('created_at')
                            ->label('Tanggal Dibuat')
                            ->dateTime('d M Y H:i'),
                        \Filament\Infolists\Components\TextEntry::make('updated_at')
                            ->label('Tanggal Diupdate')
                            ->dateTime('d M Y H:i'),
                        \Filament\Infolists\Components\TextEntry::make('deleted_at')
                            ->label('Tanggal Dihapus')
                            ->dateTime('d M Y H:i')
                            ->visible(fn($record) => filled($record->deleted_at)),
                    ])
                    ->columns(3),

                \Filament\Infolists\Components\Section::make('Konten')
                    ->schema([
                        \Filament\Infolists\Components\TextEntry::make('content')
                            ->label('Konten')
                            ->markdown()
                            ->columnSpanFull(),
                    ])
                    ->columns(1),

                // \Filament\Infolists\Components\Section::make('Terjemahan')
                //     ->schema([
                //         \Filament\Infolists\Components\TextEntry::make('title_trans')
                //             ->label('Judul (Terjemahan)')
                //             ->formatStateUsing(fn($state) => is_array($state) ? implode("\n", array_map(fn($k, $v) => strtoupper($k) . ': ' . $v, array_keys($state), $state)) : (string)$state)
                //             ->columnSpanFull(),

                //         \Filament\Infolists\Components\TextEntry::make('content_trans')
                //             ->label('Konten (Terjemahan)')
                //             ->formatStateUsing(fn($state) => is_array($state) ? implode("\n\n", array_map(fn($k, $v) => strtoupper($k) . ":\n" . $v, array_keys($state), $state)) : (string)$state)
                //             ->columnSpanFull(),
                //     ])
                //     ->columns(1),
            ])
            ->columns(1);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->label('Judul')
                    ->required()
                    ->maxLength(255),
                Forms\Components\RichEditor::make('content')
                    ->label('Konten')
                    ->required()
                    ->columnSpanFull(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Aktif')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable(),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
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
                    // Tables\Actions\ForceDeleteBulkAction::make(),
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
            'index' => Pages\ListDisclaimers::route('/'),
            'create' => Pages\CreateDisclaimer::route('/create'),
            'view' => Pages\ViewDisclaimer::route('/{record}'),
            'edit' => Pages\EditDisclaimer::route('/{record}/edit'),
        ];
    }
}
