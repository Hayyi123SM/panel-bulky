<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\ReviewImage */
class ReviewImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => \Storage::disk('public')->url($this->path),
            'review_id' => $this->review_id,
        ];
    }
}
