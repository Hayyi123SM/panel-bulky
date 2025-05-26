<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
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
                        TextEntry::make('name_trans'),
                        TextEntry::make('id_pallet')->label('ID Pallet'),
                        TextEntry::make('price')
                            ->prefix('Rp ')
                            ->numeric(0, ',', '.'),
                        TextEntry::make('total_quantity'),
                    ]),
                Section::make('Additional Product Details')
                    ->schema([
                        TextEntry::make('pdf_file')->label('PDF File'),
                        TextEntry::make('description_trans')->html(),
                    ]),
                Section::make('Status and Categorization')
                    ->schema([
                        IconEntry::make('is_active')->label('Is Active'),
                        TextEntry::make('warehouse.name')->label('Warehouse'),
                        TextEntry::make('productCategory.name')->label('Category'),
                        TextEntry::make('brands.name')->label('Brand'),
                        TextEntry::make('productCondition.title')->label('Condition'),
                        TextEntry::make('productStatus.status')->label('Status'),
                    ])
            ])->inlineLabel()->columns(1);
    }
}
