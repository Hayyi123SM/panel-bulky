<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;

class DashboardOrderCustomExport implements FromQuery, WithHeadings, WithMapping
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Order::query()
            ->with(['user', 'items.product', 'items.product.productCategory', 'shipping', 'invoices.paymentMethod'])
            ->where('payment_status', 'paid');
        // ->whereIn('order_status', ['Shipped', 'Delivered']);

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('order_date', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('order_date', '<=', $this->filters['date_to']);
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nama Pembeli',
            'Palet',
            'Kategori',
            'Harga',
            'Total Harga',
            'Ongkos Kirim',
            'Diskon',
            'Tanggal Pesanan',
            'Jenis Pembayaran',
        ];
    }

    public function map($order): array
    {
        // Nama Pembeli
        $buyer = $order->user->name ?? '-';

        // Palet (multiple mapping)
        $palets = $order->items->pluck('product.name_trans')->map(fn($p) => $p['id'] ?? '')->implode(', ');

        // Kategori
        $kategori = $order->items->pluck('product.productCategory.name_trans')->map(fn($p) => $p['id'] ?? '')->implode(', ');

        // Harga item masing-masing
        $harga = $order->items->pluck('price')->implode(', ');

        // Total harga keseluruhan (items)
        $totalHarga = $order->items->sum('price');

        // Ongkir
        $ongkir = $order->shipping->shipping_cost ?? 0;

        // Diskon
        $diskon = $order->discount_amount ?? 0;

        // Tanggal Pesanan
        $tanggal = $order->order_date?->format('Y-m-d H:i:s') ?? '-';

        $invoice = $order->invoices
            ->where('user_id', $order->user_id)
            ->first();

        // Jenis Pembayaran
        $jenisPembayaran = $invoice?->paymentMethod->name ?? '-';

        return [
            $buyer,
            $palets,
            $kategori,
            number_format($harga, 0, ',', '.'),
            number_format($totalHarga, 0, ',', '.'),
            number_format($ongkir, 0, ',', '.'),
            number_format($diskon, 0, ',', '.'),
            $tanggal,
            $jenisPembayaran,
        ];
    }
}
