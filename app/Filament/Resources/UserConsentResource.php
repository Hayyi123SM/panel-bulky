<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserConsentResource\Pages;
use App\Models\UserConsent;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UserConsentResource extends Resource
{
    protected static ?string $model = UserConsent::class;

    protected static ?string $label = 'Riwayat Persetujuan Pengguna';
    protected static ?string $pluralLabel = 'Persetujuan Pengguna';
    protected static ?string $navigationGroup = 'Audit & Log';
    protected static ?int $navigationSort = 15;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Karena ini data log, biasanya form di disable atau hanya untuk view.
                // Jika ingin melihat detail, kita bisa gunakan 'view' page.
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                // 1. Informasi Dasar (Condensed)
                TextColumn::make('user.name')
                    ->label('Pengguna')
                    ->icon('heroicon-m-user') // Tambahkan ikon untuk visual
                    ->color('primary')
                    ->searchable()
                    ->sortable(),

                // 2. Tipe Persetujuan (Visual dengan Badge/Warna)
                TextColumn::make('consent_type')
                    ->label('Tipe Persetujuan')
                    ->badge() // Tampilkan sebagai badge
                    ->color(fn(string $state): string => match ($state) {
                        'terms' => 'info', // Biru
                        'privacy' => 'success', // Hijau
                        'marketing' => 'warning', // Kuning
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),

                // 3. Detail Konten (Link dan Tooltip)
                TextColumn::make('disclaimer.title')
                    ->label('Judul Disclaimer')
                    ->tooltip(fn(UserConsent $record): string => $record->disclaimer->content ?? 'Tidak ada konten') // Tooltip untuk preview konten
                    ->limit(40) // Batasi teks agar tidak terlalu panjang
                    ->color('secondary') // Beri warna sekunder
                    ->searchable(),

                // 4. Metadata Order (Diletakkan di bagian tengah)
                TextColumn::make('order.order_number')
                    ->label('No. Order')
                    ->copyable() // Izinkan pengguna menyalin Order Number
                    ->icon('heroicon-o-clipboard-document')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Sembunyikan secara default, bisa di-toggle

                // 5. Informasi Teknis (Icon dan Tooltip)
                IconColumn::make('ip_address')
                    ->label('Log Aksi')
                    ->icon('heroicon-m-globe-alt') // Gunakan ikon untuk visualisasi log
                    ->tooltip(fn(UserConsent $record): string => "IP: {$record->ip_address}\nUser Agent: {$record->user_agent}") // Gabungkan IP dan User Agent di Tooltip
                    ->sortable()
                    ->alignCenter(), // Pusatkan ikon

                TextColumn::make('accepted_at')
                    ->label('Waktu Diterima')
                    ->dateTime('j F Y, H:i:s')
                    ->since()
                    ->color('success')
                    ->sortable(),

            ])
            ->filters([
                Tables\Filters\SelectFilter::make('consent_type')
                    ->options([
                        'terms' => 'Syarat & Ketentuan',
                        'privacy' => 'Kebijakan Privasi',
                        'marketing' => 'Persetujuan Marketing',
                    ])
                    ->label('Filter Tipe'),
            ])
            ->defaultSort('accepted_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    // Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUserConsents::route('/'),
            'view' => Pages\ViewUserConsent::route('/{record}'),
        ];
    }
}
