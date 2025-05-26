<?php

namespace App\Http\Resources;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Invoice */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
//        $remaining = $this->order->invoices()->sum('amount');

        return [
            'id' => $this->id,
            'amount' => [
                'numeric' => $this->amount,
                'formatted' => 'Rp ' . number_format($this->amount,0, ',', '.')
            ],
//            'remaining' => [
//                'numeric' => $remaining,
//                'formatted' => 'Rp ' . number_format($remaining,0, ',', '.')
//            ],
            'status' => [
                'value' => $this->status->value,
                'label' => $this->status->getLabel(),
                'color' => $this->status->getColor(),
                'description' => $this->status->getDescription()
            ],
            'created_at' => $this->created_at->format('d M Y - H:i'),
            'updated_at' => $this->updated_at->format('d M Y - H:i'),
            'need_create_payment' => is_null($this->midtrans_snap_token),
            'need_input_amount' => $this->amount == 0,
            'need_select_payment_method' => is_null($this->payment_method_id),

            'payment_url' => $this->xendit_invoice_url,

            'order' => new OrderResource($this->whenLoaded('order')),
            'user' => new UserResource($this->user),
            'payment_method' => new PaymentMethodResource($this->paymentMethod),
        ];
    }
}
