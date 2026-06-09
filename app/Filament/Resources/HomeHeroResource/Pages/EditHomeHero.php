<?php

namespace App\Filament\Resources\HomeHeroResource\Pages;

use App\Filament\Resources\HomeHeroResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditHomeHero extends EditRecord
{
    use EditRecord\Concerns\Translatable;
    protected static string $resource = HomeHeroResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\LocaleSwitcher::make()
        ];
    }
}
