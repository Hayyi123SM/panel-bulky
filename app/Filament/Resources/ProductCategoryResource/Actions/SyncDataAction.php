<?php

namespace App\Filament\Resources\ProductCategoryResource\Actions;

use App\Models\Order;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Services\WMS\WMSCategory;
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
            $wmsCategories = WMSCategory::getCategories();

            if(!collect($wmsCategories)->has('error')) {
                $this->handleResponseData($wmsCategories);
                $this->successNotificationTitle('Data berhasil disinkronkan ke WMS');
                $this->success();
            } else {
                $this->failureNotificationTitle($wmsCategories['error']);
                $this->failure();
            }
        });
    }

    private function handleResponseData(array $data): void
    {
        foreach ($data as $wmsCategory) {
            ProductCategory::updateOrCreate(
                ['wms_id' => $wmsCategory['id']],
                [
                    'name' => $wmsCategory['name_category'],
                    'name_trans' => [
                        'id' => $wmsCategory['name_category'],
                        'en' => $wmsCategory['name_category'],
                    ]
                ]
            );
        }
    }
}
