<?php

namespace App\Http\Controllers\Api\Cart;

use App\Enums\CouponDiscountTypeEnum;
use App\Enums\InvoiceStatusEnum;
use App\Enums\OrderPaymentTypeEnum;
use App\Enums\ShippingMethodEnum;
use App\Events\Order\OrderCreatedEvent;
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
use App\Services\Deliveree\Deliveree;
use App\Settings\PickupInfoSetting;
use App\Settings\PpnSettings;
use DB;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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

        $cart->items()->firstOrCreate(
            ['product_id' => $request->input('product_id')],
            [
                'quantity' => 1,
                'price' => $product->price,
            ]
        );

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
     *
     * @return CartResource The resource representation of the user's cart.
     * @throws ModelNotFoundException if the cart is not found.
     */
    public function getCart(Request $request)
    {
        $user_id = $request->user()->id;
        $cart = Cart::withCount('items')->firstOrCreate(
            ['user_id' => $user_id],
            [
                'total_price' => 0,
                'payment_method' => OrderPaymentTypeEnum::SinglePayment,
            ]
        );

        // Check and remove soft-deleted products from the cart
        $cart->items()->withTrashed()->each(function (CartItem $item) {
            if ($item->product()->withTrashed()->first()?->trashed()) {
                $item->delete();
            }
        });

        if($request->filled('mode') && $request->input('mode') == 'checkout'){
            $cart->update([
                'coupon_code' => null,
                'discount_amount' => 0,
            ]);
            $cart->items()->update(['discount_amount' => 0]);
        }

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
        $user_id = $request->user()->id;
        $cart = Cart::whereUserId($user_id)->withCount('items')->firstOrFail();

        foreach ($request->input('cart_items') as $data) {
            $cart->items()
                ->findOrFail($data['id'])
                ->update(['is_selected' => $data['selected']]);
        }

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
        $cart = Cart::whereUserId($user->id)->withCount('items')->firstOrFail();

        if(!is_null($cart->address_id)){
//            $vehicleTypeIds = $cart->items->pluck('product.vehicle_type_id')->unique();
            $warehouseIds = $cart->items->pluck('product.warehouse_id')->unique();

            if($warehouseIds->count() > 0){
                $warehouseId = $warehouseIds->first();
                $warehouse = Warehouse::find($warehouseId);

//                $vehicles = Deliveree::getVehicleTypes();
//                $vehicles = $vehicles['data'];

//                $filteredVehicles = collect($vehicles)->whereIn('id', $vehicleTypeIds);
//                $selectedVehicle = $filteredVehicles->sortByDesc(function ($vehicle) {
//                    return $vehicle['cargo_cubic_meter'];
//                })->first();

                $selectedItemCount = $cart->items()->where('is_selected', true)->count();

                $selectedVehicle = match (true) {
                    $selectedItemCount >= 5 && $selectedItemCount <= 8 => 2703,
                    $selectedItemCount >= 9 => 2723,
                    default => 2701,
                };

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

                if(isset($costs['data']) && collect($costs['data'])->count() > 0) {
                    $cost = $costs['data'][0];

                    $cart->shipping_cost = $cost['total_fees'];
                    $cart->vehicle_type_id = $selectedVehicle;
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
                    $cart->save();

                    return response()->json([
                        'message' => 'Alamat pengiriman tidak terjangkau.',
                        'shipping_cost' => false,
                    ], 422);
                }

            }
        }

        $cart->shipping_cost = 0;
        $cart->vehicle_type_id = null;
        $cart->save();

        return response()->json([
            'message' => 'Anda belum mengatur alamat pengiriman.',
            'errors' => [
                'address_id' => ['Anda belum mengatur alamat pengiriman.']
            ]
        ], 422);
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
        $cart = Cart::whereUserId($user->id)->withCount('items')->firstOrFail();

        if($cart->address_id == null && $cart->shipping_method == ShippingMethodEnum::COURIER_PICKUP){
            throw ValidationException::withMessages([
                'address' => 'Anda belum mengatur alamat pengiriman.'
            ]);
        }

        $order = DB::transaction(function () use ($cart, $request, $user) {
            $tax = app(PpnSettings::class);
            $expired = now()->addMinutes(15);

            if($request->input('payment_type') == OrderPaymentTypeEnum::SplitPayment->value) {
                $expired = now()->addHour();
            }

            if ($cart->shipping_method == ShippingMethodEnum::COURIER_PICKUP && $cart->shipping_cost <= 0) {
                throw ValidationException::withMessages(['shipping_cost' => ['Shipping cost must be greater than 0 for courier pickup.']]);
            }

            $shipping_cost = $cart->shipping_method == ShippingMethodEnum::COURIER_PICKUP ? $cart->shipping_cost : 0;
            $total = $cart->total_price + $shipping_cost - ($cart->discount_amount ?? 0);

            if($tax->enabled){
                $total += $cart->tax_amount;
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

            if($cart->shipping_method == ShippingMethodEnum::COURIER_PICKUP){
                $order->shipping()->create([
                    'shipping_cost' => $cart->shipping_cost,
                    'vehicle_type' => $cart->vehicle_type_id,
                ]);
            }

            $this->createInvoices($order, $request);
            $this->removeItemAfterOrder($cart);

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

        if($request->filled('coupon_code')) {

            $coupon = Coupon::whereCode($request->input('coupon_code'))->with(['users', 'products'])->withCount('usages')->first();

            if($coupon){

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

                if(!empty($coupon->usage_limit) && $coupon->usages_count >= $coupon->usage_limit){
                    throw ValidationException::withMessages([
                        'coupon_code' => 'Kupon telah mencapai batas penggunaan.'
                    ]);
                }

                $applicableProductIds = $cart->items()->whereIsSelected(true)->pluck('product_id')->toArray();

                if ($coupon->products->isNotEmpty()) {
                    $applicableProductIds = $cart->items()->whereIsSelected(true)->whereIn('product_id', $coupon->products->pluck('id'))->pluck('product_id')->toArray();
                    if (empty($applicableProductIds)) {
                        throw ValidationException::withMessages([
                            'coupon_code' => 'Kupon ini tidak berlaku untuk produk dalam keranjang Anda.'
                        ]);
                    }
                }

                if ($coupon->categories->isNotEmpty()) {
                    $applicableProductIds = $cart->items()->whereIsSelected(true)->whereHas('product', function ($query) use ($coupon) {
                        $query->whereIn('product_category_id', $coupon->categories->pluck('id'));
                    })->pluck('product_id')->toArray();

                    if (empty($applicableProductIds)) {
                        throw ValidationException::withMessages([
                            'coupon_code' => 'Kupon hanya berlaku untuk kategori: ' . $coupon->categories->pluck('name_trans')->join(', ') . '.'
                        ]);
                    }
                }

                $items = $cart->items()->whereIn('product_id', $applicableProductIds)->get();
                $totalDiscount = 0;

                $items->each(function ($item) use ($cart, $coupon, &$totalDiscount) {
                    if($coupon->discount_type == CouponDiscountTypeEnum::Amount){
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
        $selectedItems = $cart->items->where('is_selected', true);
        $subtotal = $selectedItems->sum(fn($item) => $item->quantity * $item->price);

        if ($settings->enabled) {
            $cart->tax_amount = ($subtotal - $cart->discount_amount) * $settings->rate / 100;
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
     * Marks the given product as sold out.
     *
     * @param Product $product The product instance to be updated
     * @return void
     */
    private function setProductToSold(Product $product)
    {
//        if(config('app.env') === 'production') {
//        }
        $product->sold_out = true;
        $product->save();
    }
}
