<?php

namespace App\Http\Controllers\Api\Cart;

use App\Enums\CouponDiscountTypeEnum;
use App\Enums\InvoiceStatusEnum;
use App\Enums\OrderPaymentTypeEnum;
use App\Enums\ShippingMethodEnum;
use App\Events\Order\OrderCreatedEvent;
use App\Helpers\PhoneHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Cart\AddToCartRequest;
use App\Http\Requests\Api\Cart\PlaceOrderRequest;
use App\Http\Requests\Api\Cart\SearchFiendRequest;
use App\Http\Requests\Api\Cart\SetAddressRequest;
use App\Http\Requests\Api\Cart\SetSelectedItemRequest;
use App\Http\Requests\Api\Cart\SetShippingMethodRequest;
use App\Http\Resources\CartResource;
use App\Http\Resources\OrderResource;
use App\Http\Resources\UserResource;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use App\Notifications\RegisteredNotification;
use App\Services\Deliveree\Deliveree;
use App\Services\Forwarder\ApiRequest;
use App\Services\GeoRegion\GeoRegionService;
use App\Settings\PickupInfoSetting;
use App\Settings\PpnSettings;
use Illuminate\Database\Eloquent\ModelNotFoundException;
// Removed duplicate use statement for Illuminate\Support\Facades\DB
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * @group Cart
 *
 * Sets the shipping method for the user's cart.
 *
 * This method retrieves the user's cart based on their user ID, updates its shipping method according to the input
 * from the request, and resets the discount information. After performing these updates, the cart's total price
 * is recalculated using the `updateTotalPrice()` method. Finally, a CartResource is returned with the updated cart data.
 *
 * @param SetShippingMethodRequest $request The request object containing the shipping method details.
 * @return CartResource The resource representation of the updated cart.
 * @authenticated
 */
class CartController extends Controller
{
    /**
     * Add to Cart
     *
     * Adds a product to the cart.
     *
     * @param AddToCartRequest $request The request object containing the product ID.
     * @return CartResource The resource representation of the updated cart.
     */
    public function add(AddToCartRequest $request)
    {
        $product = Product::find($request->product_id);
        $user_id = $request->user()->id;
        $cart = Cart::withCount('items')->firstOrCreate(
            ['user_id' => $user_id],
            [
                'total_price' => 0,
                'payment_method' => OrderPaymentTypeEnum::SinglePayment,
            ]
        );

        $packagingType = $product->packaging_type;

        try {
            DB::transaction(function () use ($cart, $product, $request, $packagingType) {
                $cartItem = $cart->items()->firstOrCreate(
                    ['product_id' => $request->input('product_id')],
                    [
                        'quantity' => 1,
                        'price' => $product->price,
                    ]
                );

                // Produk baru langsung di-checklist
                $cartItem->is_selected = true;
                $cartItem->save();

                // Uncheck semua produk lama dengan type berbeda
                $cart->items()
                    ->where('is_selected', true)
                    ->where('id', '!=', $cartItem->id)
                    ->whereHas('product', fn($q) => $q->where('packaging_type', '!=', $packagingType))
                    ->get()
                    ->each(function ($item) {
                        $item->is_selected = false;
                        $item->save();
                    });

                // Logic khusus container/truck_load: hanya satu yang bisa terceklis
                if (in_array($packagingType, ['container', 'truck_load'])) {
                    $cart->items()
                        ->where('is_selected', true)
                        ->where('id', '!=', $cartItem->id)
                        ->whereHas('product', fn($q) => $q->where('packaging_type', $packagingType))
                        ->get()
                        ->each(function ($item) {
                            $item->is_selected = false;
                            $item->save();
                        });
                }
            });
        } catch (\Exception $e) {
            Log::error('Error adding product to cart: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Gagal menambah produk ke cart.',
            ], 500);
        }

        $this->updateTotalPrice($cart);

