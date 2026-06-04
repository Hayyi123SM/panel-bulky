<?php

namespace App\Http\Resources;

use App\Models\HomeHero;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin HomeHero */
class HomeHeroResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'subtitle' => $this->subtitle,
            'image_path' => $this->image_path,
            'full_url' => asset('storage/' . $this->image_path),
            'is_active' => $this->is_active,
            'updated_at' => $this->updated_at,
        ];
    }
}
