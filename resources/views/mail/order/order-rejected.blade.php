<x-mail::message>
Halo {{ $user->name }},

Dengan menyesal kami informasikan bahwa pesanan Anda dengan nomor {{ $order->order_number }} telah ditolak.

Alasan penolakan:\
{{ $reason }}

Anda akan menerima pengembalian dana sebesar Rp {{ number_format($order->paid_amount, 0, ',', '.') }} dalam waktu 1x24 jam.

Kami mohon maaf atas ketidaknyamanan yang mungkin ditimbulkan. Jika Anda memiliki pertanyaan lebih lanjut atau membutuhkan bantuan, jangan ragu untuk menghubungi tim dukungan pelanggan kami.

Salam,\
Tim bulky.id
</x-mail::message>
