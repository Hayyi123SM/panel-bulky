<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\InvoiceStatusEnum;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Events\Order\OrderPaidEvent;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Webhook
 *
 * Handles the invoice update callback from an external service.
 *
 * This method processes the incoming request to handle status updates for orders and invoices.
 * It verifies the callback token and then updates the status of the order or invoice based
 * on the provided `external_id` and `status`.
 *
 * @param Request $request The HTTP request object containing callback data.
 * @return JsonResponse The JSON response indicating the result of the operation.
 */
class XenditController extends Controller
{
    /**
     * Xendit
     *
     * Handles the invoice update callback from an external service.
     *
     * This method processes the incoming request to handle status updates for orders and invoices.
     * It verifies the callback token and then updates the status of the order or invoice based
     * on the provided `external_id` and `status`.
     *
     * @param Request $request The HTTP request object containing callback data.
     * @return JsonResponse The JSON response indicating the result of the operation.
     */
    public function handleInvoice(Request $request): JsonResponse
    {
        $key = $request->header('x-callback-token');
        if ($key != config('xendit.verification_token')) {
            return response()->json('failed', 422);
        }

        $order = Order::find($request->input('external_id'));
        $status = $request->input('status');

        if ($order) {
            return $this->handleOrderStatusUpdate($order, $status);
        }

        $invoice = Invoice::find($request->input('external_id'));
        if ($invoice) {
            return $this->handleInvoiceStatusUpdate($invoice, $status);
        }

        return response()->json('ok');
    }

    /**
     * Update the status of an order based on the provided status parameter.
     *
     * @param Order $order The order instance to be updated.
     * @param string $status The new status to set for the order.
     * @return JsonResponse A JSON response indicating success or failure.
     */
    private function handleOrderStatusUpdate(Order $order, string $status): JsonResponse
    {
        if ($order->payment_status != OrderPaymentStatusEnum::PENDING) {
            return response()->json('Failed: ORDER_STATUS', 404);
        }

        $invoice = $order->invoices->first();
        if (!$invoice) {
            return response()->json('Failed: INVOICE', 404);
        }

        switch ($status) {
            case 'PAID':
                $order->update([
                    'payment_status' => OrderPaymentStatusEnum::PAID,
                    'order_status' => OrderStatusEnum::WaitingConfirmation,
                    'paid_at' => now(),
                ]);
                $invoice->update(['status' => InvoiceStatusEnum::PAID]);
                event(new OrderPaidEvent($order));
                break;
            case 'EXPIRED':
                $order->update([
                    'payment_status' => OrderPaymentStatusEnum::CANCELED,
                    'order_status' => OrderStatusEnum::Canceled,
                    'cancel_reason' => 'Jatuh tempo pembayaran',
                ]);
                $invoice->update(['status' => InvoiceStatusEnum::EXPIRED]);
                foreach ($order->items as $item) {
                    $item->product->update(['sold_out' => false]);
                }
                break;
        }

        return response()->json('success');
    }

    /**
     * Update the status of an invoice based on the provided status parameter.
     *
     * @param Invoice $invoice The invoice instance to be updated.
     * @param string $status The new status to set for the invoice.
     * @return JsonResponse A JSON response indicating success or failure.
     */
    private function handleInvoiceStatusUpdate(Invoice $invoice, string $status): JsonResponse
    {
        if($invoice->status != InvoiceStatusEnum::PENDING){
            return response()->json('Failed: ORDER_STATUS', 404);
        }

        $order = $invoice->order;
        $invoice->update(['status' => $status == 'PAID' ? InvoiceStatusEnum::PAID : InvoiceStatusEnum::EXPIRED]);

        if (!$order) {
            return response()->json('Failed: INVOICE', 404);
        }

//        $paidCount = $order->invoices->where('status', InvoiceStatusEnum::PAID)->count();
        $totalPaid = $order->invoices->where('status', InvoiceStatusEnum::PAID)->sum('amount');

        if($totalPaid >= $order->total_price){
            $order->update([
                'payment_status' => OrderPaymentStatusEnum::PAID,
                'order_status' => OrderStatusEnum::WaitingConfirmation,
                'paid_at' => now(),
            ]);
            event(new OrderPaidEvent($order));
        } else {
            $order->update([
                'payment_status' => OrderPaymentStatusEnum::PARTIALLY_PAID,
                'order_status' => OrderStatusEnum::Pending
            ]);
        }

//        if($paidCount > 0){
//            if ($order->invoices->count() > $paidCount) {
//                $order->update([
//                    'payment_status' => OrderPaymentStatusEnum::PARTIALLY_PAID,
//                    'order_status' => OrderStatusEnum::Pending
//                ]);
//            } elseif ($order->invoices->count() == $paidCount) {
//                $order->update([
//                    'payment_status' => OrderPaymentStatusEnum::PAID,
//                    'order_status' => OrderStatusEnum::WaitingConfirmation,
//                    'paid_off_at' => now(),
//                ]);
//                event(new OrderPaidEvent($order));
//            }
//        }

        return response()->json('success');
    }

    private function rollbackSoldStatus(Order $order)
    {
        $order->items->each(function ($item) {
            $item->product->update(['sold_out' => false]);
        });
    }
}
