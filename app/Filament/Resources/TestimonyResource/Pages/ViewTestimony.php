<?php

namespace App\Filament\Resources\TestimonyResource\Pages;

use App\Filament\Resources\TestimonyResource;
use Filament\Actions;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewTestimony extends ViewRecord
{
    protected static string $resource = TestimonyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)
            ->schema([
                Section::make()
                    ->schema([
                        ImageEntry::make('image')
                            ->label('Avatar')
                            ->placeholder('-'),
                        TextEntry::make('name')->label('Nama'),
                        TextEntry::make('label'),
                        TextEntry::make('content')->label('Testimoni'),
                    ])
            ])->inlineLabel()->columns(1);
    }
}
