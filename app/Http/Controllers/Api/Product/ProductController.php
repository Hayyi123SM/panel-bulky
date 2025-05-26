<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\ProductsRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use LaravelIdea\Helper\App\Models\_IH_Product_QB;

/**
 * @group Products
 *
 * Retrieves a list of products based on the provided search criteria.
 *
 * @param ProductsRequest $request The request object containing search criteria.
 * @return AnonymousResourceCollection The JSON resource containing the paginated list of products.
 */
class ProductController extends Controller
{
    /**
     * List Product
     *
     * Retrieves a list of products based on the provided search criteria.
     *
     * @param ProductsRequest $request The request object containing search criteria.
     * @return AnonymousResourceCollection The JSON resource containing the paginated list of products.
     */
    public function getProducts(ProductsRequest $request): AnonymousResourceCollection
    {
        $query = Product::query()
            ->when($request->filled('search'), fn($q) => $q->where(function ($query) use ($request) {
                $search = '%' . strtolower($request->search) . '%';
                $query->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name_trans, '$.id'))) LIKE ?", [$search])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name_trans, '$.en'))) LIKE ?", [$search]);
            }))
            ->when($request->category, fn($q) => $q->whereHas('productCategory', function($q) use ($request) {
                return $q->whereIn('slug', explode(',', $request->category))->withTrashed();
            }))
            ->when($request->status, function($q) use ($request) {
                return $q->whereIn('product_status_id', explode(',', $request->status))->withTrashed();
            })
            ->when($request->condition, fn($q) => $q->whereHas('productCondition', function($q) use ($request) {
                return $q->whereIn('slug', explode(',', $request->condition))->withTrashed();
            }))
            ->when($request->brands, fn($q) => $q->whereHas('brands', fn($q) => $q->whereIn('slug', $request->brands)))
            ->when($request->price_min, fn($q) => $q->where('price', '>=', $request->price_min))
            ->when($request->price_max, fn($q) => $q->where('price', '<=', $request->price_max))
            ->whereIsActive(true)
            ->when($request->random, fn($q) => $q->inRandomOrder())
            ->with('warehouse');

        $products = $query->paginate($request->get('per_page', 15));

        return ProductResource::collection($products);
    }

    /**
     * Detail Product
     *
     * Retrieves the details of a specific product.
     *
     * @param string $slug The slug of the product.
     * @return ProductResource The JSON resource containing the details of the product.
     */
    public function detail(string $slug): ProductResource
    {
        $product = Product::whereSlug($slug)
            ->whereIsActive(true)
            ->with(['productCategory' => fn($q) => $q->withTrashed(), 'productCondition' => fn($q) => $q->withTrashed(), 'brands' => fn($q) => $q->withTrashed(), 'productStatus', 'warehouse'])
            ->firstOrFail();

        return new ProductResource($product);
    }

    /**
     * Related Products
     *
     * Retrieves a list of related products based on the provided product slug.
     *
     * @param string $slug The slug of the product.
     * @return AnonymousResourceCollection The JSON resource containing the list of related products.
     */
    public function relatedProduct(string $slug): AnonymousResourceCollection
    {
        $product = Product::whereSlug($slug)
            ->whereIsActive(true)
            ->firstOrFail();

        $relatedProducts = Product::where('id', '!=', $product->id)
            ->whereIsActive(true)
            ->where(function ($query) use ($product) {
                $query->where('product_category_id', $product->product_category_id)
                    ->orWhereHas('brands', fn($q) => $q->whereIn('id', $product->brands->pluck('id')));
            })
            ->with(['warehouse'])
            ->inRandomOrder()
            ->limit(5)
            ->get();

        $additionalProducts = $this->getAdditionalRelatedProducts($relatedProducts, $product);

        return ProductResource::collection($relatedProducts->merge($additionalProducts));
    }

    /**
     * Retrieves additional related products if the initial related products are less than 5.
     *
     * @param \Illuminate\Database\Eloquent\Collection $relatedProducts The initial related products.
     * @param Product $product The current product.
     * @return Collection
     */
    private function getAdditionalRelatedProducts(\Illuminate\Database\Eloquent\Collection $relatedProducts, Product $product)
    {
        $count = $relatedProducts->count();

        if ($count >= 5) return collect([]);

        return Product::where('id', '!=', $product->id)
            ->whereIsActive(true)
            ->whereNotIn('id', $relatedProducts->pluck('id'))
            ->inRandomOrder()
            ->limit(5 - $count)
            ->get();
    }
}
