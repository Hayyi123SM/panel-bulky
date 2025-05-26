<?php

namespace App\Filament\Resources\ProductConditionResource\Pages;

use App\Filament\Resources\ProductConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateProductCondition extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;

    protected static string $resource = ProductConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make()
        ];
    }
}
