<?php

namespace App\Http\Controllers\Api\Coupon;

use App\Http\Controllers\Controller;
use App\Http\Resources\CouponResource;
use App\Models\Coupon;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Request;

/**
 * @group Coupon
 *
 * Handles operations related to coupons in the application.
 */
class CouponController extends Controller
{
    /**
     * User Assigned Coupons
     *
     * Retrieves the coupons assigned to the authenticated user.
     *
     * @param Request $request The incoming HTTP request instance.
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection A collection of coupon resources.
     * @authenticated
     */
    public function getUserAssignedCoupons(Request $request)
    {
        $user = $request->user();
        $coupons = $user->coupons;
        return CouponResource::collection($coupons);
    }

    /**
     * Product Assigned Coupons
     */
    public function getProductAssignedCoupons($productId)
    {
        $product = Product::findOrFail($productId);
        $coupons = $product->coupons;
        return CouponResource::collection($coupons);
    }


    /**
     * Unassigned Coupons
     *
     * Retrieve Unassigned Coupons
     */
    public function getUnassignedCoupons()
    {
        $coupons = Coupon::whereDoesntHave('products')
            ->whereDoesntHave('users')
            ->get();

        return CouponResource::collection($coupons);
    }

    /**
     * Category Assigned Coupons
     */
    public function getCategoryAssignedCoupons($categoryId)
    {
        $category = ProductCategory::findOrFail($categoryId);
        $coupons = $category->coupons;
        return CouponResource::collection($coupons);
    }
}
