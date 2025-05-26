<?php

namespace App\Filament\Resources\ProductStatusResource\Pages;

use App\Filament\Resources\ProductStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductStatus extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;
    protected static string $resource = ProductStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
