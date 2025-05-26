<?php

namespace App\Http\Controllers\Api\Banner;

use App\Http\Controllers\Controller;
use App\Http\Resources\BannerResource;
use App\Models\Banner;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

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
     * @return AnonymousResourceCollection
     */
    public function index(): AnonymousResourceCollection
    {
        $banners = Banner::whereIsActive(true)->get();
        return BannerResource::collection($banners);
    }
}
