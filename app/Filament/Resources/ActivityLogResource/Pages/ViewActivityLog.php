<?php

namespace App\Filament\Resources\ActivityLogResource\Pages;

use Filament\Resources\Pages\ViewRecord;
use App\Filament\Resources\ActivityLogResource;

class ViewActivityLog extends ViewRecord
{
    protected static string $resource = ActivityLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTitle(): string
    {
        return 'Detail Log Aktivitas';
    }

    public function getSubheading(): ?string
    {
        return $this->record->description;
    }
}
