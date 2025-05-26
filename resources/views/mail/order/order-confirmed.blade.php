<x-mail::message>
# Halo {{ $user->name }},

Kami dengan senang hati mengabarkan bahwa pesanan Anda dengan nomor {{ $order->order_number }} telah berhasil dikonfirmasi dan saat ini sedang kami proses.

Detail pesanan Anda:
* Nomor Pesanan: {{ $order->order_number }}
* Tanggal Pemesanan: {{ $order->order_date->format('d F Y') }}
* Total Pembayaran: Rp {{ number_format($order->total_price, 0, ',', '.') }}

Anda akan menerima email notifikasi selanjutnya ketika pesanan Anda telah siap {{ $order->shipping_method == \App\Enums\ShippingMethodEnum::COURIER_PICKUP ? 'dikirim.' : 'diambil.' }}.

Terima kasih telah berbelanja di bulky.id!

Salam,\
Tim bulky.id
</x-mail::message>
