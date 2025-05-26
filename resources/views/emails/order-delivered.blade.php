<x-mail::message>
# Halo {{ $order->user->name }},

Kami senang memberitahu Anda bahwa pesanan Anda dengan nomor {{ $order->order_number }} telah berhasil dikirimkan.

Semoga produk yang Anda terima sesuai dengan harapan Anda. Pendapat Anda sangat berarti bagi kami dan pelanggan lain.

Klik tautan di bawah ini untuk memberikan ulasan Anda:

<x-mail::button :url="$url">
Tulis Ulasan
</x-mail::button>

Ulasan Anda akan membantu kami meningkatkan layanan dan produk kami.

Terima kasih telah berbelanja di Bulky.id!

Salam,<br>
Tim Bulky.id
</x-mail::message>
