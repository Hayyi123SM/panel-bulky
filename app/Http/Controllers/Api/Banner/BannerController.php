<?php

namespace App\Http\Controllers\Api\Banner;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\JsonResponse;

/**
 * @group Banner
 *
 * Handles operations related to banners, including retrieving and managing active banners.
 */
class BannerController extends Controller
{
    /**
     * Get Banner
     *
     * Retrieves all active banners and returns them as a collection of BannerResource instances
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        // Get all active banners
        $banners = Banner::whereIsActive(true)->get();

        // Group by page. For 'product' page, additionally group by product_type (null -> 'default')
        $grouped = $banners->groupBy('page')->mapWithKeys(function ($items, $page) {
            if (strtolower($page) === 'product') {
                $byType = $items->groupBy(function ($b) {
                    return $b->product_type ?? 'default';
                })->mapWithKeys(function ($subItems, $type) {
                    // Use BannerResource to serialize each group
                    return [$type => BannerResource::collection($subItems)->toArray(request())];
                });

                return [$page => $byType->toArray()];
            }

            // Non-product pages: return flat array of banners
            return [$page => BannerResource::collection($items)->toArray(request())];
        })->toArray();

        // Return response object with grouped data and meta
        return response()->json([
            'data' => $grouped,
            'meta' => [
                'total' => $banners->count(),
            ],
        ]);
    }

    /**
     * Return active banners for home page as flat array
     *
     * @return JsonResponse
     */
    public function home(): JsonResponse
    {
        $banners = Banner::whereIsActive(true)->where('page', 'home')->orderBy('order')->get();
        return response()->json([
            'data' => BannerResource::collection($banners)->toArray(request()),
            'meta' => ['total' => $banners->count()],
        ]);
    }

    /**
     * Return active banners for product page grouped by product_type
     *
     * @return JsonResponse
     */
    public function product(): JsonResponse
    {
        $banners = Banner::whereIsActive(true)
            ->where('page', 'product')
            ->orderBy('order')
            ->get();

        // Group by product_type (null => 'default') and pick only the first/priority banner per type.
        $grouped = $banners->groupBy(function ($b) {
            return $b->product_type ?? 'default';
        })->mapWithKeys(function ($items, $type) {
            $first = $items->sortBy('order')->first();
            return [$type => $first ? BannerResource::make($first)->toArray(request()) : null];
        })->toArray();

        return response()->json([
            'data' => $grouped,
            'meta' => ['total' => $banners->count()],
        ]);
    }
}
