<?php

namespace App\Filament\Resources\ProductResource\Pages;

use App\Filament\Resources\ProductResource;
use App\Jobs\ExportProductsJob;
use App\Services\WMS\ApiRequest;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListProducts extends ListRecords
{
    use ListRecords\Concerns\Translatable;

    protected static string $resource = ProductResource::class;

    protected array $wmsProducts = [];

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('exportProduk')
                ->label('Export Produk')
                ->icon('heroicon-o-arrow-down-tray')
                ->form([
                    Forms\Components\Select::make('is_active')
                        ->label('Status Aktif')
                        ->options([
                            '' => 'Semua',
                            '1' => 'Aktif',
                            '0' => 'Tidak Aktif',
                        ]),
                    Forms\Components\Select::make('sold_out')
                        ->label('Status Penjualan')
                        ->options([
                            '' => 'Semua',
                            '0' => 'Belum Terjual',
                            '1' => 'Terjual',
                        ]),
                ])
                ->action(function ($data) {
                    $filters = [
                        'is_active' => $data['is_active'] ?? '',
                        'sold_out' => $data['sold_out'] ?? '',
                    ];

                    $user = auth()->user();
                    ExportProductsJob::dispatch($user, $filters);

                    Notification::make()
                        ->success()
                        ->title('Export Diproses')
                        ->body('Export produk sedang diproses. Anda akan menerima notifikasi saat file siap diunduh.')
                        ->send();
                })
                ->modalHeading('Export Produk')
                ->modalSubmitActionLabel('Export'),
            Actions\Action::make('syncProduk')
                ->label('Sync')
                ->icon('heroicon-o-arrow-path')
                ->color('warning')
                ->form([
                    Forms\Components\Select::make('product')
                        ->label('Produk (Not Sale)')
                        ->searchable()
                        ->required()
                        ->live()
                        ->options(fn () => $this->loadWmsProducts()),
                    Forms\Components\TextInput::make('discount')
                        ->label('Diskon (%)')
                        ->numeric()
                        ->suffix('%')
                        ->minValue(0)
                        ->maxValue(100)
                        ->rules(['lte:100', 'gte:0'])
                        ->required()
                        ->live(debounce: '500ms'),
                    Forms\Components\Placeholder::make('calculation_result')
                        ->label('Hasil Kalkulasi Diskon')
                        ->content(function (Forms\Get $get) {
                            $productId = $get('product');
                            $discount = (float) ($get('discount') ?? 0);

                            if (! $productId || $discount <= 0) {
                                return 'Pilih produk dan masukkan diskon untuk melihat kalkulasi.';
                            }

                            $product = $this->wmsProducts[$productId] ?? null;
                            if (! $product) {
                                return 'Data produk tidak ditemukan.';
                            }

                            $originalPrice = (float) $product['total_old_price_bulky'];
                            $discountAmount = $originalPrice * ($discount / 100);
                            $finalPrice = $originalPrice - $discountAmount;

                            return sprintf(
                                '%s: Rp %s → Rp %s (diskon: Rp %s)',
                                $product['name_document'],
                                number_format($originalPrice, 0, ',', '.'),
                                number_format($finalPrice, 0, ',', '.'),
                                number_format($discountAmount, 0, ',', '.'),
                            );
                        }),
                ])
                ->action(function ($data) {
                    $productId = $data['product'];
                    $discount = (float) $data['discount'];
                    $product = $this->wmsProducts[$productId] ?? null;
                    $productName = $product['name_document'] ?? "ID {$productId}";

                    $result = ApiRequest::sendPostRequest('/api/bulky/update-sale-price', [
                        'bulky_document_id' => (int) $productId,
                        'discount' => (int) $discount,
                    ]);

                    if (isset($result['error'])) {
                        Notification::make()
                            ->danger()
                            ->title('Sync Diskon Gagal')
                            ->body("Gagal update diskon untuk {$productName}. {$result['error']}")
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title('Sync Diskon Berhasil')
                        ->body("Diskon {$discount}% berhasil diterapkan ke {$productName}.")
                        ->send();
                })
                ->modalHeading('Sync Diskon Produk')
                ->modalSubmitActionLabel('Sync')
                ->modalWidth('lg'),
            Actions\LocaleSwitcher::make(),
        ];
    }

    protected function loadWmsProducts(): array
    {
        $result = ApiRequest::sendGetRequest('/api/bulky-documents/not-sale');

        if (isset($result['error'])) {
            Notification::make()
                ->danger()
                ->title('Gagal Memuat Produk')
                ->body($result['error'])
                ->send();

            return [];
        }

        $products = [];
        foreach ($result as $item) {
            $products[$item['id']] = $item['name_document'];
            $this->wmsProducts[$item['id']] = $item;
        }

        return $products;
    }
}
