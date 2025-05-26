<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\OrderStatusEnum;
use App\Events\Order\DeliveryInProgressEvent;
use App\Events\Order\OrderDeliveredEvent;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderShipping;
use Illuminate\Http\Request;

/**
 * @group Webhook
 *
 * Handles incoming webhook requests from Deliveree and updates order and shipping statuses.
 *
 * This method processes the webhook payload and updates the associated order's status
 * and shipping information based on the received delivery status. It also dispatches
 * appropriate events when specific statuses are encountered.
 *
 * @param Request $request The HTTP request object containing the webhook payload.
 *
 * @return \Illuminate\Http\JsonResponse A JSON response indicating the success of the operation.
 */
class DelivereeController extends Controller
{
    /**
     * Deliveree
     *
     * Handles the webhook request for updating order and shipping statuses.
     *
     * Processes the request by validating the given status and updating the
     * associated order and shipping details accordingly. Dispatches events
     * based on the current status of the order and saves any changes.
     *
     * Accepted statuses include:
     * - delivery_completed
     * - locating_driver
     * - driver_accept_booking
     * - delivery_in_progress
     * - locating_driver_timeout
     * - canceled
     *
     * The function retrieves the respective `OrderShipping` record using the provided
     * booking ID and updates the associated `Order` and `shipping` data if applicable.
     *
     * @param Request $request The incoming HTTP request, containing details such as
     *                         status and booking ID for processing.
     * @return \Illuminate\Http\JsonResponse A JSON response indicating the operation's success.
     */
    public function index(Request $request)
    {
//        $key = $request->header('Authorization');

//        if ($key == config('deliveree.webhook_authorization')) {
//            \Log::alert('Deliveree webhook received: ' . json_encode($request->all()));
//        } else {
//            \Log::alert('Deliveree webhook received with invalid key: ' . json_encode($request->all()));
//        }

//        return response()->json(['error' => 'Unauthorized.'], 401);

        $statuses = [
            'delivery_completed',
            'locating_driver',
            'driver_accept_booking',
            'delivery_in_progress',
            'locating_driver_timeout',
            'canceled',
        ];

        $status = $request->input('status');
        $booking_id = $request->input('id');
//        $order_number = $request->input('job_order_number');

        if (in_array($status, $statuses)) {
            $order_shipping = OrderShipping::where('booking_id', $booking_id)->first();
            if($order_shipping){
                $order = $order_shipping->order;

                if ($order) {
                    switch ($status) {
                        case 'delivery_completed':
                            $order->order_status = OrderStatusEnum::Delivered;
                            OrderDeliveredEvent::dispatch($order);
                            break;
                        case 'delivery_in_progress':
                            $order->order_status = OrderStatusEnum::Shipped;
                            DeliveryInProgressEvent::dispatch($order);
                            break;
                        case 'locating_driver_timeout':
                        case 'canceled':
                            $order->order_status = OrderStatusEnum::Processing;
                            break;
                    }

                    $order->save();

                    $order->shipping()->update([
                        'booking_status' => $status,
                        'tracking_url' => $request->input('tracking_url', $order->shipping->tracking_url),
                    ]);
                }
            }
        }

        return response()->json(['success' => true]);
    }
}
