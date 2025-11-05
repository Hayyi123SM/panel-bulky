<?php

namespace App\Filament\Resources\UserConsentResource\Pages;

use App\Filament\Resources\UserConsentResource;
use Filament\Actions;
use Filament\Infolists\Components\Fieldset;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Pages\ViewRecord;

class ViewUserConsent extends ViewRecord
{
    protected static string $resource = UserConsentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\EditAction::make(),
        ];
    }

    // --- Tambahkan method infolist() ini ---
    public function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Grid::make(3) // Menggunakan Grid 3 kolom untuk tata letak
                    ->schema([
                        // 1. Bagian Utama (User, Order, Disclaimer)
                        Section::make('Informasi Persetujuan')
                            ->description('Detail pengguna, order, dan disclaimer yang disetujui.')
                            ->icon('heroicon-m-document-check')
                            ->columnSpan(2) // Mengambil 2 dari 3 kolom
                            ->schema([
                                TextEntry::make('user.name')
                                    ->label('Pengguna yang Setuju')
                                    ->icon('heroicon-m-user')
                                    ->color('primary'),

                                TextEntry::make('order.order_number')
                                    ->label('Nomor Order Terkait')
                                    ->copyable()
                                    ->icon('heroicon-m-clipboard'),

                                TextEntry::make('disclaimer.title')
                                    ->label('Judul Disclaimer')
                                    ->badge()
                                    ->color('secondary'),

                                TextEntry::make('consent_type')
                                    ->label('Tipe Persetujuan')
                                    ->badge()
                                    ->color(fn(string $state): string => match ($state) {
                                        'terms' => 'info',
                                        'privacy' => 'success',
                                        'marketing' => 'warning',
                                        default => 'gray',
                                    }),
                            ]),

                        // 2. Bagian Log Teknis
                        Fieldset::make('Log Waktu & Teknis')
                            ->columnSpan(1) // Mengambil 1 kolom sisanya
                            ->schema([
                                TextEntry::make('accepted_at')
                                    ->label('Waktu Diterima')
                                    ->dateTime('j F Y, H:i:s')
                                    ->icon('heroicon-m-calendar')
                                    ->color('success'),

                                TextEntry::make('ip_address')
                                    ->label('Alamat IP')
                                    ->icon('heroicon-m-globe-alt'),

                                TextEntry::make('created_at')
                                    ->label('Data Dibuat')
                                    ->dateTime(),
                            ]),
                    ]),

                // 3. Detail User Agent (Lebar Penuh)
                Section::make('Detail Browser/Perangkat (User Agent)')
                    ->collapsible() // Boleh dilipat (opsional)
                    ->schema([
                        TextEntry::make('user_agent')
                            ->label('User Agent String Lengkap')
                            ->prose() // Tampilkan dalam format blok teks yang rapi
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return parent::mutateFormDataBeforeFill($data);
    }
}
