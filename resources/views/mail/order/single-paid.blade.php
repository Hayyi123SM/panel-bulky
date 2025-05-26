<x-mail::message>
# Halo {{ $user->name }},

Terima kasih! Pembayaran untuk pesanan Anda telah kami terima.

Berikut adalah detail pesanan Anda:

* Nomor Pesanan: {{ $order->order_number }}
* Tanggal Pemesanan: {{ $order->order_date->format('d F Y') }}
* Total Pembayaran: Rp {{ number_format($order->invoices->first()->amount, 0, ',', '.') }}
* Status Pembayaran: {{ $order->invoices->first()->status }}

Saat ini kami sedang memproses pesanan Anda. Anda akan menerima email notifikasi selanjutnya ketika pesanan Anda telah dikirim.

Jika Anda memiliki pertanyaan, jangan ragu untuk menghubungi tim dukungan pelanggan kami.

Salam hangat,\
Tim bulky.id
</x-mail::message>