        return new CartResource($cart);
    }

    /**
     * Remove Cart Item
     *
     * Removes a product from the cart.
     *
     * @param Product $product The product to be removed from the cart.
     * @return CartResource The resource representation of the updated cart.
     */
    public function removeItem(Product $product)
    {
        $user_id = request()->user()->id;
        $cart = Cart::whereUserId($user_id)->withCount('items')->firstOrFail();

        $cart->items()->where('product_id', $product->id)->delete();

        $this->updateTotalPrice($cart);

        return new CartResource($cart);
    }

    /**
     * Get Cart
     *
     * Retrieves the cart for the authenticated user.
     * If cart doesn't exist, returns empty cart info without persisting to database.
     *
     * @return CartResource The resource representation of the user's cart.
     */
    public function getCart(Request $request)
    {
        $user_id = $request->user()->id;

        // Get existing cart or return empty cart object (don't persist empty cart)
        $cart = Cart::where('user_id', $user_id)
            ->with(['items' => function ($query) {
                $query->with(['product' => function ($pq) {
                    $pq->withTrashed();
                }])->withTrashed();
            }])
            ->withCount('items')
            ->first();

        // If cart doesn't exist, return empty cart without creating database record
        if (!$cart) {
            $emptyCart = new Cart([
                'user_id' => $user_id,
                'total_price' => 0,
                'payment_method' => OrderPaymentTypeEnum::SinglePayment,
            ]);
            $emptyCart->items_count = 0;
            return new CartResource($emptyCart);
        }

        // Clean up soft-deleted products from cart and flag unavailable items
        $hasDeletedItems = false;
        foreach ($cart->items as $item) {
            // Remove soft-deleted products
            if ($item->product?->trashed()) {
                $item->delete();
                $hasDeletedItems = true;
                continue;
            }

            // Flag items with inactive or sold out products (don't remove, let FE handle display)
            if ($item->product) {
                $item->is_available = $item->product->is_active && !$item->product->sold_out;
                $item->availability_status = match (true) {
                    !$item->product->is_active => 'inactive',
                    $item->product->sold_out => 'sold_out',
                    default => 'available'
                };
                $item->status_message = match ($item->availability_status) {
                    'inactive' => 'Produk tidak tersedia',
                    'sold_out' => 'Produk sudah terjual',
                    default => null
                };
            }
        }

        // Refresh cart items if any were deleted
        if ($hasDeletedItems) {
            $cart->load('items.product');
            $cart->loadCount('items');
        }

        // Reset discounts when entering checkout mode
        if ($request->filled('mode') && $request->input('mode') == 'checkout') {
            $cart->update([
                'coupon_code' => null,
                'discount_amount' => 0,
            ]);
            $cart->items()->update(['discount_amount' => 0]);

            // Refresh items after discount reset
            $cart->refresh();
            // $cart->load('items.product');
        }

        // Reload to get fresh data after updateTotalPrice
        $cart->load('items.product');

        // Recalculate cart total price
        $this->updateTotalPrice($cart);

        return new CartResource($cart);
    }

    /**
     * Set Selected Item
     *
     * Updates the selected status of items in the user's cart.
     *
     * This method takes a SetSelectedItemRequest object as input and updates the selected status
     * of the items in the user's cart based on the request data. The user ID is obtained from the request
     * and used to retrieve the cart from the database. The `cart_items` data from the request is then looped
     * through and each item is updated in the database to reflect the selected status.
     *
     * After updating the items, the total price of the cart is updated using the `updateTotalPrice()` method.
     * Finally, a CartResource object is returned containing the updated cart.
     *
     * @param SetSelectedItemRequest $request The request object containing the user's selection data.
     * @return CartResource The resource representing the updated cart.
     */
    public function setSelectedItem(SetSelectedItemRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        $cart = Cart::whereUserId($user->id)->with('items.product')->firstOrFail();

        // Bulk select by packaging_type
        if (isset($data['select_all_type'])) {
            $type = $data['select_all_type'];
            if ($type === 'palet') {
                // Ambil semua item palet yang available
                $paletItems = $cart->items->filter(function ($item) {
                    return $item->product
                        && $item->product->packaging_type === 'palet'
                        && $item->product->is_active
                        && !$item->product->sold_out;
                });

                // Check if ada palet items yang unavailable
                $unavailablePaletCount = $cart->items->filter(function ($item) {
                    return $item->product
                        && $item->product->packaging_type === 'palet'
                        && (!$item->product->is_active || $item->product->sold_out);
                })->count();

                if ($paletItems->isEmpty()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tidak ada produk palet yang tersedia untuk dipilih.'
                    ], 422);
                }

                $allSelected = $paletItems->every(function ($item) {
                    return $item->is_selected;
                });

                // Select all available palet, unselect all container & truck_load
                foreach ($cart->items as $item) {
                    if ($item->product && $item->product->packaging_type === 'palet') {
                        // Only select if product is available
                        if ($item->product->is_active && !$item->product->sold_out) {
                            $item->is_selected = !$allSelected;
                        } else {
                            $item->is_selected = false;
                        }
                    } else {
                        $item->is_selected = false;
                    }
                    $item->save();
                }

                // Notify if some items were skipped
                if ($unavailablePaletCount > 0 && !$allSelected) {
                    return response()->json([
                        'success' => true,
                        'message' => "{$unavailablePaletCount} produk tidak tersedia atau sudah terjual, dilewati dari pemilihan.",
                        'cart' => new CartResource($cart->fresh()->load('items.product'))
                    ]);
                }
            } elseif (in_array($type, ['container', 'truck_load'])) {
                // Select all for container/truck_load is not allowed
                return response()->json([
                    'success' => false,
                    'message' => 'Tidak bisa checklist semua produk dengan type ' . str_replace('_', ' ', $type) . '. Hanya satu item ' . str_replace('_', ' ', $type) . ' yang bisa dipilih.'
                ], 422);
            }
        } elseif (isset($data['cart_item_id']) && isset($data['is_selected'])) {
            // Single item select/unselect
            $cartItem = CartItem::where('id', $data['cart_item_id'])
                ->whereHas('cart', fn($q) => $q->where('user_id', $user->id))
                ->with('product')
                ->firstOrFail();

            // Validate product availability before selecting
            if ($data['is_selected'] && $cartItem->product) {
                if (!$cartItem->product->is_active) {
                    throw ValidationException::withMessages([
                        'cart_item' => "Produk '{$cartItem->product->name}' sudah tidak tersedia. Silakan hapus dari keranjang."
                    ]);
                }

                if ($cartItem->product->sold_out) {
                    throw ValidationException::withMessages([
                        'cart_item' => "Produk '{$cartItem->product->name}' sudah terjual. Silakan hapus dari keranjang."
                    ]);
                }
            }

            $packagingType = $cartItem->product?->packaging_type;
            if (in_array($packagingType, ['container', 'truck_load']) && $data['is_selected']) {
                // Unselect all palet
                foreach ($cart->items as $item) {
                    if ($item->product && $item->product->packaging_type === 'palet') {
                        $item->is_selected = false;
                        $item->save();
                    }
                }
                // Unselect all other container/truck_load items (both types) except the one being selected
                foreach ($cart->items as $item) {
                    if ($item->product && in_array($item->product->packaging_type, ['container', 'truck_load']) && $item->id != $cartItem->id) {
                        $item->is_selected = false;
                        $item->save();
                    }
                }
                // Select only the chosen container/truck_load item
                $cartItem->is_selected = true;
                $cartItem->save();
            } elseif ($packagingType === 'palet') {
                // Unselect all container & truck_load
                foreach ($cart->items as $item) {
                    if ($item->product && in_array($item->product->packaging_type, ['container', 'truck_load'])) {
                        $item->is_selected = false;
                        $item->save();
                    }
                }
                // Update palet item
                $cartItem->is_selected = $data['is_selected'];
                $cartItem->save();
            } else {
                // Fallback for unknown type
                $cartItem->is_selected = $data['is_selected'];
                $cartItem->save();
            }
        }

        // Reload items to ensure response is up-to-date
        $cart->load('items.product');

        // Update total price after selection change
        $this->updateTotalPrice($cart);

        return new CartResource($cart);
    }

    /**
     * Search Friend
     *
     * Searches for friends based on a search query.
     *
     * This method takes a SearchFriendRequest object as input and searches for friends in the database
     * whose username matches the search query provided in the request. The search query is obtained from the
     * request input and passed to the `where` method to perform a partial match search on the `username` column.
     * The maximum number of results is limited to 20 using the `take` method. The matching friends are then retrieved
     * from the database using the `get` method and returned as a collection of UserResource objects.
     *
     * @param SearchFiendRequest $request The request object containing the search query.
     * @return AnonymousResourceCollection The resource representing the collection of matching friends.
     */
    public function searchFriend(SearchFiendRequest $request)
    {
        $userId = $request->user()->id;
        $friends = User::whereEmail($request->input('search'))
            ->whereNot('id', $userId)
            ->take(20)
            ->get();

        return UserResource::collection($friends);
    }

    /**
     * Set Shipping Method
     *
     * Sets the shipping method for the user's cart during checkout.
     *
     * @param SetShippingMethodRequest $request The request instance containing the shipping method and user details
     * @return CartResource A resource representing the updated cart
     */
    public function setShippingMethod(SetShippingMethodRequest $request)
    {
        request()->request->add(['mode' => 'checkout']);

        $user = $request->user();
        $cart = Cart::whereUserId($user->id)->withCount('items')->firstOrFail();
        $cart->update([
            'shipping_method' => $request->input('method'),
            'discount_amount' => 0,
            'coupon_discount' => null,
        ]);

        $this->updateTotalPrice($cart);

        return new CartResource($cart);
    }

    /**
     * Set Address
     *
     * Sets the address for the user's cart during checkout.
     *
     * @param SetAddressRequest $request The incoming request containing the address ID
     * @return CartResource Returns the updated cart resource with the set address
     */
    public function setAddress(SetAddressRequest $request)
    {
        request()->request->add(['mode' => 'checkout']);
        $user = $request->user();
        $address = $user->addresses()->findOrFail($request->input('address_id'));
        $cart = Cart::whereUserId($user->id)->withCount('items')->firstOrFail();
        $cart->update([
            'address_id' => $address->id,
        ]);

        $this->updateTotalPrice($cart);

        return new CartResource($cart);
    }

    /**
     * Get Shipping Cost
     *
     * Calculates and retrieves the shipping cost for the user's cart based on the provided request.
     *
     * This method determines the shipping cost by analyzing the user's cart,
     * selected items, associated warehouse, and destination address. If the shipping
     * address is provided and reachable, it calculates the cost based on pre-defined
     * vehicle types and retrieves the delivery quote. If any issue is encountered (e.g.,
     * no shipping address or unreachable address), appropriate error responses are
     * returned.
     *
     * @param Request $request The HTTP request containing the user's details and cart information.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the shipping cost details or an error message.
     */
    public function getShippingCost(Request $request)
    {
        $user = $request->user();
        $cart = Cart::whereUserId($user->id)->with('items.product')->firstOrFail();

        // Validasi alamat
        if (is_null($cart->address_id)) {
            $cart->shipping_cost = 0;
            $cart->vehicle_type_id = null;
            $cart->shipping_provider = null;
            $cart->save();
            return response()->json([
                'message' => 'Silakan atur alamat pengiriman sebelum melanjutkan proses.',
                'errors' => [
                    'address_id' => ['Alamat belum diatur.']
                ]
            ], 422);
        }

        // Validasi gudang
        $warehouseIds = $cart->items->pluck('product.warehouse_id')->unique()->filter();
        if ($warehouseIds->count() === 0) {
            $cart->shipping_cost = 0;
            $cart->vehicle_type_id = null;
            $cart->shipping_provider = null;
            $cart->save();
            return response()->json([
                'status' => 'no_warehouse',
                'message' => 'Produk di keranjang belum memiliki gudang asal. Mohon cek data produk.'
            ], 422);
        }
        $warehouseId = $warehouseIds->first();
        $warehouse = Warehouse::find($warehouseId);

        // Validasi item & packaging type
        $selectedItems = $cart->items->where('is_selected', true);
        $packagingTypes = $selectedItems->pluck('product.packaging_type')->unique();
        $selectedCount = $selectedItems->count();
        if ($packagingTypes->count() > 1) {
            return response()->json([
                'status' => 'mixed_packaging_type',
                'message' => 'Pengiriman tidak bisa digabung dengan tipe produk yang berbeda. Silakan pisahkan transaksi.'
            ], 422);
        }
        $packagingType = $packagingTypes->first();
        $geoService = app(GeoRegionService::class);
        $isJabodetabek = $geoService->determineShippingMethod($cart->address->latitude, $cart->address->longitude);

        // Logic palet
        if ($packagingType === 'palet') {
            if (!$isJabodetabek) {
                // return response()->json([
                //     'status' => 'unavailable_address',
                //     'message' => 'Pengiriman palet hanya tersedia untuk alamat di Jabodetabek & Bandung'
                // ], 422);

                $location = $geoService->getLocationFromGoogleMaps($cart->address->latitude, $cart->address->longitude);
                $transportType = $location['transport_type'] ?? null;
                $loadType = $location['load_type'] ?? null;

                $city = preg_replace([
                    '/^(Kota|Kabupaten)\s+/i',
                    '/\s+(City|Regency)$/i'
                ], '', $location['city']);
                $apiForwarder = app(ApiRequest::class);
                $destination = $apiForwarder->post('/citylist', 'CITYLIST', [
                    'city_name' => $city
                ]);
                if (empty($destination['data']) || !isset($destination['data'][0]['item_id'])) {
                    return response()->json([
                        'status' => 'unavailable_address',
                        'message' => 'Kota tujuan tidak tersedia untuk pengiriman ' . str_replace('_', ' ', $packagingType) . '. Silakan pilih kota lain.'
                    ], 422);
                }

                $costs = $apiForwarder->post('/pricinglist_l8', 'PRICINGLIST_L8', [
                    "origin_city" => 208,
                    "destination_city" => $destination['data'][0]['item_id'],
                    "destination_subdistrict" => 0,
                    "transport_type" => $transportType,
                    "load_type" => $loadType,
                    "service_type" => 1,
                    "vehicle_type" => 0 // Mandatory [7 = CDD Long; 12 = Wing Box; 0 = Selain Land Transport]
                ]);
                return $costs;
                if (isset($costs['data']) && collect($costs['data'])->count() > 0) {
                    $cost = $costs['data'][0];
                    if (isset($cost['tariff_idr']) && !is_numeric($cost['tariff_idr'])) {
                        $cart->shipping_cost = 0;
                        $cart->shipping_provider = null;
                        $cart->save();
                        return response()->json([
                            'status' => 'unavailable_address',
                            'message' => 'Alamat pengiriman tidak terjangkau. Silakan cek kembali alamat Anda.'
                        ], 422);
                    }
                    $cart->shipping_cost = $cost['tariff_idr'];
                    $cart->shipping_provider = 'Forwarder';
                    $cart->requirement_provider = $cost;
                    $cart->save();
                    return response()->json([
                        'data' => [
                            'total_cost' => [
                                'value' => $cost['tariff_idr'],
                                'formatted' => $cost['currency'] . ' ' . number_format($cost['tariff_idr'], 0, ',', '.')
                            ]
                        ]
                    ]);
                } else {
                    $cart->shipping_cost = 0;
                    $cart->shipping_provider = null;
                    $cart->save();
                    return response()->json([
                        'status' => 'unavailable_address',
                        'message' => 'Alamat pengiriman tidak terjangkau. Silakan cek kembali alamat Anda.'
                    ], 422);
                }
            }
            // ...existing code Deliveree
            // selected vehicle for production
            $selectedVehicle = match (true) {
                $selectedCount >= 5 && $selectedCount <= 8 => 2703, // CDE Box Liquid8
                $selectedCount >= 9 => 2723, // CDD Box Liquid8
                default => 2701, // van liquid8
            };
            // $selectedVehicle = match (true) {
            //     $selectedCount >= 5 && $selectedCount <= 8 => 14,
            //     $selectedCount >= 9 => 75,
            //     default => 76,
            // };
            $data = [
                'time_type' => 'now',
                'vehicle_type_id' => $selectedVehicle,
                'locations' => [
                    [
                        'address' => $warehouse->address,
                        'latitude' => $warehouse->latitude,
                        'longitude' => $warehouse->longitude,
                    ],
                    [
                        'address' => $cart->address->address,
                        'latitude' => $cart->address->latitude,
                        'longitude' => $cart->address->longitude,
                    ]
                ]
            ];
            $costs = Deliveree::getDeliveryQuote($data);
            if (isset($costs['data']) && collect($costs['data'])->count() > 0) {
                $cost = $costs['data'][0];
                $cart->shipping_cost = $cost['total_fees'];
                $cart->vehicle_type_id = $selectedVehicle;
                $cart->shipping_provider = 'Deliveree';
                $cart->save();
                return response()->json([
                    'data' => [
                        'total_cost' => [
                            'value' => $cost['total_fees'],
                            'formatted' => $cost['currency'] . ' ' . number_format($cost['total_fees'], 0, ',', '.')
                        ],
                        'total_distance' => $cost['total_distance'],
                        'distance_fees' => [
                            'value' => $cost['distance_fees'],
                            'formatted' => $cost['currency'] . ' ' . number_format($cost['distance_fees'], 0, ',', '.')
                        ],
                        'way_point_fees' => [
                            'value' => $cost['way_point_fees'],
                            'formatted' => $cost['currency'] . ' ' . number_format($cost['way_point_fees'], 0, ',', '.')
                        ],
                        'cod_pod_fees' => [
                            'value' => $cost['cod_pod_fees'],
                            'formatted' => $cost['currency'] . ' ' . number_format($cost['cod_pod_fees'], 0, ',', '.')
                        ],
                        'extra_fees' => [
                            'value' => $cost['extra_fees'],
                            'formatted' => $cost['currency'] . ' ' . number_format($cost['extra_fees'], 0, ',', '.')
                        ],
                        'surcharges_fees' => [
                            'value' => $cost['surcharges_fees'],
                            'formatted' => $cost['currency'] . ' ' . number_format($cost['surcharges_fees'], 0, ',', '.')
                        ],
                        'surcharges_adjustments_fees' => [
                            'value' => $cost['surcharges_adjustments_fees'],
                            'formatted' => $cost['currency'] . ' ' . number_format($cost['surcharges_adjustments_fees'], 0, ',', '.')
                        ]
                    ]
                ]);
            } else {
                $cart->shipping_cost = 0;
                $cart->vehicle_type_id = null;
                $cart->shipping_provider = null;
                $cart->save();
                return response()->json([
                    'status' => 'unavailable_address',
                    'message' => 'Alamat pengiriman tidak terjangkau. Silakan cek kembali alamat Anda.'
                ], 422);
            }
        }

        // Logic kontainer & truck_load
        if (in_array($packagingType, ['container', 'truck_load'])) {
            if ($selectedCount > 1) {
                return response()->json([
                    'status' => 'mixed_packaging_type',
                    'message' => 'Pesanan ' . str_replace('_', ' ', $packagingType) . ' hanya bisa 1 item per transaksi. Untuk lebih dari satu, lakukan transaksi terpisah.'
                ], 422);
            }

            // Provider Deliveree untuk Jabodetabek
            if ($isJabodetabek) {
                $vehicleTypeId = null;
                if ($packagingType === 'truck_load') {
                    $selectedProduct = $selectedItems->first()?->product;
                    // $vehicleTypeId = $selectedProduct?->truck_load_vehicle_type_id;
                    $vehicleTypeId = 2725; // CDD Long Liquid8 (req anas, di buat default ke sini dulu)
                } elseif ($packagingType === 'container') {
                    $vehicleTypeId = 2728;
                }

                $data = [
                    'time_type' => 'now',
                    'vehicle_type_id' => $vehicleTypeId,
                    'locations' => [
                        [
                            'address' => $warehouse->address,
                            'latitude' => $warehouse->latitude,
                            'longitude' => $warehouse->longitude,
                        ],
                        [
                            'address' => $cart->address->address,
                            'latitude' => $cart->address->latitude,
                            'longitude' => $cart->address->longitude,
                        ]
                    ]
                ];
                $costs = Deliveree::getDeliveryQuote($data);
                if (isset($costs['data']) && collect($costs['data'])->count() > 0) {
                    $cost = $costs['data'][0];
                    $cart->shipping_cost = $cost['total_fees'];
                    $cart->vehicle_type_id = $vehicleTypeId;
                    $cart->shipping_provider = 'Deliveree';
                    $cart->requirement_provider = $cost;
                    $cart->save();
                    return response()->json([
                        'data' => [
                            'total_cost' => [
                                'value' => $cost['total_fees'],
                                'formatted' => $cost['currency'] . ' ' . number_format($cost['total_fees'], 0, ',', '.')
                            ]
                        ]
                    ]);
                } else {
                    $cart->shipping_cost = 0;
                    $cart->vehicle_type_id = $vehicleTypeId;
                    $cart->shipping_provider = null;
                    $cart->save();
                    return response()->json([
                        'status' => 'unavailable_address',
                        'message' => 'Alamat pengiriman tidak terjangkau. Silakan cek kembali alamat Anda.'
                    ], 422);
                }
            } else {
                // Provider Forwarder untuk non-Jabodetabek
                $location = $geoService->getLocationFromGoogleMaps($cart->address->latitude, $cart->address->longitude);
                $transportType = $location['transport_type'] ?? null;
                $loadType = $location['load_type'] ?? null;

                // if ($packagingType === 'container' && !($transportType == 1 && $loadType == 1)) {
                //     return response()->json([
                //         'status' => 'unavailable_address',
                //         'message' => 'Pengiriman dengan alamat ini tidak dapat dilakukan untuk tipe produk container.'
                //     ], 422);
                // }
                if ($packagingType === 'truck_load' && !($transportType == 3 && $loadType == 4)) {
                    return response()->json([
                        'status' => 'unavailable_address',
                        'message' => 'Pengiriman dengan alamat ini tidak dapat dilakukan untuk tipe produk truck load.'
                    ], 422);
                }

                $city = preg_replace([
                    '/^(Kota|Kabupaten)\s+/i',
                    '/\s+(City|Regency)$/i'
                ], '', $location['city']);
                $apiForwarder = app(ApiRequest::class);
                $destination = $apiForwarder->post('/citylist', 'CITYLIST', [
                    'city_name' => $city
                ]);
                if (empty($destination['data']) || !isset($destination['data'][0]['item_id'])) {
                    return response()->json([
                        'status' => 'unavailable_address',
                        'message' => 'Kota tujuan tidak tersedia untuk pengiriman ' . str_replace('_', ' ', $packagingType) . '. Silakan pilih kota lain.'
                    ], 422);
                }

                $costs = $apiForwarder->post('/pricinglist_l8', 'PRICINGLIST_L8', [
                    "origin_city" => 208,
                    "destination_city" => $destination['data'][0]['item_id'],
                    "destination_subdistrict" => 0,
                    "transport_type" => $transportType,
                    "load_type" => $loadType,
                    "service_type" => 1,
                    "vehicle_type" => $packagingType === "container" ? 12 : 7 // Mandatory [7 = CDD Long; 12 = Wing Box; 0 = Selain Land Transport]
                ]);
                if (isset($costs['data']) && collect($costs['data'])->count() > 0) {
                    $cost = $costs['data'][0];
                    if (isset($cost['tariff_idr']) && !is_numeric($cost['tariff_idr'])) {
                        $cart->shipping_cost = 0;
                        $cart->shipping_provider = null;
                        $cart->save();
                        return response()->json([
                            'status' => 'unavailable_address',
                            'message' => 'Alamat pengiriman tidak terjangkau. Silakan cek kembali alamat Anda.'
                        ], 422);
                    }
                    $cart->shipping_cost = $cost['tariff_idr'];
                    $cart->shipping_provider = 'Forwarder';
                    $cart->requirement_provider = $cost;
                    $cart->save();
                    return response()->json([
                        'data' => [
                            'total_cost' => [
                                'value' => $cost['tariff_idr'],
                                'formatted' => $cost['currency'] . ' ' . number_format($cost['tariff_idr'], 0, ',', '.')
                            ]
                        ]
                    ]);
                } else {
                    $cart->shipping_cost = 0;
                    $cart->shipping_provider = null;
                    $cart->save();
                    return response()->json([
                        'status' => 'unavailable_address',
                        'message' => 'Alamat pengiriman tidak terjangkau. Silakan cek kembali alamat Anda.'
                    ], 422);
                }
            }
        }
    }

    /**
     * Place Order
     *
     * Places an order for the user's cart items.
     *
     * This method takes a PlaceOrderRequest object as input and processes the user's cart items
     * to create a new order. The user's ID is obtained from the request and used to retrieve the cart
     * from the database. Inside a database transaction, the order is created and associated with the user.
     * The total price, payment method, and additional notes are copied from the request to the order.
     *
     * Next, the selected items from the cart are looped through and each item is added to the order
     * by creating a new order item with the product ID, quantity, and price copied from the cart item.
     *
     * Finally, the createInvoices() method is called to generate invoices for the order, and a new
     * OrderResource object is returned representing the created order.
     *
     * @param PlaceOrderRequest $request The request object containing the order details.
     * @return OrderResource
     * @throws \Throwable
     */
    public function placeOrder(PlaceOrderRequest $request)
    {
        $user = $request->user();
        $cart = Cart::whereUserId($user->id)
            ->with(['items' => function ($query) {
                $query->where('is_selected', true)->with('product');
            }])
            ->withCount(['items', 'items as selected_items_count' => function ($query) {
                $query->where('is_selected', true);
            }])
            ->firstOrFail();

        // Validate cart is not empty
        if ($cart->items_count == 0) {
            throw ValidationException::withMessages([
                'cart' => 'Keranjang Anda kosong. Silakan tambahkan produk terlebih dahulu.'
            ]);
        }

        // Validate at least one item is selected
        if ($cart->selected_items_count == 0) {
            throw ValidationException::withMessages([
                'cart' => 'Tidak ada produk yang dipilih. Silakan pilih minimal satu produk.'
            ]);
        }

        // Validate cart total price
        if ($cart->total_price <= 0) {
            throw ValidationException::withMessages([
                'cart' => 'Total harga keranjang tidak valid. Silakan periksa kembali produk Anda.'
            ]);
        }

        // Validate address for courier pickup
        if ($cart->address_id == null && $cart->shipping_method == ShippingMethodEnum::COURIER_PICKUP) {
            throw ValidationException::withMessages([
                'address' => 'Anda belum mengatur alamat pengiriman.'
            ]);
        }

        // Validate selected items have valid products
        foreach ($cart->items as $item) {
            if (!$item->product || $item->product->trashed()) {
                throw ValidationException::withMessages([
                    'cart' => 'Beberapa produk dalam keranjang Anda tidak tersedia lagi. Silakan refresh halaman.'
                ]);
            }

            // Validate product is active (not disabled by admin)
            if (!$item->product->is_active) {
                throw ValidationException::withMessages([
                    'cart' => "Produk '{$item->product->name}' sudah tidak tersedia. Silakan hapus dari keranjang dan pilih produk lain."
                ]);
            }

            // Validate product is not sold out (race condition prevention)
            if ($item->product->sold_out) {
                throw ValidationException::withMessages([
                    'cart' => "Produk '{$item->product->name}' sudah terjual. Silakan hapus dari keranjang dan pilih produk lain."
                ]);
            }

            if ($item->quantity <= 0) {
                throw ValidationException::withMessages([
                    'cart' => 'Jumlah produk tidak valid. Silakan periksa kembali keranjang Anda.'
                ]);
            }

            if ($item->price <= 0) {
                throw ValidationException::withMessages([
                    'cart' => 'Harga produk tidak valid. Silakan hubungi customer service.'
                ]);
            }
        }

        try {
            $order = DB::transaction(function () use ($cart, $request, $user) {
                $tax = app(PpnSettings::class);
                $expired = now()->addMinutes(15);

                if ($request->input('payment_type') == OrderPaymentTypeEnum::SplitPayment->value) {
                    $expired = now()->addHour();
                }

                if ($cart->shipping_method == ShippingMethodEnum::COURIER_PICKUP && $cart->shipping_cost <= 0) {
                    throw ValidationException::withMessages(['shipping_cost' => ['Shipping cost must be greater than 0 for courier pickup.']]);
                }

                $shipping_cost = $cart->shipping_method == ShippingMethodEnum::COURIER_PICKUP ? $cart->shipping_cost : 0;
                $total = $cart->total_price + $shipping_cost - ($cart->discount_amount ?? 0);

                if ($tax->enabled) {
                    $total += $cart->tax_amount;
                }

                if ($request->input('is_insurance') === true && $cart->shipping_provider === "Forwarder") {
                    $insurance_percentage = $cart->requirement_provider['transport_name'] === 'LAND TRANSPORT' ? 0.125 : 0.2;
                    $insurance_amount = ($insurance_percentage / 100) * $cart->total_price;
                    $total += $insurance_amount;
                }

                $order = Order::withCount(['items', 'invoices'])->with(['items', 'invoices'])->create([
                    'user_id' => $user->id,
                    'total_price' => $total,
                    'payment_method' => $request->input('payment_type'),
                    'notes' => $request->input('notes'),
                    'payment_expired_at' => $expired,
                    'shipping_method' => $cart->shipping_method,
                    'name' => $cart->address?->name ?? $cart->user->name,
                    'phone_number' => $cart->address?->phone_number ?? $cart->user->phone_number,
                    'shipping_address' => $cart->address?->address,
                    'latitude' => $cart->address?->latitude,
                    'longitude' => $cart->address?->longitude,
                    'discount_amount' => $cart->discount_amount ?? 0,
                    'tax_rate' => $tax->rate,
                    'tax_amount' => $cart->tax_amount,
                    'is_tax_active' => $tax->enabled,
                ]);

                $cart->items()->whereIsSelected(true)->each(function ($item) use ($order) {
                    $order->items()->create([
                        'product_id' => $item->product_id,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'discount_amount' => $item->discount_amount,
                    ]);

                    $this->setProductToSold($item->product);
                });

                $coupon = Coupon::whereCode($cart->coupon_code)->first();
                $coupon?->usages()->create([
                    'user_id' => $user->id,
                    'order_id' => $order->id,
                ]);

                if ($cart->shipping_method == ShippingMethodEnum::COURIER_PICKUP) {
                    $order->shipping()->create([
                        'shipping_provider' => $cart->shipping_provider,
                        'requirement_provider' => $cart->requirement_provider,
                        'shipping_cost' => $cart->shipping_cost,
                        'vehicle_type' => $cart->vehicle_type_id,
                        'is_insurance' => $request->input('is_insurance') ?? false,
                        'insurance_amount' => $insurance_amount ?? 0,
                        'insurance_percentage' => $insurance_percentage ?? 0,
                    ]);
                }

                $this->createInvoices($order, $request);
                $this->removeItemAfterOrder($cart);

                return $order;
            });
        } catch (\Exception $e) {
            Log::error('Order Creation Failed: ' . $e->getMessage());
            return response()->json([
                'status' => 'order_creation_failed',
                'message' => 'Gagal membuat order'
            ], 500);
        }

        event(new OrderCreatedEvent($order));

        return new OrderResource($order);
    }

    public function placeOrderInWMS(PlaceOrderRequest $request)
    {
        // Validasi email dan code_document_sale selalu required
        $data = $request->all();
        $user = User::where('email', $data['email'])->first();
        $rules = [
            'email' => 'required|email',
            'code_document_sale' => 'required|string',
        ];
        $isRegisterEmail = isset($data['register_email']) && $data['register_email'] === true;

        // Jika user belum terdaftar dan belum ada flag register_email, return response khusus
        if (!$user && !$isRegisterEmail) {
            return response()->json([
                'status' => 'email_not_registered',
                'message' => 'Email belum terdaftar di Bulky. Apakah ingin mendaftarkan email ini?'
            ], 422);
        }

        // phone_number hanya required jika user baru
        if (!$user) {
            $rules['phone_number'] = 'required';
        }
        $validator = Validator::make($data, $rules);
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Normalisasi phone_number sebelum validasi unik dan simpan
        $normalizedPhone = null;
        if (isset($data['phone_number'])) {
            $normalizedPhone = PhoneHelper::formatIndonesianPhoneNumber($data['phone_number']);
        }

        $password = null;
        if (!$user) {
            // Validasi phone_number unik setelah dinormalisasi
            if (User::where('phone_number', $normalizedPhone)->exists()) {
                return response()->json([
                    'status' => 'phone_number_exists',
                    'message' => 'Nomor telepon sudah terdaftar di Bulky.',
                ], 422);
            }
            $username = 'user_' . substr(md5($data['email'] . $normalizedPhone), 0, 8);
            $name = $data['name'] ?? 'Customer ' . $normalizedPhone;
            $password = $data['password'] ?? bin2hex(random_bytes(4));
            $user = activity()->withoutLogs(function () use ($data, $normalizedPhone, $name, $username, $password) {
                return User::create([
                    'email' => $data['email'],
                    'phone_number' => $normalizedPhone,
                    'name' => $name,
                    'username' => $username,
                    'password' => Hash::make($password),
                ]);
            });
            // Kirim notifikasi email & WA berisi password
            $user->notify(new RegisteredNotification($user, $password));
        }

        // Validasi order duplikat
        $existingOrder = Order::where('user_id', $user->id)
            ->whereHas('items', function ($q) use ($data) {
                $q->whereHas('product', function ($p) use ($data) {
                    $p->where('name', 'Palet ' . $data['code_document_sale']);
                });
            })
            ->whereIn('order_status', ['pending', 'paid', 'processing']) // sesuaikan status aktif di project
            ->first();
        if ($existingOrder) {
            return response()->json([
                'status' => 'duplicate_order',
                'message' => 'Order produk ini sudah dibuat.'
            ], 422);
        }

        $product = Product::whereName('Palet ' . $data['code_document_sale'])
            ->whereSoldOut(1)
            ->first();

        if (!$product) {
            throw ValidationException::withMessages(['code_document_sale' => 'Product not found or not available.']);
        }

        $order = DB::transaction(function () use ($product, $request, $user) {
            $tax = app(PpnSettings::class);
            $expired = now()->addMinutes(15);

            if ($request->input('payment_type') == OrderPaymentTypeEnum::SplitPayment->value) {
                $expired = now()->addHour();
            }

            $total = $product->price;

            if ($tax->enabled) {
                $taxAmount = $tax->rate / 100 * $total;
                $total += $taxAmount;
            }

            $order = Order::withCount(['items', 'invoices'])->with(['items', 'invoices'])->create([
                'user_id' => $user->id,
                'total_price' => $total,
                'payment_method' => $request->input('payment_type'),
                'notes' => $request->input('notes'),
                'payment_expired_at' => $expired,
                'shipping_method' => 'self_pickup',
                'name' => $user->name,
                'phone_number' => $user->phone_number,
                'discount_amount' => 0,
                'tax_rate' => $tax->rate,
                'tax_amount' => $tax->enabled ? $taxAmount : 0,
                'is_tax_active' => $tax->enabled,
            ]);

            $order->items()->create([
                'product_id' => $product->id,
                'quantity' => 1,
                'price' => $product->price,
                'discount_amount' => 0,
            ]);

            $this->createInvoices($order, $request);

            return $order;
        });

        event(new OrderCreatedEvent($order));

        return new OrderResource($order);
    }

    /**
     * Apply Coupon
     *
     * Applies a coupon code to the user's cart during the checkout process.
     *
     * This method validates the coupon code and checks its applicability to the
     * items in the cart based on various criteria such as expiry date, applicable
     * products or categories, usage limits, and user-specific restrictions. If
     * the coupon is valid, it calculates the total discount and updates the cart
     * accordingly.
     *
     * @param Request $request The HTTP request object containing the coupon code
     * @return CartResource The updated cart resource with the applied discount, if any
     * @throws ValidationException Thrown if the coupon code is invalid, expired,
     *                             or not applicable for the user's cart items
     */
    public function applyCoupon(Request $request)
    {
        request()->request->add(['mode' => 'checkout']);

        $request->validate([
            'coupon_code' => 'nullable',
        ]);
        $user = $request->user();
        $cart = Cart::whereUserId($user->id)->with('items')->firstOrFail();

        if ($request->filled('coupon_code')) {

            $coupon = Coupon::whereCode($request->input('coupon_code'))->with(['users', 'products'])->withCount('usages')->first();

            if ($coupon) {

                if ($coupon->expiry_date && now()->greaterThan($coupon->expiry_date)) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'Kupon ini telah kedaluwarsa.'
                    ]);
                }

                if ($coupon->users->isNotEmpty() && !$coupon->users->contains($request->user()->id)) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'Anda tidak dapat menggunakan kupon ini.'
                    ]);
                }

                if (!empty($coupon->usage_limit) && $coupon->usages_count >= $coupon->usage_limit) {
                    throw ValidationException::withMessages([
                        'coupon_code' => 'Kupon telah mencapai batas penggunaan.'
                    ]);
                }

                $applicableProductIds = $cart->items()->whereIsSelected(true)->pluck('product_id')->toArray();

                // Filter by minimal purchase
                if (!empty($coupon->minimum_purchase)) {
                    $applicableProductIds = $cart->items()
                        ->whereIsSelected(true)
                        ->where('price', '>=', $coupon->minimum_purchase)
                        ->pluck('product_id')
                        ->toArray();

                    if (empty($applicableProductIds)) {
                        throw ValidationException::withMessages([
                            'coupon_code' => 'Tidak ada produk yang memenuhi minimal pembelian kupon (' . number_format($coupon->minimum_purchase, 0, ',', '.') . ').'
                        ]);
                    }
                }

                if ($coupon->products->isNotEmpty()) {
                    $applicableProductIds = $cart->items()->whereIsSelected(true)->whereIn('product_id', $coupon->products->pluck('id'))->pluck('product_id')->toArray();
                    if (empty($applicableProductIds)) {
                        throw ValidationException::withMessages([
                            'coupon_code' => 'Kupon ini tidak berlaku untuk produk dalam keranjang Anda.'
                        ]);
                    }
                }

                if ($coupon->categories->isNotEmpty()) {
                    // Filter applicableProductIds further by category
                    $applicableProductIds = $cart->items()->whereIsSelected(true)
                        ->whereIn('product_id', $applicableProductIds)
                        ->whereHas('product', function ($query) use ($coupon) {
                            $query->whereIn('product_category_id', $coupon->categories->pluck('id'));
                        })
                        ->pluck('product_id')
                        ->toArray();

                    if (empty($applicableProductIds)) {
                        throw ValidationException::withMessages([
                            'coupon_code' => 'Kupon hanya berlaku untuk kategori: ' . $coupon->categories->pluck('name_trans')->join(', ') . '.'
                        ]);
                    }
                }

                $items = $cart->items()->whereIn('product_id', $applicableProductIds)->get();
                $totalDiscount = 0;

                $items->each(function ($item) use ($cart, $coupon, &$totalDiscount) {
                    if ($coupon->discount_type == CouponDiscountTypeEnum::Amount) {
                        $discountAmount = (float)$coupon->discount_value;
                        $item->update(['discount_amount' =>  $discountAmount]);
                        $totalDiscount += $discountAmount;
                    } elseif ($coupon->discount_type == CouponDiscountTypeEnum::Percent) {
                        $discountAmount = (($item->price * $coupon->discount_value) / 100);
                        $item->update(['discount_amount' =>  $discountAmount]);
                        $totalDiscount += $discountAmount;
                    }
                });

                $cart->discount_amount = $totalDiscount;
                $cart->coupon_code = $request->input('coupon_code');
            } else {
                $cart->discount_amount = 0;
                $cart->coupon_code = null;
                $cart->items()->update(['discount_amount' => 0]);
            }
        } else {
            $cart->discount_amount = 0;
            $cart->coupon_code = null;
            $cart->items()->update(['discount_amount' => 0]);
        }

        $cart->save();

        $this->updateTotalPrice($cart);
        $cart = $cart->load('items');

        return new CartResource($cart);
    }

    /**
     * Clear Coupon
     *
     * Clears the applied coupon in the user's cart and resets the discount amount.
     *
     * @param Request $request The HTTP request instance containing user data
     * @return void
     */
    public function clearCoupon(Request $request)
    {
        request()->request->add(['mode' => 'checkout']);
        $user = $request->user();
        $cart = Cart::whereUserId($user->id)->with('items')->firstOrFail();

        $cart->discount_amount = 0;
        $cart->coupon_code = null;
        $cart->save();

        $this->updateTotalPrice($cart);
    }

    /**
     * Get Pickup Info
     *
     * Retrieves and returns the pickup information settings in a JSON response.
     *
     * @return JsonResponse The JSON response containing the pickup information settings
     */
    public function getPickupInfo(): JsonResponse
    {
        $settings = new  PickupInfoSetting();

        $now = now()->dayOfWeek;
        $days = collect($settings->open_hour);
        $day = $days->where('day', $now)->first();

        $is_open = $day['is_open'];
        $start = now()->format('H:i') >= $day['start_time'];
        $end = now()->format('H:i') < $day['end_time'];

        return response()->json([
            'data' => [
                'address' => $settings->address,
                'operational_hours' => $settings->operational_hours,
                'whatsapp_number' => $settings->whatsapp_number,
                'is_open' => $is_open && $start && $end,
            ]
        ]);
    }

    /**
     * Updates the total price of a cart.
     *
     * This method calculates the total price of a cart by summing the product of each selected item's quantity and price.
     * The total price is then saved to the cart.
     *
     * @param Cart $cart The cart to update.
     * @return void
     */
    private function updateTotalPrice(Cart $cart)
    {
        $settings = app(PpnSettings::class);
        // Ensure we have fresh items with product relation when cart exists in DB.
        if ($cart->exists) {
            $items = $cart->items()->with('product')->get();
        } else {
            // For in-memory cart instances (not persisted), use whatever is loaded or an empty collection.
            $items = $cart->relationLoaded('items') ? $cart->items : collect();
        }

        // Only consider selected items whose product is present, active and not sold out.
        $selectedItems = $items->filter(function ($item) {
            return ($item->is_selected ?? false)
                && $item->product
                && ($item->product->is_active ?? false)
                && !($item->product->sold_out ?? false);
        });

        $subtotal = $selectedItems->sum(fn($item) => $item->quantity * $item->price);

        if ($settings->enabled) {
            $taxableBase = max(0, $subtotal - ($cart->discount_amount ?? 0));
            $cart->tax_amount = $taxableBase * $settings->rate / 100;
        } else {
            $cart->tax_amount = 0;
        }

        $cart->total_price = $subtotal;

        $cart->save();
    }

    /**
     * Creates invoices for an order.
     *
     * This method creates invoices based on the payment type specified in the request. If the payment type is "Single Payment",
     * a single invoice will be created with the amount set to the order's total price and the status set to "Pending". If the payment type
     * is "Split Payment", multiple invoices will be created, one for each friend ID specified in the request. Each invoice will have the
     * amount set to the order's total price and the status set to "Pending". If the payment type is neither "Single Payment" nor "Split Payment",
     * a ValidationException will be thrown.
     *
     * @param Order $order The order for which to create invoices.
     * @param Request $request The request containing the payment type and friend IDs (if applicable).
     * @return void
     * @throws ValidationException If the payment type is not supported.
     */
    private function createInvoices(Order $order, Request $request)
    {
        if ($request->input('payment_type') == OrderPaymentTypeEnum::SinglePayment->value) {
            $order->invoices()->create([
                'user_id' => $order->user_id,
                'amount' => $order->total_price,
                'status' => InvoiceStatusEnum::PENDING,
            ]);
        } elseif ($request->input('payment_type') == OrderPaymentTypeEnum::SplitPayment->value) {
            $order->invoices()->create([
                'user_id' => $order->user_id,
                'amount' => 0,
                'status' => InvoiceStatusEnum::PENDING,
            ]);

            foreach ($request->input('friend_ids') as $friendId) {
                $order->invoices()->create([
                    'user_id' => $friendId,
                    'amount' => 0,
                    'status' => InvoiceStatusEnum::PENDING,
                ]);
            }
        } else {
            throw ValidationException::withMessages([
                'payment_type' => ['Payment type not supported'],
            ]);
        }
    }

    /**
     * Removes the selected items from the cart after an order is placed.
     *
     * @param Cart $cart The cart object from which the items will be removed
     * @return void
     */
    private function removeItemAfterOrder(Cart $cart)
    {
        $cart->items()->whereIsSelected(true)->delete();

        $cart->shipping_method = null;
        $cart->shipping_cost = 0;
        $cart->coupon_code = null;
        $cart->discount_amount = 0;
        $cart->save();

        $this->updateTotalPrice($cart);
    }

    /**
     * Marks the given product as sold out with optimistic locking.
     * Uses WHERE condition to prevent double-checkout race condition.
     * 
     * @param Product $product The product instance to be updated
     * @return void
     * @throws \Exception If product is already sold by another user
     */
    private function setProductToSold(Product $product)
    {
        // Optimistic locking: only update if product is still available
        $affected = Product::where('id', $product->id)
            ->where('sold_out', false)
            ->where('is_active', true)
            ->update([
                'sold_out' => true,
                'updated_at' => now(),
            ]);

        // If no rows were affected, product was already sold by another user
        if ($affected === 0) {
            throw new \Exception("Produk '{$product->name}' sudah terjual oleh user lain. Transaksi dibatalkan.");
        }

        Log::info("Product marked as sold", [
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);
    }
}
