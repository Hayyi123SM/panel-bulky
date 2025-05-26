<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Video */
class VideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'thumbnail' => \Storage::disk('public')->url($this->thumbnail),
            'description' => $this->description,
            'view_count' => $this->view_count,
            'path' => \Storage::disk('public')->url($this->path),
            'type' => $this->type,

            'user' => new UserResource($this->whenLoaded('user')),
        ];
    }
}
