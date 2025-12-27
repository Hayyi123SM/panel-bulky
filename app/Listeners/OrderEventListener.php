<?php

namespace App\Listeners;

use App\Enums\OrderPaymentTypeEnum;
use App\Enums\ShippingMethodEnum;
use App\Events\Order\AlreadyPickedUpEvent;
use App\Events\Order\DeliveryInProgressEvent;
use App\Events\Order\InvoicePaidEvent;
use App\Events\Order\OrderCanceledEvent;
use App\Events\Order\OrderConfirmedEvent;
use App\Events\Order\OrderCreatedEvent;
use App\Events\Order\OrderDeliveredEvent;
use App\Events\Order\OrderPaidEvent;
use App\Events\Order\OrderRejectedEvent;
use App\Events\Order\ReadyToPickUpEvent;
use App\Jobs\SendWhatsappToSeller;
use App\Mail\OrderDelivered;
use App\Models\Admin;
use App\Models\User;
use App\Notifications\Order\AlreadyPickedUpNotification;
use App\Notifications\Order\CancelOrderByAdminNotification;
use App\Notifications\Order\InvoicePaidNotification;
use App\Notifications\Order\JoinPaymentNotification;
use App\Notifications\Order\OrderConfirmedNotification;
use App\Notifications\Order\OrderCreatedNotification;
use App\Notifications\Order\OrderPaidAdminNotification;
use App\Notifications\Order\OrderRejectedNotification;
use App\Notifications\Order\ReadyToPickupNotification;
use App\Notifications\Order\SplitPaymentConfirmationNotification;
use App\Notifications\Order\SplitPaymentConfirmationOwnerNotification;
use App\Notifications\Order\SplitPaymentOrderFullPaidNotification;
use App\Services\WhatsApp\WhatsApp;
use Illuminate\Events\Dispatcher;

class OrderEventListener
{
    public function orderCreated(OrderCreatedEvent $event): void
    {
        $order = $event->order;
        $order->user->notify(new OrderCreatedNotification($order));

        $url = $order->payment_method == OrderPaymentTypeEnum::SplitPayment
            ? 'https://bulky.id/redirect?type=order-split&order_id=' . $order->id
            : 'https://bulky.id/redirect?type=order&order_id=' . $order->id;
        $total = number_format($order->total_price, 0, ',', '.');
        $message = "Halo {$order->user->name}, pesanan Anda sudah masuk.\n\nNomor pesanan: $order->order_number\nTotal: Rp $total \n\nSilahkan segera menyeselesaikan pembayaran.\nKlik di sini untuk bayar: $url";
        WhatsApp::sendMessage($order->user->phone_number, $message);

        if ($order->payment_method == OrderPaymentTypeEnum::SplitPayment) {
            foreach ($order->invoices as $invoice) {
                $invoice->user->notify(new JoinPaymentNotification($order));
            }
        }
    }

