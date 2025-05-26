<?php

namespace App\Filament\Resources\ReviewResource\Pages;

use App\Filament\Resources\ReviewResource;
use App\Filament\Resources\ReviewResource\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;
use Mokhosh\FilamentRating\Entries\RatingEntry;

class ViewReview extends ViewRecord
{
    protected static string $resource = ReviewResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ApproveAction::make(),
            Actions\CancelAction::make(),
        ];
    }

    public function infolist(Infolist $infolist): Infolist
    {
        return parent::infolist($infolist)
            ->schema([
                Section::make('Ulasan')
                    ->schema([
                        TextEntry::make('user.name')->label('Pengguna'),
                        TextEntry::make('order.order_number')->label('Nomor Pesanan'),
                        TextEntry::make('product.name')->label('Produk')->placeholder('-'),
                        IconEntry::make('approved')->label('Disetujui'),
                        RatingEntry::make('rating')->label('Rating'),
                        TextEntry::make('comment')->label('Komentar'),
                        ImageEntry::make('images.path')->label('Gambar')->placeholder('-'),
                    ])->inlineLabel()
            ]);
    }
}
