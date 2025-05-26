<?php

namespace App\Filament\Resources\ProductConditionResource\Pages;

use App\Filament\Resources\ProductConditionResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProductCondition extends EditRecord
{
    use EditRecord\Concerns\Translatable;
    protected static string $resource = ProductConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make()
        ];
    }
}