    public function orderPaid(OrderPaidEvent $event): void
    {
        $order = $event->order;

        $total = 'Rp ' . number_format($order->total_price, 0, ',', '.');
        $tax = 'Rp ' . number_format($order->tax_amount, 0, ',', '.');
        $shippingAddress = $order->shipping_method == ShippingMethodEnum::COURIER_PICKUP ? $order->shipping_address : '-';
        $shippingCost = 'Rp ' . number_format($order->shipping_method == ShippingMethodEnum::COURIER_PICKUP ? $order->shipping->shipping_cost : 0, 0, ',', '.');
        $discount = 'Rp ' . number_format($order->discount_amount, 0, ',', '.');
        $totalDiscount = 0;
        $priceAfterDiscount = 'Rp ' . number_format($order->total_price - $order->tax_amount - ($order->shipping->shipping_cost ?? 0), 0, ',', '.');
        $priceBeforeDiscount = 'Rp ' . number_format($order->total_price - $order->tax_amount - ($order->shipping->shipping_cost ?? 0) - $order->discount_amount, 0, ',', '.');

        if ($order->payment_method == OrderPaymentTypeEnum::SplitPayment) {
            $message = <<<EOT
                    💳 Pembayaran Anda Telah Kami Terima.

                    Nama: {$order->user->name}
                    Alamat: {$shippingAddress}
                    No. WhatsApp: {$order->phone_number}

                    Nomer Pesanan: 
                    {$order->order_number}

                    📥 Rincian Produk:

                    EOT;

            $items = $order->items;
            foreach ($items as $item) {
                $totalDiscount += $item->discount_amount;
                $totalItem = 'Rp ' . number_format(($item->price * $item->quantity) - $item->discount_amount, 0, ',', '.');
                // $message .= "{$item->product->name_trans} \n$item->quantity x $item->price = $totalItem \n";
                $message .= "Produk : {$item->product->name_trans} \n";
                $message .= "Jumlah Unit : $item->quantity \n";
                $message .= "Harga : Rp " . number_format($item->price, 0, ',', '.') . " \n";
                $message .= "Diskon : - Rp " . number_format($item->discount_amount, 0, ',', '.') . " \n";
                $message .= "--------------------------\n";
                $message .= "Harga Setelah Diskon : Rp " . number_format($item->price - $item->discount_amount, 0, ',', '.') . " \n\n";
            }
            $message .= "\n💰 Rincian Pembayaran:\n";
            $message .= "Total Harga : $priceBeforeDiscount \n";
            $message .= "Total Diskon : - " . $totalDiscount . " \n";
            $message .= "--------------------------\n";
            $message .= "Harga Setelah Diskon : $priceAfterDiscount \n";
            $message .= "--------------------------\n";
            $message .= "Ongkir : $shippingCost \n";
            $message .= "PPN : $tax \n";
            $message .= "--------------------------\n";
            $message .= "✅ Total Pembayaran : $total \n";
            $message .= "==========================\n\n";
            $message .= "\nTanggal Order : " . $order->created_at->format('d-m-Y H:i');
            $message .= "\n📦 Pesanan Anda akan segera kami proses sesuai detail di atas.";
            $message .= "\n\nTerimakasih telah order dari Bulky.id. \n\n Untuk pertanyaan silahkan hubungi kami di 📩 admin@bulky.id, Atau untuk info lebih lanjut di 🌐 https://bulky.id";
            // $message .= "\n-----------------";
            // $message .= "\nSalam Hangat, \nAdmin Bulky";

            foreach ($order->invoices() as $invoice) {
                $invoice->user->notify(new SplitPaymentOrderFullPaidNotification($invoice));
                WhatsApp::sendMessage($invoice->user->phone_number, $message);
            }
        } else {
            $order->user->notify(new InvoicePaidNotification($order));

            $message = <<<EOT
                    💳 Pembayaran Anda Telah Kami Terima.

                    Nama: {$order->user->name}
                    Alamat: {$shippingAddress}
                    No. WhatsApp: {$order->phone_number}

                    Nomer Pesanan: 
                    {$order->order_number}

                    📥 Rincian Produk:

                    EOT;

            $items = $order->items;
            foreach ($items as $item) {
                $totalDiscount += $item->discount_amount;
                $totalItem = 'Rp ' . number_format(($item->price * $item->quantity) - $item->discount_amount, 0, ',', '.');
                // $message .= "{$item->product->name_trans} \n$item->quantity x $item->price = $totalItem \n";
                $message .= "Produk : {$item->product->name_trans} \n";
                $message .= "Jumlah Unit : $item->quantity \n";
                $message .= "Harga : Rp " . number_format($item->price, 0, ',', '.') . " \n";
                $message .= "Diskon : - Rp " . number_format($item->discount_amount, 0, ',', '.') . " \n";
                $message .= "--------------------------\n";
                $message .= "Harga Setelah Diskon : Rp " . number_format($item->price - $item->discount_amount, 0, ',', '.') . " \n\n";
            }
            $message .= "\n💰 Rincian Pembayaran:\n";
            $message .= "Total Harga : $priceBeforeDiscount \n";
            $message .= "Total Diskon : - " . $totalDiscount . " \n";
            $message .= "--------------------------\n";
            $message .= "Harga Setelah Diskon : $priceAfterDiscount \n";
            $message .= "--------------------------\n";
            $message .= "Ongkir : $shippingCost \n";
            $message .= "PPN : $tax \n";
            $message .= "--------------------------\n";
            $message .= "✅ Total Pembayaran : $total \n";
            $message .= "==========================\n\n";
            $message .= "\nTanggal Order : " . $order->created_at->format('d-m-Y H:i');
            $message .= "\n📦 Pesanan Anda akan segera kami proses sesuai detail di atas.";
            $message .= "\n\nTerimakasih telah order dari Bulky.id. \n\n Untuk pertanyaan silahkan hubungi kami di 📩 admin@bulky.id, Atau untuk info lebih lanjut di 🌐 https://bulky.id";
            // $message .= "\n-----------------";
            // $message .= "\nSalam Hangat, \nAdmin Bulky";

            WhatsApp::sendMessage($order->user->phone_number, $message);
        }

        $sellerMessage = <<<EOT
                    💳 Pembayaran Telah Diterima

                    Buyer: $order->name
                    Alamat: $shippingAddress
                    No. WhatsApp: $order->phone_number

                    Nomer Pesanan:
                    $order->order_number

                    📥 Rincian Produk:

                    EOT;

        $items = $order->items;
        foreach ($items as $item) {
            // $totalItem = 'Rp ' . number_format(($item->price * $item->quantity) - $item->discount_amount, 0, ',', '.');
            $sellerMessage .= "Produk : {$item->product->name_trans} \n";
            $sellerMessage .= "Jumlah Unit : $item->quantity \n";
            $sellerMessage .= "Harga : Rp " . number_format($item->price, 0, ',', '.') . " \n";
            $sellerMessage .= "Diskon : - Rp " . number_format($item->discount_amount, 0, ',', '.') . " \n";
            $sellerMessage .= "--------------------------\n";
            $sellerMessage .= "Harga Setelah Diskon : Rp " . number_format($item->price - $item->discount_amount, 0, ',', '.') . " \n\n";
        }

        $sellerMessage .= "\n💰 Rincian Pembayaran:\n";
        $sellerMessage .= "Total Harga : $priceBeforeDiscount \n";
        $sellerMessage .= "Total Diskon : - " . $totalDiscount . " \n";
        $sellerMessage .= "--------------------------\n";
        $sellerMessage .= "Harga Setelah Diskon : $priceAfterDiscount \n";
        $sellerMessage .= "--------------------------\n";
        $sellerMessage .= "Ongkir : $shippingCost \n";
        $sellerMessage .= "PPN : $tax \n";
        $sellerMessage .= "--------------------------\n";
        $sellerMessage .= "✅ Total Pembayaran : $total \n";
        $sellerMessage .= "==========================\n\n";
        $sellerMessage .= "\nTanggal Order : " . $order->created_at->format('d-m-Y H:i');
        $sellerMessage .= "\n\n📦Mohon segera siapkan produknya sesuai pesanan Customer.";

        // SendWhatsappToSeller::dispatch([
        //     '62811889588', //Pak Niko
        //     '628119112722', //Pak Mike
        //     '6285280106488', //Admin 1
        //     '6289603097173', //Admin 2
        //     '62811833164', //admin 3
        //     '62811858804', //admin 4
        //     '62811908990', //admin 5
        // ], $sellerMessage);

        $admins = Admin::all();
        $admins->each(function (Admin $admin) use ($order) {
            $admin->notify(new OrderPaidAdminNotification($order));
        });
    }

