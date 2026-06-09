<?php

namespace App\Filament\Resources\HomeHeroResource\Pages;

use App\Filament\Resources\HomeHeroResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Actions;

class CreateHomeHero extends CreateRecord
{
    use CreateRecord\Concerns\Translatable;
    protected static string $resource = HomeHeroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\LocaleSwitcher::make()
        ];
    }
}
