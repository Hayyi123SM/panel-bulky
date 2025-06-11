<?php

namespace App\Http\Controllers\Api\Order;

use App\Enums\OrderPaymentTypeEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\Invoice\CreatePaymentRequest;
use App\Http\Requests\Api\Order\Invoice\SetAmountRequest;
use App\Http\Resources\InvoiceResource;
use App\Http\Resources\PaymentMethodCollection;
use App\Http\Resources\PaymentMethodGroupResource;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentMethodGroup;
use Auth;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use Midtrans\Config;
use Midtrans\Snap;
use Throwable;
use Xendit\Invoice\CreateInvoiceRequest;
use Xendit\Invoice\InvoiceApi;
use Xendit\XenditSdkException;

/**
 * @group Orders
 *
 * Handles invoice-related operations for the application.
 * @subgroup Invoices
 * @authenticated
 */
class InvoiceController extends Controller
{
    /**
     * Get Payment Method
     *
     * Retrieves the payment methods grouped by their payment method group.
     *
     * @return AnonymousResourceCollection
     */
    public function getPaymentMethod()
    {
        $groups = PaymentMethodGroup::has('paymentMethods')
            ->orderBy('name')
            ->get();
        return PaymentMethodGroupResource::collection($groups);
    }

    /**
     * List Invoices
     *
     * Retrieves all invoices related to a given order.
     *
     * @param Order $order The order object.
     *
     * @return AnonymousResourceCollection The collection of invoice resources.
     */
    public function getInvoicesByOrder(Order $order)
    {
        $invoices = $order->invoices->load(['user', 'paymentMethod']);
        return InvoiceResource::collection($invoices);
    }

    /**
     * Get Invoices By Order
     *
     * Retrieves a specific invoice related to a given order for the authenticated user.
     *
     * @param Order $order The order object.
     *
     * @return InvoiceResource The invoice resource.
     */
    public function getMyInvoiceByOrder(Order $order)
    {
        $userId = request()->user()->id;
        $invoice = $order->invoices()
            ->whereUserId($userId)
            ->with(['user', 'paymentMethod', 'order'])
            ->first();

        return new InvoiceResource($invoice);
    }

    /**
     * Set Amount
     *
     * Sets the amount for a specific invoice.
     *
     * @param SetAmountRequest $request The request object containing the user and invoice information.
     *
     * @return InvoiceResource The resource representing the updated invoice.
     *
     * @throws ModelNotFoundException If the invoice is not found for the given user.
     */
    public function setInvoiceAmount(SetAmountRequest $request)
    {
        $userId = $request->user()->id;
        $invoice = Invoice::whereUserId($userId)
            ->findOrFail($request->input('invoice_id'))
            ->load(['user', 'order', 'paymentMethod']);

        $totalInvoice = $invoice->order->invoices->sum('amount');
        $remaining = $invoice->order->total_price - $totalInvoice;

        if ($request->integer('amount') > $remaining) {
            throw ValidationException::withMessages([
                'amount' => ['Jumlah yang dimasukkan melebihi jumlah yang tersisa. Sisa jumlah: Rp ' . number_format($remaining, 0, ',', '.')]
            ]);
        }

        $invoice->amount = $request->input('amount');
        $invoice->save();

        return new InvoiceResource($invoice);
    }

    /**
     * Create Payment
     *
     * @throws Exception
     */
    public function createPayment(CreatePaymentRequest $request)
    {
        $user = $request->user();
        $invoice = Invoice::whereUserId($user->id)->findOrFail($request->input('invoice_id'));
        $paymentMethod = PaymentMethod::findOrFail($request->input('payment_method'));

        $apiInstance = new InvoiceApi();

        $redirectUrl = 'https://bulky.wms-liquid8.online/redirect/?type=order&order_id=' . $invoice->order_id . '&payment_success=true';
        $orderId = $invoice->order_id;
        if ($invoice->order->payment_method == OrderPaymentTypeEnum::SplitPayment) {
            $orderId = $invoice->id;
            $redirectUrl = 'https://bulky.wms-liquid8.online/redirect/?type=order-split&order_id=' . $invoice->order_id . '&payment_success=true';
        }

        $name = $invoice->order->items()->first()->product->name;

        $apiRequest = new CreateInvoiceRequest([
            'external_id' => $orderId,
            'description' => $name,
            'amount' => $invoice->amount,
            'invoice_duration' => 800,
            //            'invoice_duration' => $invoice->order->payment_method == OrderPaymentTypeEnum::SinglePayment ? 900 : 3600,
            'success_redirect_url' => $redirectUrl,
            'payment_methods' => [$paymentMethod->code],
        ]);

        try {
            $result = $apiInstance->createInvoice($apiRequest);
            $invoice->xendit_id = $result->getId();
            $invoice->xendit_invoice_url = $result->getInvoiceUrl();
            $invoice->payment_method_id = $request->input('payment_method');
            $invoice->save();

            return new InvoiceResource($invoice);
        } catch (XenditSdkException $exception) {
            throw new Exception($exception->getMessage());
        }
    }
}
