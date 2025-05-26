<?php

namespace App\Http\Controllers\Api\Order;

use App\Enums\OrderPaymentStatusEnum;
use App\Enums\OrderPaymentTypeEnum;
use App\Enums\OrderStatusEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Order\GetOrdersRequest;
use App\Http\Requests\Api\Order\ReviewRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\ValidationException;
use JetBrains\PhpStorm\Pure;

/**
 * @group Orders
 *
 * Retrieve a paginated list of orders based on the filters provided in the request.
 *
 * This method retrieves orders for the currently authenticated user
 * and applies filters based on the requested type ('waiting_payment',
 * 'orders', or 'split_payment'). It supports additional filters such as
 * search keywords, order date, and order status. The resulting orders
 * are loaded with related data, such as items, products (including
 * trashed ones), and invoices, and are then returned as a resource collection.
 *
 * @param GetOrdersRequest $request The HTTP request object containing the filters.
 *
 * @return AnonymousResourceCollection A collection of the filtered orders.
 * @throws ValidationException If an invalid type is provided in the request.
 * @authenticated
 */
class OrderController extends Controller
{
    /**
     * List Order
     *
     * Retrieve a paginated list of orders based on the filters provided in the request.
     *
     * This method retrieves orders for the currently authenticated user
     * and applies filters based on the requested type ('waiting_payment',
     * 'orders', or 'split_payment'). It supports additional filters such as
     * search keywords, order date, and order status. The resulting orders
     * are loaded with related data, such as items, products (including
     * trashed ones), and invoices, and are then returned as a resource collection.
     *
     * @param GetOrdersRequest $request The HTTP request object containing the filters.
     *
     * @return AnonymousResourceCollection A collection of the filtered orders.
     * @throws ValidationException If an invalid type is provided in the request.
     *
     */
    public function getOrders(GetOrdersRequest $request)
    {
        $userId = $request->user()->id;

        if($request->input('type') == 'waiting_payment') {
            $orders = Order::whereIn('payment_status', [
                OrderPaymentStatusEnum::PENDING,
                OrderPaymentStatusEnum::PARTIALLY_PAID
            ])
                ->whereNot('payment_method', OrderPaymentTypeEnum::SplitPayment->value)
                ->when($request->input('search'), function ($query, $search) {
                    return $query->where('order_number', 'like', "%{$search}%");
                })
                ->when($request->input('date'), function ($query, $date) {
                    return $query->whereDate('order_date', $date);
                })
                ->whereUserId($userId)
                ->withCount(['items', 'invoices'])
                ->with('items.product', function ($query) {
                    $query->withTrashed();
                })
                ->whereHas('items')
                    ->whereHas('items.product', function ($subQuery) {
                        $subQuery->whereNotNull('id');
                    })
                ->latest()
                ->paginate($request->input('per_page'))
                ->load(['items', 'invoices']);
        } elseif ($request->input('type') == 'orders') {
            $orders = Order::whereNotIn('payment_status', [
                    OrderPaymentStatusEnum::PENDING,
                    OrderPaymentStatusEnum::PARTIALLY_PAID
                ])
                ->whereNot('payment_method', OrderPaymentTypeEnum::SplitPayment->value)
                ->when($request->input('search'), function ($query, $search) {
                    return $query->where('order_number', 'like', "%{$search}%");
                })
                ->when($request->input('date'), function ($query, $date) {
                    return $query->whereDate('order_date', $date);
                })
                ->when($request->input('status'), function ($query, $status) {
                    $query->where('order_status', $status);
                })
                ->whereUserId($userId)
                ->withCount(['items', 'invoices'])
                ->with('items.product', function ($query) {
                    $query->withTrashed();
                })
                ->whereHas('items.product', function ($subQuery) {
                    $subQuery->whereNotNull('id');
                })
                ->latest()
                ->paginate($request->input('per_page'));
        } elseif ($request->input('type') == 'split_payment') {
            $orders = Order::where('payment_method', OrderPaymentTypeEnum::SplitPayment->value)
                ->when($request->input('search'), function ($query, $search) {
                    return $query->where('order_number', 'like', "%{$search}%");
                })
                ->when($request->input('date'), function ($query, $date) {
                    return $query->whereDate('order_date', $date);
                })
                ->when($request->input('status'), function ($query, $status) {
                    $query->where('order_status', $status);
                })
                ->whereHas('invoices', function ($query) use ($userId) {
                    $query->where('user_id', $userId);
                })
                ->withCount(['items', 'invoices'])
                ->with('items.product', function ($query) {
                    $query->withTrashed();
                })
                ->whereHas('items.product', function ($subQuery) {
                    $subQuery->whereNotNull('id');
                })
                ->latest()
                ->paginate($request->input('per_page'));
        } else {
            throw ValidationException::withMessages([
                'type' => ['The type field is invalid.'],
            ]);
        }

        return OrderResource::collection($orders);
    }

    /**
     * Detail Order
     *
     * Retrieve detailed information about a specific order.
     *
     * This method returns a detailed view of the given order by converting
     * the order instance into a structured resource format.
     *
     * @param Order $order The order instance to retrieve details for.
     *
     * @return OrderResource A resource representing the detailed information of the order.
     */
    #[Pure] public function getDetailOrder(Order $order)
    {
        $a = $order;
        return new OrderResource($a);
    }

    /**
     * Submit Review
     *
     * Handles the review submission process for an order.
     *
     * This function processes image uploads, creates reviews for each product
     * in the order, associates the uploaded images with the reviews, and marks
     * the order as reviewed.
     *
     * @param ReviewRequest $request The request instance containing review data.
     * @param Order $order The order instance for which the review is submitted.
     * @return OrderResource The resource representing the updated order.
     */
    public function review(ReviewRequest $request, Order $order)
    {
        $paths = [];

        if($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $paths[] = $image->store('reviews', 'public');
            }
        }

        $item = $order->items()->where('product_id', $request->input('product_id'))->first();
        $review = $item->product->reviews()->create([
            'user_id' => $request->user()->id,
            'order_id' => $order->id,
            'rating' => $request->input('rating'),
            'comment' => $request->input('comment')
        ]);

        foreach ($paths as $path) {
            $review->images()->create([
                'path' => $path
            ]);
        }

//        foreach ($order->items as $item) {
//            $review = $item->product->reviews()->create([
//                'user_id' => $request->user()->id,
//                'order_id' => $order->id,
//                'rating' => $request->input('rating'),
//                'comment' => $request->input('comment')
//            ]);
//
//            foreach ($paths as $path) {
//                $review->images()->create([
//                    'path' => $path
//                ]);
//            }
//        }
        $order->has_reviewed = true;
        $order->save();
        $order->load('items.product.reviews');

        return new OrderResource($order);
    }

    /**
     * Mark as Completed
     *
     * Marks the given order as completed.
     *
     * This function updates the status of the order to "completed" and
     * returns a resource representing the updated order.
     *
     * @param Order $order The order instance to be marked as completed.
     * @return OrderResource The resource representing the updated order.
     */
    public function complete(Order $order)
    {
        $order->update([
            'order_status' => OrderStatusEnum::Completed
        ]);
        return new OrderResource($order);
    }
}
