<?php

namespace App\Filament\Resources\UserConsentResource\Pages;

use App\Filament\Exports\UserConsentExporter;
use App\Filament\Resources\UserConsentResource;
use Filament\Actions;
use Filament\Actions\ExportAction;
use Filament\Resources\Pages\ListRecords;

class ListUserConsents extends ListRecords
{
    protected static string $resource = UserConsentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
            ExportAction::make()
                ->exporter(UserConsentExporter::class)
                ->label('Ekspor Data'),
        ];
    }
}
