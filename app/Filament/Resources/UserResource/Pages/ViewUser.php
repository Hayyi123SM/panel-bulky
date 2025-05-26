<?php

namespace App\Filament\Resources\UserResource\Pages;

use App\Filament\Resources\UserResource;
use Filament\Actions;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            UserResource\Actions\ChangePasswordAction::make()
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)
            ->schema([
                Section::make()
                    ->schema([
                        TextEntry::make('name')->label('Nama'),
                        TextEntry::make('username'),
                        TextEntry::make('email'),
                        TextEntry::make('phone_number')->label('No. Telepon')
                            ->label('Phone Number'),
                    ])
            ])->inlineLabel()->columns(1);
    }

    public function getRelationManagers(): array
    {
        return [
            UserResource\RelationManagers\OrdersRelationManager::make()
        ];
    }
}
