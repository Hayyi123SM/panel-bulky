<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use FilamentTiptapEditor\Facades\TiptapConverter;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewProduct extends ViewRecord
{
    use ViewRecord\Concerns\Translatable;

    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\LocaleSwitcher::make()
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)
            ->schema([
                Section::make('Product Image')
                    ->schema([
                        ImageEntry::make('images')
                    ]),
                Section::make('Main Product Information')
                    ->schema([
                        TextEntry::make('name_trans')
                            ->label('Name')
                            ->state(function ($record): string {
                                $activeLocale = (property_exists($this, 'activeLocale') && $this->activeLocale) ? $this->activeLocale : app()->getLocale();
                                return (string) $record->getTranslation('name_trans', $activeLocale, false);
                            }),
                        TextEntry::make('id_pallet')->label('ID Pallet'),
                        TextEntry::make('price')
                            ->prefix('Rp ')
                            ->numeric(0, ',', '.'),
                        TextEntry::make('total_quantity'),
                        TextEntry::make('packaging_type')
                            ->label('Tipe Pengemasan')
                            ->formatStateUsing(fn($state) => match ($state) {
                                'palet' => 'Palet',
                                'container' => 'Kontainer',
                                'truck_load' => 'Truck Load',
                                default => '-',
                            }),
                        TextEntry::make('truck_load_vehicle_type_id')
                            ->label('Jenis Kendaraan Truck Load')
                            ->visible(fn($record) => $record->packaging_type === 'truck_load')
                            ->formatStateUsing(function ($state) {
                                $options = [
                                    2704 => 'Fuso Box Liquid8 (8 Tons)',
                                    2726 => 'Fuso Pickup Liquid8 (8 Ton)',
                                    2727 => 'Fuso Box Liquid8 (10 Ton)',
                                    2728 => 'Fuso Pickup Liquid8 (10 Ton)',
                                ];
                                return $options[$state] ?? $state;
                            }),
                    ]),
                Section::make('Additional Product Details')
                    ->schema([
                        TextEntry::make('pdf_file')->label('PDF File'),
                        TextEntry::make('description_trans')
                            ->label('Description')
                            ->state(function ($record): string {
                                $activeLocale = (property_exists($this, 'activeLocale') && $this->activeLocale) ? $this->activeLocale : app()->getLocale();
                                $translation = $record->getTranslation('description_trans', $activeLocale, false);
                                if (empty($translation)) return '';
                                return TiptapConverter::asHTML($translation);
                            })
                            ->html(),
                    ]),
                Section::make('Status and Categorization')
                    ->schema([
                        IconEntry::make('is_active')->label('Is Active'),
                        TextEntry::make('warehouse.name')->label('Warehouse'),
                        TextEntry::make('productCategory.name')->label('Category'),
                        TextEntry::make('brands.name')->label('Brand'),
                        TextEntry::make('productCondition.title')->label('Condition'),
                        TextEntry::make('productStatus.status')->label('Status'),
                        TextEntry::make('note_discrepancy')->label('Catatan perbedaan')
                            ->formatStateUsing(fn($state) => $state !== null ? $state . '%' : '-'),
                    ]),
                Section::make('Dimensi & Berat')
                    ->schema([
                        TextEntry::make('length_cm')
                            ->label('Panjang')
                            ->formatStateUsing(fn($state) => $state !== null ? ((is_numeric($state) && floor($state) == $state) ? (int) $state : number_format($state, 2, ',', '.')) . ' cm' : '-'),

                        TextEntry::make('width_cm')
                            ->label('Lebar')
                            ->formatStateUsing(fn($state) => $state !== null ? ((is_numeric($state) && floor($state) == $state) ? (int) $state : number_format($state, 2, ',', '.')) . ' cm' : '-'),

                        TextEntry::make('height_cm')
                            ->label('Tinggi')
                            ->formatStateUsing(fn($state) => $state !== null ? ((is_numeric($state) && floor($state) == $state) ? (int) $state : number_format($state, 2, ',', '.')) . ' cm' : '-'),

                        TextEntry::make('weight_kg')
                            ->label('Berat')
                            ->formatStateUsing(fn($state) => $state !== null ? ((is_numeric($state) && floor($state) == $state) ? (int) $state : number_format($state, 2, ',', '.')) . ' kg' : '-'),

                        TextEntry::make('volume_m3')
                            ->label('Volume')
                            ->formatStateUsing(fn($state) => $state !== null ? number_format($state, 6, ',', '.') . ' m³' : '-'),
                    ])
            ])->inlineLabel()->columns(1);
    }
}
