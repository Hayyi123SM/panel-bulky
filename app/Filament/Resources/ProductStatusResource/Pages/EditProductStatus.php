<?php

namespace App\Filament\Resources\ProductStatusResource\Pages;

use App\Filament\Resources\ProductStatusResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductStatus extends EditRecord
{
    use EditRecord\Concerns\Translatable;
    protected static string $resource = ProductStatusResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make(),
        ];
    }
}
