<?php

namespace App\Filament\Resources\OrderResource\Actions;

use App\Enums\OrderStatusEnum;
use App\Enums\ShippingMethodEnum;
use App\Models\Order;
use App\Models\Warehouse;
use App\Services\Deliveree\Deliveree;
use App\Services\Forwarder\ApiRequest;
use App\Services\GeoRegion\GeoRegionService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\TextInput;
use Illuminate\Support\Facades\Log;

class SendPackageAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'send_package_action';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Kirim Paket');
        $this->icon('heroicon-o-truck');
        $this->color('info');
        $this->visible(function (Order $record) {
            $processing = $record->order_status == OrderStatusEnum::Processing;
            $byCourier = $record->shipping_method == ShippingMethodEnum::COURIER_PICKUP;
            return $processing && $byCourier;
        });

        $this->requiresConfirmation();

        $this->action(function (Order $record) {
            $warehouseIds = $record->items->pluck('product.warehouse_id')->unique();
            $warehouse = Warehouse::find($warehouseIds->first());
            switch ($record->shipping->shipping_provider) {
                case 'Deliveree':
                    $booking = Deliveree::createDelivery([
                        'vehicle_type_id' => $record->shipping->vehicle_type,
                        'booking_payment_type' => 'credit',
                        'time_type' => 'now',
                        'job_order_number' => $record->order_number,
                        'allow_parking_fees' => true,
                        'allow_tolls_fees' => true,
                        'locations' => [
                            [
                                'address' => $warehouse->address,
                                'latitude' => $warehouse->latitude,
                                'longitude' => $warehouse->longitude,
                                'recipient_name' => 'Bulky | Octagon',
                                'recipient_phone' => '+62811833164',
                                'note' => 'Pickup Location'
                            ],
                            [
                                'address' => $record->shipping_address,
                                'latitude' => $record->latitude,
                                'longitude' => $record->longitude,
                                'recipient_name' => $record->name,
                                'recipient_phone' => $record->phone_number,
                            ]
                        ]
                    ]);
                    break;
                case 'Forwarder':

                    $geoService = app(GeoRegionService::class);
                    $warehouseLocation = $geoService->getLocationFromGoogleMaps($warehouse->latitude, $warehouse->longitude);
                    $consigneeLocation = $geoService->getLocationFromGoogleMaps($record->latitude, $record->longitude);
                    $requirementProvider = $record->shipping->requirement_provider;
                    $apiForwarder = app(ApiRequest::class);
                    $packagingType = $record->items->first()->product->packaging_type;
                    $vehicleId = match ($packagingType) {
                        'palet' => 7, // CDD Long
                        'container' => 12, // Wing Box
                        'truck_load' => 7, // CDD Long
                        default => 7,
                    };

                    if ($requirementProvider['transport_id'] === 1) { // Sea Freight
                        $itemproducts = $record->items->pluck('product')->map(function ($product) {
                            return [
                                "qty" => "1",
                                "containertypeid" => "63", // 20 DC
                                "packageid" => "7",
                                "length" => $product->length_cm,
                                "width" => $product->width_cm,
                                "height" => $product->height_cm,
                                // "volume" => round(($product->length_cm * $product->width_cm * $product->height_cm) / 1_000_000, 2), // Convert cm3 to m3
                                "volume" => round($product->length_cm * $product->width_cm * $product->height_cm, 2), // Convert cm3
                                "weight" => $product->weight_kg,
                                "cargoid" => "78",
                                "cargodesc" => ""
                            ];
                        });
                        $payload = [
                            "transportid" => $requirementProvider['transport_id'], // Mandatory [Ambil dari API Tariff -> Sea Freight = 1]
                            "movetypeid" => "1", // Mandatory [Selalu di-input -> DOOR/DOOR = 1]
                            "loadtypeid" => $requirementProvider['loadtype_id'], // Mandatory [Ambil dari API Tariff -> Sea - FCL = 1]
                            "servicetypeid" => $requirementProvider['servicetype_id'], // Mandatory [Ambil dari API Tariff -> Reguler = 1]
                            "origincityid" => $requirementProvider['origin_cityid'], // Mandatory [Ambil dari API City]
                            "destinationcityid" => $requirementProvider['destination_cityid'], // Mandatory [Ambil dari API City]
                            "lclbasisid" => "1", // Mandatory [Ambil dari API Tariff -> LCLBasisId]
                            "cargoreadydate" => "", // Optional
                            "shipper" => "BULKY",
                            "shipperaddress" => $warehouse->address,
                            "shipperlat" => $warehouse->latitude,
                            "shipperlng" => $warehouse->longitude,
                            "shippercountry" => "Indonesia",
                            "shipperprovince" => $warehouseLocation['province'],
                            "shippercity" => $warehouseLocation['city'],
                            "shipperpostalcode" => $warehouseLocation['post_code'] ?? '',
                            "shipperremark" => "Pickup Location",
                            "consignee" => $record->name,
                            "consigneeaddress" => $consigneeLocation['full_address'] ?? $record->shipping_address,
                            "consigneelat" => $record->latitude,
                            "consigneelng" => $record->longitude,
                            "consigneecountry" => "Indonesia",
                            "consigneeprovince" => $consigneeLocation['province'],
                            "consigneecity" => $consigneeLocation['city'],
                            "consigneepostalcode" => $consigneeLocation['post_code'] ?? '',
                            "consigneeremark" => $record->shipping_address,
                            "pickup" => "BULKY",
                            "pickupaddress" => $warehouse->address,
                            "pickuplat" => $warehouse->latitude,
                            "pickuplng" => $warehouse->longitude,
                            "pickupcountry" => "Indonesia",
                            "pickupprovince" => $warehouseLocation['province'],
                            "pickupcity" => $warehouseLocation['city'],
                            "pickuppostalcode" => $warehouseLocation['post_code'] ?? '',
                            "pickupphone" => $warehouse->contact_info,
                            "pickupremark" => "Pickup Location",
                            "delivery" => $record->name,
                            "deliveryaddress" => $consigneeLocation['full_address'] ?? $record->shipping_address,
                            "deliverylat" => $record->latitude,
                            "deliverylng" => $record->longitude,
                            "deliverycountry" => "Indonesia",
                            "deliveryprovince" => $consigneeLocation['province'],
                            "deliverycity" => $consigneeLocation['city'],
                            "deliverypostalcode" => $consigneeLocation['post_code'] ?? '',
                            "deliveryphone" => $record->phone_number,
                            "deliveryremark" => $record->shipping_address,
                            "vouchercode" => "",
                            "currencyid" => "1", // IDR
                            "incoterm" => "",
                            "withinsurance" => $record->shipping->is_insurance,
                            "commodityamount" => $record->total_price,
                            "insuranceid" => "1",
                            "premiamount" => $record->total_price * (0.2 / 100), // 0.2% adalah insurance transport SEA
                            "bookingdetail" => $itemproducts->toArray()
                        ];
                        Log::info('Forwarder Sea Freight Booking Payload', $payload);
                        $booking = $apiForwarder->post('/createbooking', 'CREATEBOOKING', $payload);
                        Log::info('Forwarder Sea Freight Booking Response', $booking);
                    } else { // Land Transport
                        $itemproducts = $record->items->pluck('product')->map(function ($product) {
                            return [
                                "packaging" => "7", // Palet
                                "commodity" => "78", // null
                                "cargodesc" => $product->name,
                                "qty" => "1",
                                "length" => $product->length_cm,
                                "width" => $product->width_cm,
                                "height" => $product->height_cm,
                                // "volume" => round(($product->length_cm * $product->width_cm * $product->height_cm) / 1_000_000, 2), // Convert cm3 to m3
                                "volume" => round($product->length_cm * $product->width_cm * $product->height_cm, 2), // Convert cm3
                                "totalvolume" => round($product->length_cm * $product->width_cm * $product->height_cm, 2), // Convert cm3
                                "weight" => $product->weight_kg,
                                "totalweight" => $product->weight_kg,
                            ];
                        });
                        $payload = [
                            "transportid" => $requirementProvider['transport_id'], // Mandatory [Ambil dari API Tariff -> Land Transport = 3]
                            "loadid" => $requirementProvider['loadtype_id'], // Mandatory [Ambil dari API Tariff -> Land - FTL = 4]
                            "serviceid" => $requirementProvider['servicetype_id'], // Mandatory [Ambil dari API Tariff -> Reguler = 1]
                            "origincityid" => $requirementProvider['origin_cityid'], // Mandatory [Ambil dari API City]
                            "destinationcityid" => $requirementProvider['destination_cityid'], // Mandatory [Ambil dari API City]
                            "destinationsubdistrictid" => $requirementProvider['destination_subdistric_id'], // Mandatory [Ambil dari API Subdistrict]
                            "priceid" => 1, // Mandatory [Input -> 1]
                            "pickuptimetype" => "SCHEDULE", // Mandatory [Input -> SCHEDULE]
                            "pickuptimeon" => "", // Optional
                            "vehicleid" => $vehicleId, // Mandatory [Ambil dari API Tariff -> Vehicle Type = 2]
                            "vehicleqty" => 1, // Mandatory
                            "shippername" => "BULKY", // Mandatory [Ambil dari Shipper Name Bulky]
                            "shipperphone" => $warehouse->contact_info, // Mandatory [Ambil dari Shipper Phone Bulky]
                            "shipperaddress" => $warehouse->address, // Mandatory
                            "consigneename" => $record->name, // Mandatory
                            "consigneephone" => $record->phone_number, // Mandatory
                            "consigneeaddress" => $consigneeLocation['full_address'] ?? $record->shipping_address, // Mandatory
                            "estdistance" => "", // Mandatory (estimasi jarak)
                            "estprice" => "", // Optional
                            "basisprice" => "ECONOMY", // Mandatory
                            "remark" => $record->shipping_address, // Optional
                            "withinsurance" => $record->shipping->is_insurance, // Mandatory
                            "commodityamount" => $record->total_price, // Mandatory jika withInsurance = 1
                            "insuranceid" => "1", // Mandatory jika withInsurance = 1
                            "premiamount" => $record->total_price * (0.125 / 100), // 0.125% adalah insurance transport Land
                            "locations" => [
                                [
                                    "address" => $warehouse->address,
                                    "latitude" => $warehouse->latitude,
                                    "longitude" => $warehouse->longitude,
                                    "type" => "PICKUP",
                                    "order" => 1,
                                    "picname" => "BULKY",
                                    "picphone" => $warehouse->contact_info,
                                    "detail" => $warehouse->address,
                                ],
                                [
                                    "address" => $consigneeLocation['full_address'] ?? $record->shipping_address,
                                    "latitude" => $record->latitude,
                                    "longitude" => $record->longitude,
                                    "type" => "DELIVERY",
                                    "order" => 2,
                                    "picname" => $record->name,
                                    "picphone" => $record->phone_number,
                                    "detail" => $record->shipping_address
                                ]
                            ],
                            "datadetail" => $itemproducts->toArray()
                        ];
                        $booking = $apiForwarder->post('/createbookingland', 'CREATEBOOKINGLAND', $payload);
                        Log::info('Forwarder Land Transport Booking Payload', $payload);
                        Log::info('Forwarder Land Transport Booking Response', $booking);
                    }
                    break;
                default:
                    $booking = ['error' => 'No valid shipping provider selected'];
                    break;
            }

            if (!collect($booking)->has('error')) {
                $booking_id = $booking['booking_id'] ?? $booking['data']['booking_no'];
                if ($record->shipping->shipping_provider === 'Forwarder') {
                    $apiForwarder = app(ApiRequest::class);
                    $tracking = $apiForwarder->post('/tnt_shareable', 'TNTSHAREABLE', [
                        'booking_no' => $booking['data']['booking_no']
                    ]);
                }
                $record->shipping->update([
                    'booking_id' => $booking_id,
                    'tracking_url' => $tracking['data'][0]['shareablelink'] ?? null,
                ]);
                $record->order_status = OrderStatusEnum::Shipped;
                $record->save();
            } else {
                $this->failureNotificationTitle($booking['error']);
                $this->failure();
            }
        });
    }
}
