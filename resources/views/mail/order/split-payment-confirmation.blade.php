<x-mail::message>
# Halo {{ $user->name }},

Terima kasih telah melakukan pembayaran patungan sebesar **Rp{{ number_format($invoice->amount, 0, ',', '.') }}** untuk pesanan **{{ $invoice->order->order_number }}**.

Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi tim dukungan pelanggan kami.

Salam,\
Tim bulky.id
</x-mail::message>
