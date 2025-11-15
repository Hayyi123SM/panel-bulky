<?php

namespace App\Filament\Resources\StatusPackageResource\Pages;

use App\Filament\Resources\StatusPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateStatusPackage extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = StatusPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
