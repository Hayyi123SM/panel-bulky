<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\InvoiceStatusEnum;
use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderStatusEnum;
use App\Events\Order\InvoicePaidEvent;
use App\Events\Order\OrderPaidEvent;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransController extends Controller
{
    public string $serverKey;


    public function __construct()
    {
        $this->serverKey = config('services.midtrans.server_key');
    }

    /**
     * Handles updating the order status and payment status.
     *
     * @param Order $order The order to update the status for.
     * @param Invoice|null $invoice The invoice associated with the order.
     * @return JsonResponse The JSON response indicating the success.
     */
    protected function handleOrderStatus(Order $order, Invoice $invoice = null)
    {
        $order->update([
            'payment_status' => OrderPaymentStatusEnum::PAID,
            'order_status' => OrderStatusEnum::WaitingConfirmation,
            'paid_at' => now(),
        ]);

        $invoice?->update([
            'status' => InvoiceStatusEnum::PAID
        ]);

        return response()->json(['message' => 'Success']);
    }

    /**
     * Handles the pending status.
     *
     * @return JsonResponse The JSON response indicating the success.
     */
    protected function handlePendingStatus()
    {
        return response()->json(['message' => 'Success']);
    }

    /**
     * Updates the payment status and order status of an order to pending,
     * and updates the status of the first invoice of the order to DENY.
     *
     * @param Order $order The order to update.
     * @return JsonResponse The JSON response with a success message.
     */
    protected function handleDenyStatus(Order $order)
    {
        $order->update([
            'payment_status' => OrderPaymentStatusEnum::PENDING,
            'order_status' => OrderStatusEnum::Pending
        ]);

        $invoice = $order->invoices->first();
        $invoice->update([
            'status' => InvoiceStatusEnum::DENY
        ]);

        return response()->json(['message' => 'Success']);
    }

    /**
     * Handles the expiration status of an order.
     *
     * This method updates the payment and order status of the given order to "CANCELED".
     * It also updates the status of the first invoice associated with the order to "EXPIRED".
     *
     * @param Order $order The order to handle the expiration status for.
     * @return JsonResponse The JSON response indicating the success of the operation.
     */
    protected function handleExpireStatus(Order $order)
    {
        $order->update([
            'payment_status' => OrderPaymentStatusEnum::CANCELED,
            'order_status' => OrderStatusEnum::Canceled,
            'cancel_reason' => 'Jatuh tempo pembayaran',
        ]);

        $invoice = $order->invoices->first();
        $invoice->update([
            'status' => InvoiceStatusEnum::EXPIRED
        ]);

        foreach ($order->items as $item) {
            $item->product->update([
                'sold_out' => false
            ]);
        }

        return response()->json(['message' => 'Success']);
    }

    /**
     * Handles the cancel status of an order.
     *
     * This method updates the payment and order status of the given order to "CANCELED".
     * It also updates the status of the first invoice associated with the order to "EXPIRED".
     *
     * @param Order $order The order to handle the cancel status for.
     * @return JsonResponse The JSON response indicating the success of the operation.
     */
    protected function handleCancelStatus(Order $order)
    {
        $order->update([
            'payment_status' => OrderPaymentStatusEnum::CANCELED,
            'order_status' => OrderStatusEnum::Canceled,
            'cancel_reason' => 'Pembatalan pembayaran',
        ]);

        $invoice = $order->invoices->first();
        $invoice->update([
            'status' => InvoiceStatusEnum::EXPIRED
        ]);

        foreach ($order->items as $item) {
            $item->product->update([
                'sold_out' => false
            ]);
        }

        return response()->json(['message' => 'Success']);
    }

    /**
     * Handles the different transaction statuses for an order.
     *
     * This method handles the different transaction statuses received from a notification.
     * It checks the transaction status and performs the corresponding action based on the current status of the order.
     *
     * @return JsonResponse
     */
    public function order(Request $request)
    {
        $order = Order::findOrFail($request->order_id);
        $signature = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $this->serverKey);

        if($signature == $request->signature_key){
            switch ($request->transaction_status) {
                case 'capture':
                    if ($request->payment_type == 'credit_card' && $request->fraud_status == 'accept') {
                        if ($order->order_status == OrderStatusEnum::Pending && $order->payment_status == OrderPaymentStatusEnum::PENDING) {
                            event(new OrderPaidEvent($order));
                            return $this->handleOrderStatus($order, $order->invoices->first());
                        }
                    }
                    break;
                case 'settlement':
                    if ($order->order_status == OrderStatusEnum::Pending && $order->payment_status == OrderPaymentStatusEnum::PENDING) {
                        event(new OrderPaidEvent($order));
                        return $this->handleOrderStatus($order, $order->invoices->first());
                    }
                    break;
                case 'pending':
                    if ($order->order_status == OrderStatusEnum::Pending && $order->payment_status == OrderPaymentStatusEnum::PENDING) {
                        return $this->handlePendingStatus();
                    }
                    break;
                case 'deny':
                    return $this->handleDenyStatus($order);
                case 'expire':
                    return $this->handleExpireStatus($order);
                case 'cancel':
                    return $this->handleCancelStatus($order);
                default:
                    break;
            }
        }

        return response()->json([
            'message' => 'The provided signature is invalid.'
        ]);

    }

    /**
     * Handles the status of an invoice and updates the order status accordingly.
     *
     * This method updates the status of the given invoice to "PAID". It also checks the number of paid invoices associated with the order.
     *
     * If the number of paid invoices is less than the total number of invoices, the payment status of the order is set to "PARTIALLY_PAID" and the order status is set to "Pending".
     * If the number of paid invoices is equal to the total number of invoices, the payment status of the order is set to "PAID" and the order status is set to "Processing".
     *
     * @param Invoice $invoice The invoice to handle the status for.
     * @param Order $order The order associated with the invoice.
     * @return JsonResponse The JSON response indicating the success of the operation.
     */
    protected function handleInvoiceStatus(Invoice $invoice, Order $order)
    {
        $invoice->update(['status' => InvoiceStatusEnum::PAID]);
        $paidCount = $order->invoices->where('status', InvoiceStatusEnum::PAID)->count();

        if ($order->invoices->count() > $paidCount) {
            $order->update([
                'payment_status' => OrderPaymentStatusEnum::PARTIALLY_PAID,
                'order_status' => OrderStatusEnum::Pending
            ]);
        } elseif ($order->invoices->count() == $paidCount) {
            $order->update([
                'payment_status' => OrderPaymentStatusEnum::PAID,
                'order_status' => OrderStatusEnum::WaitingConfirmation,
                'paid_off_at' => now(),
            ]);

            event(new OrderPaidEvent($order));
        }

        return response()->json(['message' => 'Success']);
    }

    /**
     * Handles the pending status of an invoice.
     *
     * This method handles the pending status of the given invoice. It does not perform any database
     * updates or modifications. The only purpose of this method is to return a JSON response with a
     * success message.
     *
     * @return JsonResponse The JSON response indicating the success of the operation.
     */
    protected function handlePendingInvoiceStatus()
    {
        return response()->json(['message' => 'Success']);
    }

    /**
     * Handles the denial status of an invoice and updates the payment and order status accordingly.
     *
     * This method updates the status of the given invoice to "DENY". It then checks the number of invoices associated with the order
     * that have a status of "PAID". If there are more invoices with a status of "PAID" than the total number of invoices,
     * the order's payment status is updated to "PARTIALLY_PAID" and the order status is updated to "Pending". If the number of invoices
     * with a status of "PAID" is equal to the total number of invoices, the order's payment status is updated to "PAID" and the
     * order status is updated to "Processing". A JSON response indicating the success of the operation is returned.
     *
     * @param Invoice $invoice The invoice to handle the denial status for.
     * @param Order $order The order associated with the invoice.
     * @return JsonResponse The JSON response indicating the success of the operation.
     */
    protected function handleDenyInvoiceStatus(Invoice $invoice, Order $order)
    {
        $invoice->update(['status' => InvoiceStatusEnum::DENY]);
        $paidCount = $order->invoices->where('status', InvoiceStatusEnum::PAID)->count();

        if ($order->invoices->count() > $paidCount) {
            $order->update([
                'payment_status' => OrderPaymentStatusEnum::PARTIALLY_PAID,
                'order_status' => OrderStatusEnum::Pending
            ]);
        } elseif ($order->invoices->count() == $paidCount) {
            $order->update([
                'payment_status' => OrderPaymentStatusEnum::PAID,
                'order_status' => OrderStatusEnum::WaitingConfirmation,
                'paid_off_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Success']);
    }

    /**
     * Handles the expiration status of an invoice and updates the payment and order status accordingly.
     *
     * This method updates the status of the given invoice to "EXPIRED". It then calculates the number of paid invoices
     * associated with the order and compares it with the total number of invoices. If there are any unpaid invoices, the
     * payment status of the order is updated to "PARTIALLY_PAID" and the order status is set to "Pending". If all invoices
     * are paid, the payment status is updated to "PAID" and the order status is set to "Processing".
     *
     * @param Invoice $invoice The invoice to handle the expiration status for.
     * @param Order $order The order associated with the invoice.
     * @return JsonResponse The JSON response indicating the success of the operation.
     */
    protected function handleExpireInvoiceStatus(Invoice $invoice, Order $order)
    {
        $invoice->update(['status' => InvoiceStatusEnum::EXPIRED]);
        $paidCount = $order->invoices->where('status', InvoiceStatusEnum::PAID)->count();

        if ($order->invoices->count() > $paidCount) {
            $order->update([
                'payment_status' => OrderPaymentStatusEnum::PARTIALLY_PAID,
                'order_status' => OrderStatusEnum::Pending
            ]);
        } elseif ($order->invoices->count() == $paidCount) {
            $order->update([
                'payment_status' => OrderPaymentStatusEnum::PAID,
                'order_status' => OrderStatusEnum::WaitingConfirmation,
                'paid_off_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Success']);
    }

    /**
     * Handles the cancellation status of an invoice and updates the order status accordingly.
     *
     * This method updates the status of the given invoice to "CANCELLED". It also checks the payment status of the associated order based on the number of paid invoices.
     * If there are more unpaid invoices, the payment status is updated to "PARTIALLY_PAID" and the order status is set to "Pending".
     * If all invoices are paid, the payment status is updated to "PAID" and the order status is set to "Processing".
     *
     * @param Invoice $invoice The invoice to handle the cancellation status for.
     * @param Order $order The order associated with the invoice.
     * @return JsonResponse The JSON response indicating the success of the operation.
     */
    protected function handleCancelInvoiceStatus(Invoice $invoice, Order $order)
    {
        $invoice->update(['status' => InvoiceStatusEnum::CANCELLED]);
        $paidCount = $order->invoices()->where('status', InvoiceStatusEnum::PAID)->count();

        if ($order->invoices->count() > $paidCount) {
            $order->update([
                'payment_status' => OrderPaymentStatusEnum::PARTIALLY_PAID,
                'order_status' => OrderStatusEnum::Pending
            ]);
        } elseif ($order->invoices->count() == $paidCount) {
            $order->update([
                'payment_status' => OrderPaymentStatusEnum::PAID,
                'order_status' => OrderStatusEnum::WaitingConfirmation,
                'paid_off_at' => now(),
            ]);
        }

        return response()->json(['message' => 'Success']);
    }

    /**
     * Processes the notification and handles the invoice status based on the notification type.
     *
     * This method retrieves the notification response from the Notification class and
     * fetches the invoice and order associated with the notification order ID.
     * It then checks the transaction status of the notification and performs the
     * appropriate action based on the status. If the status is "capture", it checks
     * the payment type and fraud status to determine if it can handle the invoice status.
     * If the status is "settlement", "pending", "deny", "expire", or "cancel", it proceeds
     * with handling the invoice status accordingly. If the status is not recognized, it does nothing.
     *
     * @return JsonResponse The JSON response indicating the success of the operation.
     */
    public function invoice(Request $request)
    {
        $invoice = Invoice::findOrFail($request->input('order_id'));
        $order = $invoice->order;
        $signature = hash('sha512', $request->order_id . $request->status_code . $request->gross_amount . $this->serverKey);

        if ($signature == $request->input('signature_key')) {
            switch ($request->transaction_status) {
                case 'capture':
                    if ($request->payment_type == 'credit_card' && $request->fraud_status == 'accept') {
                        if ($invoice->status == InvoiceStatusEnum::PENDING) {
                            event(new InvoicePaidEvent($invoice));
                            return $this->handleInvoiceStatus($invoice, $order);
                        }
                    }
                    break;
                case 'settlement':
                    if ($invoice->status == InvoiceStatusEnum::PENDING) {
                        event(new InvoicePaidEvent($invoice));
                        return $this->handleInvoiceStatus($invoice, $order);
                    }
                    break;
                case 'pending':
                    if ($invoice->status == InvoiceStatusEnum::PENDING) {
                        return $this->handlePendingInvoiceStatus();
                    }
                    break;
                case 'deny':
                    return $this->handleDenyInvoiceStatus($invoice, $order);
                case 'expire':
                    return $this->handleExpireInvoiceStatus($invoice, $order);
                case 'cancel':
                    return $this->handleCancelInvoiceStatus($invoice, $order);
                default:
                    break;
            }
        }

        return response()->json([
            'message' => 'The provided signature is invalid.'
        ]);
    }
}
