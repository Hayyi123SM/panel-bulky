<?php

namespace App\Filament\Resources\ProductBrandResource\Pages;

use App\Filament\Resources\ProductBrandResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageProductBrands extends ManageRecords
{
    protected static string $resource = ProductBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            ProductBrandResource\Actions\SyncDataAction::make(),
        ];
    }
}
