<?php


namespace App\Http\Resources;

use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'images' => !empty($this->images)
                ? array_map(fn($image) => Storage::disk('public')->url($image), $this->images)
                : [Storage::disk('public')->url('images/product-default.png')],
            'name' => $this->name,
            'name_trans' => $this->getTranslations('name_trans'),
            'slug' => $this->slug,
            'id_pallet' => $this->id_pallet,
            'show_price_before_discount' => $this->price_before_discount > 0,
            'price_before_discount' => [
                'numeric' => $this->price_before_discount,
                'formatted' => 'Rp ' . number_format($this->price_before_discount, 0, ',', '.'),
            ],
            'price' => [
                'numeric' => $this->price,
                'formatted' => 'Rp ' . number_format($this->price, 0, ',', '.'),
            ],
            'total_quantity' => $this->total_quantity,
            'packaging_type' => $this->packaging_type,
            'pdf_file' => Storage::disk('public')->url($this->pdf_file),
            'description' => $this->description,
            'description_trans' => $this->getTranslations('description_trans'),
            'brands' => ProductBrandResource::collection($this->whenLoaded('brands')),
            'sold_out' => $this->sold_out,
            'is_active' => $this->is_active,
            'is_new' => $this->is_new,
            'tag_ribbon' => $this->tag_ribbon,

            // Product availability for FE (consistency with CartItemResource)
            'is_available' => $this->is_active && !$this->sold_out,
            'can_add_to_cart' => $this->is_active && !$this->sold_out,
            'availability_status' => match (true) {
                !$this->is_active => 'inactive',
                $this->sold_out => 'sold_out',
                default => 'available'
            },
            'status_message' => match (true) {
                !$this->is_active => 'Produk tidak tersedia',
                $this->sold_out => 'Produk sudah terjual',
                default => null
            },

            'note_discrepancy' => $this->note_discrepancy ?? 1,

            'warehouse' => $this->warehouse // jika tidak ada warehouse, ambil warehouse pertama sebagai default
                ? new WarehouseResource($this->warehouse)
                : new WarehouseResource(Warehouse::query()->first()),
            'category' => new ProductCategoryResource($this->whenLoaded('productCategory')),
            'condition' => new ProductConditionResource($this->whenLoaded('productCondition')),
            'status' => new ProductStatusResource($this->whenLoaded('productStatus')),
            'status_package' => new StatusPackageResource($this->whenLoaded('statusPackage')),
            'rating_avg' => round($this->reviews->avg('rating'), 2),
            'reviews' => ReviewResource::collection($this->reviews()->where('approved', true)->get()),
        ];
    }
}
