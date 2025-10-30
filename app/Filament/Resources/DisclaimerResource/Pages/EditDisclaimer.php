<?php

namespace App\Filament\Resources\DisclaimerResource\Pages;

use App\Filament\Resources\DisclaimerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDisclaimer extends EditRecord
{
    protected static string $resource = DisclaimerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
