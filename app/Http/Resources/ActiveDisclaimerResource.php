<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ActiveDisclaimerResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        // determine locale preference
        $locale = $request->query('locale') ?? $request->header('Accept-Language') ?? config('app.locale');
        if (str_contains($locale, ',')) {
            $locale = explode(',', $locale)[0];
        }
        if (str_contains($locale, '-')) {
            $locale = explode('-', $locale)[0];
        }

        $title = null;
        $content = null;

        // Spatie HasTranslations stores translations in JSON columns 'title_trans' and 'content_trans'
        if (method_exists($this, 'getTranslation')) {
            try {
                $title = $this->getTranslation('title_trans', $locale);
            } catch (\Throwable $e) {
                $title = $this->title ?? null;
            }

            try {
                $content = $this->getTranslation('content_trans', $locale);
            } catch (\Throwable $e) {
                $content = $this->content ?? null;
            }
        } else {
            $title = $this->title ?? null;
            $content = $this->content ?? null;
        }

        return [
            'title' => $title,
            'slug' => $this->slug,
            'content' => $content,
            'translations' => [
                'title' => $this->title_trans ?? null,
                'content' => $this->content_trans ?? null,
            ],
            'is_active' => (bool) $this->is_active,
            'updated_at' => $this->updated_at?->toIsoString(),
        ];
    }
}
