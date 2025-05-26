<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Product */
class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'images' => array_map(fn($image) => \Storage::disk('public')->url($image), $this->images),
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
            'pdf_file' => \Storage::disk('public')->url($this->pdf_file),
            'description' => $this->description,
            'description_trans' => $this->getTranslations('description_trans'),
            'brands' => ProductBrandResource::collection($this->whenLoaded('brands')),
            'sold_out' => $this->sold_out,

            'warehouse' => new WarehouseResource($this->whenLoaded('warehouse')),
            'category' => new ProductCategoryResource($this->whenLoaded('productCategory')),
            'condition' => new ProductConditionResource($this->whenLoaded('productCondition')),
            'status' => new ProductStatusResource($this->whenLoaded('productStatus')),
            'rating_avg' => round($this->reviews->avg('rating'), 2),
            'reviews' => ReviewResource::collection($this->reviews()->where('approved', true)->get()),
        ];
    }
}
