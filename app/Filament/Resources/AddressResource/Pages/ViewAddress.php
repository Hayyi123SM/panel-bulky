<?php

namespace App\Filament\Resources\AddressResource\Pages;

use App\Filament\Resources\AddressResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewAddress extends ViewRecord
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)
            ->schema([
                Section::make('Address Detail')
                    ->schema([
                        TextEntry::make('user.name')->label('Pengguna'),
                        TextEntry::make('label'),
                        TextEntry::make('name')->label('Nama'),
                        TextEntry::make('phone_number')->label('Nomor Telepon'),
                        TextEntry::make('address')
                            ->label('Alamat')
                            ->helperText(fn($record) => $record->formatted_area),
                    ])
            ])->inlineLabel()->columns(1);
    }
}
