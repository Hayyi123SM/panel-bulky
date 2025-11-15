<?php

namespace App\Filament\Resources\StatusPackageResource\Pages;

use App\Filament\Resources\StatusPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditStatusPackage extends EditRecord
{
    use EditRecord\Concerns\Translatable;

    protected static string $resource = StatusPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
