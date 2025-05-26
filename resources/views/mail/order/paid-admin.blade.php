<x-mail::message>
# Halo {{ $user->name }},

Ada pesanan baru yang telah terbayar lunas di bulky.id. Berikut detailnya:

* Nomor Pesanan: {{ $order->order_number }}
* Tanggal Pemesanan: {{ $order->order_date->format('d F Y') }}
* Pelanggan: {{ $order->user->name }}
* Email Pelanggan: {{ $order->user->email }}
* Total Pembayaran: Rp {{ number_format($order->total_price, 0, ',', '.') }}

Silakan segera proses pesanan ini.

Anda dapat melihat detail pesanan lebih lanjut di dashboard admin.

Salam,\
Sistem bulky.id
</x-mail::message>
