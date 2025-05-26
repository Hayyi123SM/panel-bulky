<?php

namespace App\Filament\Resources\ProductStatusResource\Pages;

use App\Filament\Resources\ProductStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords\Concerns\Translatable;
use Filament\Resources\Pages\ManageRecords;

class ManageProductStatuses extends ManageRecords
{
    use Translatable;

    protected static string $resource = ProductStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ProductStatusResource\Actions\SyncDataAction::make(),
            Actions\LocaleSwitcher::make(),
        ];
    }
}
