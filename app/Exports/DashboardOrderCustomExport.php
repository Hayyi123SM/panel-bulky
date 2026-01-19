<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

class DashboardOrderCustomExport implements FromQuery, WithHeadings, WithMapping, WithColumnFormatting
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
        $palets = $order->items
            ->map(
                fn($item) =>
                $item->product?->getTranslation('name_trans', 'id')
                    ?? $item->product?->name
                    ?? '-' // Fallback "-" karena data produk mungkin sudah dihapus
            )
            ->implode(', ');

        // Kategori
        $kategori = $order->items
            ->map(
                fn($i) =>
                $i->product?->productCategory?->getTranslation('name_trans', 'id')
                    ?? $i->product?->productCategory?->name
                    ?? '-'
            )
            ->implode(', ');

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
            $harga,
            $totalHarga,
            $ongkir,
            $diskon,
            $tanggal,
            $jenisPembayaran,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'E' => '#,##0', // Total Harga
            'F' => '#,##0', // Ongkir
            'G' => '#,##0', // Diskon
        ];
    }
}
