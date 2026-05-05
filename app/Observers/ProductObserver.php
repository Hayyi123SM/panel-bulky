<?php

namespace App\Observers;

use App\Models\Product;
use Illuminate\Support\Facades\Storage;

class ProductObserver
{
    public function creating(Product $product): void
    {
        $product->name = $product->name_trans;
        $product->slug = $product->createUniqueSlug($product->name);
    }

    public function updating(Product $product): void
    {
        // Jika name_trans diubah, sinkronkan field name dan perbarui slug
        if ($product->isDirty('name_trans')) {
            $product->name = $product->name_trans;
            // gunakan excludeId untuk menghindari cek terhadap dirinya sendiri
            $product->slug = $product->createUniqueSlug($product->name, $product->id ?? null);
        }
    }

    public function forceDeleted(Product $product): void
    {
        // Hapus semua gambar
        Storage::disk('public')->delete($product->images);
        // Hapus file PDF jika ada
        if (!empty($product->pdf_file)) {
            Storage::disk('public')->delete($product->pdf_file);
        }
    }
}
