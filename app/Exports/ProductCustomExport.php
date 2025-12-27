<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProductCustomExport implements FromQuery, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function query()
    {
        $query = Product::query();
        if (isset($this->filters['is_active']) && $this->filters['is_active'] !== '') {
            $query->where('is_active', (int) $this->filters['is_active']);
        }
        if (isset($this->filters['sold_out']) && $this->filters['sold_out'] !== '') {
            $query->where('sold_out', (int) $this->filters['sold_out']);
        }
        $query->whereNull('deleted_at');
        return $query;
    }

    public function headings(): array
    {
        return [
            'Nama Produk',
            'Harga Sebelum Diskon',
            'Diskon (%)',
            'Harga Produk',
            'QTY',
            'Status Aktif',
            'Status Penjualan',
            'Kategori Produk',
            'Kondisi Produk',
            'Status Produk',
            'Nama Gudang',
            'Merek Produk',
            'File PDF',
            'Deskripsi Produk',
            'Tanggal Dibuat',
            'Tanggal Diperbarui',
        ];
    }

    public function map($product): array
    {
        // Nama Produk dari name_trans->id
        $namaProduk = $product->name_trans['id'] ?? $product->name;
        // Kategori Produk
        $kategori = $product->productCategory->name ?? '-';
        // Kondisi Produk
        $kondisi = $product->productCondition->title ?? '-';
        // Status Produk
        $statusProduk = $product->productStatus->status ?? '-';
        // Nama Gudang
        $gudang = $product->warehouse->name ?? '-';
        // Merek Produk (join brands)
        $merek = $product->brands->pluck('name')->implode(', ');
        // File PDF
        $pdf = $product->pdf_file ? 'https://back-office.bulky.id/storage/' . $product->pdf_file : '-';
        // Deskripsi Produk
        $desc = $product->description ?? ($product->description_trans['id'] ?? '-');
        // Bersihkan HTML dari deskripsi
        $desc = preg_replace('/<br\s*\/?>/i', "\n", $desc);
        $desc = preg_replace('/<li>/i', "- ", $desc);
        $desc = preg_replace('/<\/li>/i', "\n", $desc);
        $desc = preg_replace('/<\/?(ul|ol)>/i', "\n", $desc);
        $desc = preg_replace('/<[^>]*>/', '', $desc);
        $desc = preg_replace('/[\r\n]{3,}/', "\n\n", $desc);

        return [
            $namaProduk,
            $product->price_before_discount,
            ($product->price_before_discount && $product->price_before_discount > 0)
                ? number_format((($product->price_before_discount - $product->price) / $product->price_before_discount) * 100, 2) . '%'
                : '0%',
            $product->price,
            $product->total_quantity,
            $product->is_active ? 'Aktif' : 'Tidak Aktif',
            $product->sold_out ? 'Terjual' : 'Belum Terjual',
            $kategori,
            $kondisi,
            $statusProduk,
            $gudang,
            $merek,
            $pdf,
            trim($desc),
            $product->created_at,
            $product->updated_at,
        ];
    }
}
