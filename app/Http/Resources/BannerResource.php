<?php

namespace App\Http\Resources;

use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Banner */
class BannerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'page' => $this->page,
            'product_type' => $this->product_type,
            'path' => $this->path,
            'full_url' => asset('storage/' . ($this->path ?? 'banners/default-banner.png')),
            'order' => $this->order,
            'is_active' => $this->is_active,
        ];
    }
}
