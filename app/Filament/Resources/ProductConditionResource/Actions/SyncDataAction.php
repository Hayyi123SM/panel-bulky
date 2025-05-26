<?php

namespace App\Filament\Resources\ProductConditionResource\Actions;

use App\Models\Order;
use App\Models\ProductBrand;
use App\Models\ProductCondition;
use App\Services\WMS\WMSBrand;
use App\Services\WMS\WMSCondition;
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
                $wmsConditions = WMSCondition::getConditions(['page' => $page]);
                if (!array_key_exists('error', $wmsConditions)) {
                    $currentPage = $wmsConditions['current_page'];
                    $lastPage = $wmsConditions['last_page'];

                    $this->handleResponseData($wmsConditions['data']);
                    $page++;
                } else {
                    $hasError = true;
                    $this->failureNotificationTitle($wmsConditions['error']);
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
        foreach ($data as $wmsCondition) {
            ProductCondition::updateOrCreate([
                'wms_id' => $wmsCondition['id'],
            ], [
                'title' => $wmsCondition['condition_name'],
                'title_trans' => [
                    'id' => $wmsCondition['condition_name'],
                    'en' => $wmsCondition['condition_name'],
                ],
                'slug' => $wmsCondition['condition_slug'],
            ]);
        }
    }
}
