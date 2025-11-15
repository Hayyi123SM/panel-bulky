<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Product\ProductsRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
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
            ->when($request->category, fn($q) => $q->whereHas('productCategory', function ($q) use ($request) {
                return $q->whereIn('slug', explode(',', $request->category))->withTrashed();
            }))
            ->when($request->status, function ($q) use ($request) {
                return $q->whereIn('product_status_id', explode(',', $request->status))->withTrashed();
            })
            ->when($request->condition, fn($q) => $q->whereHas('productCondition', function ($q) use ($request) {
                return $q->whereIn('slug', explode(',', $request->condition))->withTrashed();
            }))
            ->when($request->brands, fn($q) => $q->whereHas('brands', fn($q) => $q->whereIn('slug', $request->brands)))
            ->when($request->price_min, fn($q) => $q->where('price', '>=', $request->price_min))
            ->when($request->price_max, fn($q) => $q->where('price', '<=', $request->price_max))
            ->whereIsActive(true)
            ->when($request->random, fn($q) => $q->inRandomOrder())
            ->when($request->packaging_type, fn($q) => $q->where('packaging_type', $request->packaging_type))
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
            ->with(['productCategory' => fn($q) => $q->withTrashed(), 'productCondition' => fn($q) => $q->withTrashed(), 'brands' => fn($q) => $q->withTrashed(), 'productStatus', 'statusPackage', 'warehouse'])
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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'wms_id' => 'nullable|numeric|gt:0',
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'price_before_discount' => 'required|numeric|min:0',
            'total_quantity' => 'required|integer|min:0',
            'pdf_file' => 'nullable|url',
            'description' => 'required|string',
            'is_active' => 'nullable|boolean',
            'warehouse_id' => 'nullable|exists:warehouses,id',
            'product_category_id' => 'nullable|exists:product_categories,id',
            'brand_ids' => 'nullable|array',
            'brand_ids.*' => 'exists:product_brands,id',
            'product_condition_id' => 'nullable|exists:product_conditions,id',
            'product_status_id' => 'nullable|exists:product_statuses,id',
            'images' => 'nullable|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'is_sold' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $images = [];
        $fileImages = $request->file('images');

        if (is_array($fileImages)) {
            foreach ($fileImages as $image) {
                $images[] = $image->storeAs('products', $image->hashName(), 'public');
            }
        }

        $product = activity()->withoutLogs(function () use ($request, $images) {
            return Product::create([
                'images' => $images,
                'wms_id' => $request->wms_id ?? null,
                'name_trans' => $request->name,
                'price' => $request->price,
                'price_before_discount' => $request->price_before_discount,
                'total_quantity' => $request->total_quantity,
                'pdf_file' => $request->pdf_file ?? null,
                'description_trans' => $request->description,
                'is_active' => $request->is_active ?? true,
                'warehouse_id' => $request->warehouse_id ?? null,
                'product_category_id' => $request->product_category_id,
                'product_condition_id' => $request->product_condition_id,
                'product_status_id' => $request->product_status_id,
                'is_new' => true,
                'sold_out' => $request->is_sold ?? false,
            ]);
        });

        if ($request->brand_ids !== null) {
            $product->brands()->sync($request->brand_ids);
        }

        return new ProductResource($product);
    }

    public function storeBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'products' => 'required|array|min:1',
            'products.*.name' => 'required|string|max:255',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.price_before_discount' => 'required|numeric|min:0',
            'products.*.total_quantity' => 'required|integer|min:0',
            'products.*.description' => 'required|string',
            'products.*.images' => 'nullable|array',
            'products.*.images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
            'products.*.warehouse_id' => 'nullable|exists:warehouses,id',
            'products.*.product_category_id' => 'nullable|exists:product_categories,id',
            'products.*.product_condition_id' => 'nullable|exists:product_conditions,id',
            'products.*.product_status_id' => 'nullable|exists:product_statuses,id',
            'products.*.brand_ids' => 'nullable|array',
            'products.*.brand_ids.*' => 'exists:product_brands,id',
            'products.*.is_active' => 'nullable|boolean',
            'products.*.is_new' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        DB::beginTransaction();

        try {
            activity()->withoutLogs(function () use ($request) {
                foreach ($request->products as $index => $item) {
                    $storedImages = [];

                    if (isset($item['images']) && is_array($item['images'])) {
                        foreach ($item['images'] as $image) {
                            $storedImages[] = $image->storeAs('products', $image->hashName(), 'public');
                        }
                    }

                    $product = Product::create([
                        'name_trans' => $item['name'],
                        'price' => $item['price'],
                        'price_before_discount' => $item['price_before_discount'],
                        'total_quantity' => $item['total_quantity'],
                        'description_trans' => $item['description'],
                        'images' => $storedImages,
                        'is_active' => $item['is_active'] ?? true,
                        'product_category_id' => $item['product_category_id'] ?? null,
                        'product_condition_id' => $item['product_condition_id'] ?? null,
                        'product_status_id' => $item['product_status_id'] ?? null,
                        'is_new' => true,
                        'sold_out' => $item['is_sold'] ?? false,
                    ]);

                    if (!empty($item['brand_ids'])) {
                        $product->brands()->sync($item['brand_ids']);
                    }
                }
            });

            DB::commit();
            return response()->json(['message' => 'Batch produk berhasil disimpan.'], 201);
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error($e->getMessage());
            return response()->json([
                'error' => 'Gagal simpan produk',
            ], 500);
        }
    }
}
