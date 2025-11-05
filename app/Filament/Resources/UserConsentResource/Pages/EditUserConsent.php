<?php

namespace App\Filament\Resources\UserConsentResource\Pages;

use App\Filament\Resources\UserConsentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserConsent extends EditRecord
{
    protected static string $resource = UserConsentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
