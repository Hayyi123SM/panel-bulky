<x-mail::message>
# Halo {{ $user->name }},

{{ $invoice->user->name }} telah melakukan pembayaran patungan sebesar Rp{{ number_format($invoice->amount, 0, ',', '.') }} untuk pesanan {{ $invoice->order->order_number }}.

@if($remainingAmount == 0)
Pesanan Anda telah lunas dan sedang diproses. Anda akan menerima notifikasi selanjutnya ketika pesanan Anda telah dikirim.
@else
Sisa pembayaran untuk pesanan ini adalah Rp{{ number_format($remainingAmount, 0, ',', '.') }}.
@endif

Terima kasih,\
Tim bulky.id
</x-mail::message>
