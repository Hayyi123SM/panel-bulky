<?php

namespace App\Filament\Resources\DisclaimerResource\Pages;

use App\Filament\Resources\DisclaimerResource;
use Filament\Actions;
use Filament\Resources\Concerns\Translatable;
use Filament\Resources\Pages\ViewRecord;

class ViewDisclaimer extends ViewRecord
{
    use Translatable;

    protected static string $resource = DisclaimerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
