<?php

namespace App\Models;

use App\Enums\InvoiceStatusEnum;
use App\Observers\InvoiceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ObservedBy([InvoiceObserver::class])]
class Invoice extends Model
{
    use SoftDeletes, HasUuids;

    protected $fillable = [
        'payment_method_id',
        'user_id',
        'order_id',
        'amount',
        'status',
        'midtrans_snap_token',
        'midtrans_redirect_url',
        'xendit_id',
        'xendit_invoice_url'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function paymentMethod(): BelongsTo
    {
        return $this->belongsTo(PaymentMethod::class, 'payment_method_id');
    }

    protected function casts(): array
    {
        return [
            'status' => InvoiceStatusEnum::class,
        ];
    }
}
