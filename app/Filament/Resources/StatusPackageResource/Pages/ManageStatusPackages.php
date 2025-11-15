<?php

namespace App\Filament\Resources\StatusPackageResource\Pages;

use App\Filament\Resources\StatusPackageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ManageStatusPackages extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = StatusPackageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
