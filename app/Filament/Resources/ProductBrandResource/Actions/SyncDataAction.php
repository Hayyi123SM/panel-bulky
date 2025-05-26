<?php

namespace App\Filament\Resources\ProductBrandResource\Actions;

use App\Models\Order;
use App\Models\ProductBrand;
use App\Services\WMS\WMSBrand;
use Filament\Actions\Action;
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

        $this->action(function () {
            $page = 1;
            $hasError = false;

            do {
                $wmsBrands = WMSBrand::getBrands(['page' => $page]);
                if (!array_key_exists('error', $wmsBrands)) {
                    $currentPage = $wmsBrands['current_page'];
                    $lastPage = $wmsBrands['last_page'];

                    $this->handleResponseData($wmsBrands['data']);
                    $page++;
                } else {
                    $hasError = true;
                    $this->failureNotificationTitle($wmsBrands['error']);
                    $this->failure();
                    break;
                }
            } while ($currentPage < $lastPage);

            if(!$hasError) {
                $this->successNotificationTitle('Data berhasil disinkronkan ke WMS');
                $this->success();
            }
        });
    }

    private function handleResponseData(array $data): void
    {
        foreach ($data as $wmsBrand) {
            ProductBrand::updateOrCreate([
                'wms_id' => $wmsBrand['id'],
            ], [
                'name' => $wmsBrand['brand_name'],
                'slug' => $wmsBrand['brand_slug'],
            ]);
        }
    }
}