    public function invoicePaid(InvoicePaidEvent $event): void
    {
        $invoice = $event->invoice;
        $invoice->user->notify(new SplitPaymentConfirmationNotification($invoice));
        $invoice->order->user->notify(new SplitPaymentConfirmationOwnerNotification($invoice));
    }

    public function confirmed(OrderConfirmedEvent $event): void
    {
        $order = $event->order;

        if ($order->payment_method == OrderPaymentTypeEnum::SplitPayment) {
            foreach ($order->invoices() as $invoice) {
                $invoice->user->notify(new OrderConfirmedNotification($order));
            }
        } else {
            $order->user->notify(new OrderConfirmedNotification($order));
        }
    }

    public function rejected(OrderRejectedEvent $event): void
    {
        $order = $event->order;
        $reason = $event->reason;

        if ($order->payment_method == OrderPaymentTypeEnum::SplitPayment) {
            foreach ($order->invoices() as $invoice) {
                $invoice->user->notify(new OrderRejectedNotification($order, $reason));
            }
        } else {
            $order->user->notify(new OrderRejectedNotification($order, $reason));
        }
    }

    public function cancelOrder(OrderCanceledEvent $event): void
    {
        $order = $event->order;
        $type = $event->type;

        if ($type == 'admin') {
            if ($order->payment_method == OrderPaymentTypeEnum::SplitPayment) {
                foreach ($order->invoices() as $invoice) {
                    $invoice->user->notify(new CancelOrderByAdminNotification($order));
                }
            } else {
                $order->user->notify(new CancelOrderByAdminNotification($order));
            }
        }
    }

    public function readyToPickUp(ReadyToPickUpEvent $event): void
    {
        $order = $event->order;
        $order->user->notify(new ReadyToPickupNotification($order));

        $message = <<<EOT
                Hai {$order->user->name}

                Pesanan Anda dengan nomor #$order->order_number sudah siap diambil di gudang.

                Silahkan membawa bukti pembelian dengan menunjukan nomor pesanan saat mengambil pesanan Anda.
                EOT;

        if ($order->payment_method == OrderPaymentTypeEnum::SplitPayment) {
            foreach ($order->invoices() as $invoice) {
                WhatsApp::sendMessage($invoice->user->phone_number, $message);
            }
        } else {
            WhatsApp::sendMessage($order->user->phone_number, $message);
        }
    }

