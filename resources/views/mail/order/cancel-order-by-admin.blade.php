<x-mail::message>
# Halo {{ $user->name }},

Dengan menyesal kami informasikan bahwa pesanan Anda dengan nomor {{ $order->order_number }} telah dibatalkan.

Alasan pembatalan:\
{{ $order->cancel_reason }}

@if($hasPaid)
Anda akan menerima pengembalian dana penuh sebesar Rp {{ number_format($order->paid_amount, 0, ',', '.') }} dalam waktu 1x24 jam.
@endif

Kami mohon maaf atas ketidaknyamanan yang mungkin ditimbulkan. Jika Anda memiliki pertanyaan lebih lanjut, jangan ragu untuk menghubungi tim dukungan pelanggan kami.

Salam,\
Tim bulky.id
</x-mail::message>
