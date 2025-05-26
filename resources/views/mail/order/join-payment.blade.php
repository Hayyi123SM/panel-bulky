<x-mail::message>
# Halo {{ $user->name }},

Anda diundang oleh **{{ $order->user->name }}** untuk membayar sebagian dari pesanan **{{ $order->order_number }}** secara patungan.

Anda dapat menentukan sendiri jumlah yang akan Anda bayar. Silahkan melakukan pembayaran melalui menu Split Payment pada halaman dashboard bulky.id Anda.
Atau klik tombol "Bayar Sekarang" dibawah ini.

<x-mail::button :url="$url" color="green">
    Bayar Sekarang
</x-mail::button>

Terima kasih atas partisipasi Anda!

Salam,\
Tim bulky.id
</x-mail::message>
