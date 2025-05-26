<?php

use App\Enums\OrderPaymentTypeEnum;
use App\Models\Order;
use App\Services\WhatsApp\WhatsApp;
use Filament\Actions\Exports\Http\Controllers\DownloadExport;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;

//Route::get('/test/a', function () {
//    $order = Order::find('9e5b85a0-4b99-41a6-a816-2624447e2ba9');
//    $tracking_url = 'https://test.com/abcdefg';
//    $messageTemplate = <<<EOT
//            Halo {name}!
//
//            Pesananmu ({order_number}) sedang dalam proses pengiriman.
//            Kamu bisa melihat status pengirimannya di sini: {tracking_url}
//
//            Salam,
//            Tim Bulky.id
//            EOT;
//
//    $recipients = $order->payment_method == OrderPaymentTypeEnum::SplitPayment
//        ? $order->invoices->pluck('user')
//        : collect([$order->user]);
//
//    $messages = [];
//    foreach ($recipients as $recipient) {
//        $messages[] = str_replace(
//            ['{name}', '{order_number}', '{tracking_url}'],
//            [$recipient->name, $order->order_number, $tracking_url],
//            $messageTemplate
//        );
//    }
//
//    dd($messages);
//});


Route::get('/filament/exports/{export}/download', DownloadExport::class)
    ->name('filament.exports.download')
    ->middleware(['web', 'auth:admin']);
