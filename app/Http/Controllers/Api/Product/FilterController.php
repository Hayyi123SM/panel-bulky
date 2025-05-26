<?php

namespace App\Http\Controllers\Api\Product;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductBrandResource;
use App\Http\Resources\ProductCategoryResource;
use App\Http\Resources\ProductConditionResource;
use App\Http\Resources\ProductStatusResource;
use App\Http\Resources\WarehouseResource;
use App\Models\ProductBrand;
use App\Models\ProductCategory;
use App\Models\ProductCondition;
use App\Models\ProductStatus;
use App\Models\Warehouse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * @group Products
 *
 * Retrieves all the categories from the database and returns them as a collection of ProductCategoryResource objects.
 *
 * @return AnonymousResourceCollection The collection of ProductCategoryResource objects.
 * @subgroup Filters
 */
class FilterController extends Controller
{
    /**
     * Categories
     *
     * Retrieves all the categories from the database and returns them as a collection of ProductCategoryResource objects.
     *
     * @return AnonymousResourceCollection The collection of ProductCategoryResource objects.
     */
    public function categories()
    {
        return $this->getCollection(ProductCategory::class, 'name', ProductCategoryResource::class);
    }

    /**
     * Brands
     *
     * Retrieves all the brands from the database and returns them as a collection of ProductBrandResource objects.
     *
     * @return AnonymousResourceCollection The collection of ProductBrandResource objects.
     */
    public function brands()
    {
        return $this->getCollection(ProductBrand::class, 'name', ProductBrandResource::class);
    }

    /**
     * Warehouses
     *
     * Retrieves all the warehouses from the database and returns them as a collection of WarehouseResource objects.
     *
     * @return AnonymousResourceCollection The collection of WarehouseResource objects.
     */
    public function warehouse()
    {
        return $this->getCollection(Warehouse::class, 'name', WarehouseResource::class);
    }

    /**
     * Conditions
     *
     * Retrieves all the conditions from the database and returns them as a collection of ProductConditionResource objects.
     *
     * @return AnonymousResourceCollection The collection of ProductConditionResource objects.
     */
    public function conditions()
    {
        return $this->getCollection(ProductCondition::class, 'title', ProductConditionResource::class);
    }

    /**
     * Statuses
     *
     * Retrieves all the statuses from the database and returns them as a collection of ProductStatusResource objects.
     *
     * @return AnonymousResourceCollection The collection of ProductStatusResource objects.
     */
    public function statuses()
    {
        return $this->getCollection(ProductStatus::class, 'status', ProductStatusResource::class);
    }

    /**
     * Helper function to retrieve and return a collection of specified model and resource.
     *
     * @param string $model The model class to retrieve data from.
     * @param string $orderBy The column to order the results by.
     * @param string $resource The resource class to transform the data.
     * @return AnonymousResourceCollection The collection of transformed data.
     */
    private function getCollection(string $model, string $orderBy, string $resource)
    {
        $items = $model::orderBy($orderBy)->get();
        return $resource::collection($items);
    }
}
