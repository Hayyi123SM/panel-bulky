<?php

namespace App\Filament\Resources\ProductStatusResource\Actions;

use App\Models\Order;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductStatus;
use App\Services\WMS\WMSCategory;
use App\Services\WMS\WMSStatus;
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
            $wmsStatuses = WMSStatus::getStatus();

            if(!collect($wmsStatuses)->has('error')) {
                $this->handleResponseData($wmsStatuses);
                $this->successNotificationTitle('Data berhasil disinkronkan ke WMS');
                $this->success();
            } else {
                $this->failureNotificationTitle($wmsStatuses['error']);
                $this->failure();
            }
        });
    }

    private function handleResponseData(array $data): void
    {
        foreach ($data as $wmsStatus) {
            ProductStatus::updateOrCreate([
                'wms_id' => $wmsStatus['id'],
            ], [
                'status' => $wmsStatus['status_name'],
                'status_trans' => [
                    'id' => $wmsStatus['status_name'],
                    'en' => $wmsStatus['status_name'],
                ]
            ]);
        }
    }
}
