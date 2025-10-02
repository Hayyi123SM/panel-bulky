<?php

namespace App\Services\Forwarder;

use App\Models\Order;
use Illuminate\Support\Facades\Log;

class TrackingService
{
    protected ApiRequest $apiForwarder;

    public function __construct(ApiRequest $apiForwarder)
    {
        $this->apiForwarder = $apiForwarder;
    }

    /**
     * Ambil data tracking dari provider forwarder
     *
     * @param Order $order
     * @return array|null
     */
    public function getTracking(Order $order): ?array
    {
        $booking_no = $order->shipping->booking_id;
        if ($booking_no === null) {
            return null;
        }

        $tracking = $this->apiForwarder->post('/trackandtrace', 'TRACKANDTRACE', [
            "ref_cust_id" => "fdx_liquid8",
            "booking_no" => $booking_no,
        ]);

        if ($tracking['msg'] !== 'Success' && $tracking['data'] === null) {
            Log::error('Error tracking order', ['msg' => $tracking['msg'], 'data' => $tracking['data']]);
            return null;
        }

        if ($tracking['msg'] === 'Success' && $tracking['data'] === null) {
            return null;
        }

        return $tracking['data'][0] ?? null;
    }
}
