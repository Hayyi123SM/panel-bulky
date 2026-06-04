<?php

namespace App\Http\Controllers\Api\General;

use App\Http\Controllers\Controller;
use App\Http\Resources\HomeHeroResource;
use App\Models\HomeHero;
use Illuminate\Http\JsonResponse;

/**
 * @group General
 *
 * Handles API requests for home hero content.
 */
class HomeHeroController extends Controller
{
    /**
     * Home Hero
     *
     * Returns the currently active home hero.
     */
    public function showActive(): JsonResponse
    {
        $hero = HomeHero::query()
            ->where('is_active', true)
            ->latest('updated_at')
            ->first();

        return response()->json([
            'data' => $hero ? HomeHeroResource::make($hero)->toArray(request()) : null,
            'meta' => [
                'total_active' => $hero ? 1 : 0,
            ],
        ]);
    }
}
