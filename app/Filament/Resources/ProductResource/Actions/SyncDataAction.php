<?php

namespace App\Filament\Resources\ProductResource\Actions;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\ProductStatus;
use App\Models\Warehouse;
use App\Services\WMS\WMSBrand;
use App\Services\WMS\WMSProduct;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Support\Colors\Color;

class SyncDataAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sync_data_action';
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->label('Sync Data WMS');
        $this->icon('heroicon-o-arrow-down-tray');
        $this->color(Color::Cyan);
        $this->requiresConfirmation();

        $this->form([
            Select::make('warehouse')
                ->label('Pilih Gudang')
                ->helperText('Produk yang disinkronkan ke WMS akan disimpan di gudang yang dipilih.')
                ->options(Warehouse::orderBy('name')->pluck('name', 'id'))
                ->required()
                ->uuid()
        ]);

        $this->action(function (array $data) {
            $page = 1;
            $hasError = false;

            do {
                $wmsProducts = WMSProduct::getProducts(['page' => $page]);
                if (!array_key_exists('error', $wmsProducts)) {
                    $currentPage = $wmsProducts['current_page'];
                    $lastPage = $wmsProducts['last_page'];

                    $created = $this->handleResponseData($wmsProducts['data'], $data);
                    if (!$created) {
                        $hasError = true;
                        $this->failureNotificationTitle('Terjadi Kesalahan saat mensinkronkan ke WMS.');
                        $this->failure();
                        break;
                    }
                    $page++;
                } else {
                    $hasError = true;
                    $this->failureNotificationTitle($wmsProducts['error']);
                    $this->failure();
                    break;
                }
            } while ($currentPage < $lastPage);

            if (!$hasError) {
                $this->successNotificationTitle('Data berhasil disinkronkan ke WMS');
                $this->success();
            }
        });
    }

    private function handleResponseData(array $data, array $input): bool
    {
        foreach ($data as $wmsProduct) {
            $images = [];
            foreach ($wmsProduct['palet_images'] as $image) {
                $url = config('wms.base_url') . $image['file_path'];
                $imageContents = file_get_contents($url);
                $imagePath = 'images/pallet/' . basename($url);
                \Storage::disk('public')->put($imagePath, $imageContents);
                $images[] = $imagePath;
            }

            $category = ProductCategory::whereWmsId($wmsProduct['category_id'])->first();
            $condition = ProductCondition::whereWmsId($wmsProduct['product_condition_id'])->first();
            $status = ProductStatus::whereWmsId($wmsProduct['product_status_id'])->first();

            if ($category && $condition && $status) {
                $product = Product::firstOrCreate(['wms_id' => $wmsProduct['id']], [
                    'warehouse_id' => $input['warehouse'],
                    'name' => $wmsProduct['name_palet'],
                    'name_trans' => [
                        'id' => $wmsProduct['name_palet'],
                        'en' => $wmsProduct['name_palet'],
                    ],
                    'description' => $wmsProduct['description'],
                    'description_trans' => [
                        'id' => $wmsProduct['description'],
                        'en' => $wmsProduct['description'],
                    ],
                    'price' => (int)$wmsProduct['total_price_palet'],
                    'sold_out' => $wmsProduct['is_sale'],
                    'id_pallet' => $wmsProduct['palet_barcode'],
                    'total_quantity' => $wmsProduct['total_product_palet'],
                    'pdf_file' => $wmsProduct['file_pdf'],
                    'is_active' => false,
                    'product_category_id' => $category->id,
                    'product_condition_id' => $condition->id,
                    'product_status_id' => $status->id,
                    'images' => $images,
                ]);

                $brandIds = [];
                foreach ($wmsProduct['palet_brands'] as $palletBrand) {
                    $brand = ProductBrand::find($palletBrand['id']);
                    if ($brand) {
                        $brandIds[] = $brand->id;
                    }
                }

                $product->brands()->sync($brandIds);
            } else {
                return false;
            }
        }

        return true;
    }
}