    public function alreadyPickedUp(AlreadyPickedUpEvent $event): void
    {
        $order = $event->order;
        $order->user->notify(new AlreadyPickedUpNotification($order));

        $url = 'https://bulky.id/redirect?type=order' . ($order->payment_method == OrderPaymentTypeEnum::SplitPayment ? '-split' : '') . '&order_id=' . $order->id;

        $message = <<<EOT
        Hai {$order->user->name}

        Terima kasih sudah mengambil pesanan Anda. Semoga Anda senang dengan produk yang telah Anda beli.
        Jangan lupa untuk memberikan ulasan tentang produk yang Anda beli ya. Ulasan Anda sangat berharga bagi kami untuk terus meningkatkan kualitas layanan.

        Kami tunggu kunjungan Anda kembali di Bulky.id. Ada banyak produk menarik lainnya yang menanti Anda!
        EOT;

        $messageReview = <<<EOT
        Hai {$order->user->name}

        Terima kasih sudah mengambil pesanan Anda. Semoga Anda senang dengan produk yang telah Anda beli.
        Jangan lupa untuk memberikan ulasan tentang produk yang Anda beli ya. Ulasan Anda sangat berharga bagi kami untuk terus meningkatkan kualitas layanan.

        bantu kasih ulasan di sini ya: $url

        Kami tunggu kunjungan Anda kembali di Bulky.id. Ada banyak produk menarik lainnya yang menanti Anda!
        EOT;

        if ($order->payment_method == OrderPaymentTypeEnum::SplitPayment) {
            $order->invoices->where('user_id', '!=', $order->user_id)
                ->each(fn($invoice) => WhatsApp::sendMessage($invoice->user->phone_number, $message));
        }
        WhatsApp::sendMessage($order->user->phone_number, $messageReview);
    }

    public function delivered(OrderDeliveredEvent $event): void
    {
        $order = $event->order;
        $url = $order->payment_method == OrderPaymentTypeEnum::SplitPayment
            ? 'https://bulky.id/redirect?type=order-split&order_id=' . $order->id
            : 'https://bulky.id/redirect?type=order&order_id=' . $order->id;

        $message = <<<EOT
                Halo {$order->user->name}!

                Pesananmu ($order->order_number) sudah sampai ya? Semoga suka dengan produknya!

                Kami mau minta tolong nih, kalau ada waktu, bantu kasih ulasan di sini ya: $url

                Pendapatmu sangat berarti buat kami. Terima kasih banyak!

                Salam,
                Tim Bulky.id
                EOT;

        \Mail::to($order->user)->send(new OrderDelivered($order, $url));
        WhatsApp::sendMessage($order->user->phone_number, $message);
    }

    public function deliveryInProgress(DeliveryInProgressEvent $event): void
    {
        $order = $event->order;
        $tracking_url = $event->tracking_url;
        $messageTemplate = <<<EOT
            Halo {name}!

            Pesananmu ({order_number}) sedang dalam proses pengiriman.
            Kamu bisa melihat status pengirimannya di sini: {tracking_url}

            Salam,
            Tim Bulky.id
            EOT;

        $recipients = $order->payment_method == OrderPaymentTypeEnum::SplitPayment
            ? $order->invoices->pluck('user')
            : collect([$order->user]);

        foreach ($recipients as $recipient) {
            $message = str_replace(
                ['{name}', '{order_number}', '{tracking_url}'],
                [$recipient->name, $order->order_number, $tracking_url],
                $messageTemplate
            );

            WhatsApp::sendMessage($recipient->phone_number, $message);
        }
    }

    public function subscribe(Dispatcher $event): void
    {
        $event->listen(
            OrderCreatedEvent::class,
            [self::class, 'orderCreated']
        );

        $event->listen(
            OrderPaidEvent::class,
            [self::class, 'orderPaid']
        );

        $event->listen(
            InvoicePaidEvent::class,
            [self::class, 'invoicePaid']
        );

        $event->listen(
            OrderCanceledEvent::class,
            [self::class, 'cancelOrder']
        );

        $event->listen(
            OrderConfirmedEvent::class,
            [self::class, 'confirmed']
        );

        $event->listen(
            OrderRejectedEvent::class,
            [self::class, 'rejected']
        );

        $event->listen(
            ReadyToPickUpEvent::class,
            [self::class, 'readyToPickUp']
        );

        $event->listen(
            AlreadyPickedUpEvent::class,
            [self::class, 'alreadyPickedUp']
        );

        $event->listen(
            OrderDeliveredEvent::class,
            [self::class, 'delivered']
        );

        $event->listen(
            DeliveryInProgressEvent::class,
            [self::class, 'deliveryInProgress']
        );
    }
}
