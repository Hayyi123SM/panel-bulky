<x-mail::message>
# Halo {{ $user->name }},

Terima kasih telah berbelanja di bulky.id!

Kami telah menerima pesanan Anda dan saat ini sedang menunggu pembayaran. Berikut adalah detail pesanan Anda:

* Nomor Pesanan: {{ $order->order_number}}
* Tanggal Pemesanan: {{ $order->order_date->format('d F Y') }}
* Total Tagihan: Rp {{ number_format($order->total_price, 0, ',', '.') }}
* Status Pembayaran: {{ $order->payment_status->getLabel() }}

Silakan selesaikan pembayaran Anda dalam waktu {{ $order->created_at->addHours(23)->format('d/m/Y - H:i') }} agar pesanan Anda dapat segera kami proses

<x-mail::button :url="$url">
    Bayar Sekarang
</x-mail::button>

Jika Anda memiliki pertanyaan atau memerlukan bantuan, jangan ragu untuk menghubungi tim dukungan pelanggan kami.

Salam hangat,\
Tim bulky.id
</x-mail::message>
