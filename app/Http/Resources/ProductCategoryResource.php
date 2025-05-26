<?php

namespace App\Http\Resources;

use App\Models\ProductCategory;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin ProductCategory */
class ProductCategoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_trans' => $this->getTranslations('name_trans'),
            'slug' => $this->slug,
            'icon' => !is_null($this->icon) ? \Storage::disk('public')->url($this->icon) : null,
        ];
    }
}
