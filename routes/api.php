<?php

use App\Http\Controllers\Api\Area\AreaController;
use App\Http\Controllers\Api\Auth\EmailVerificationController;
use App\Http\Controllers\Api\Auth\Mobile\AuthController as MobileAuthController;
use App\Http\Controllers\Api\Auth\NewPasswordController;
use App\Http\Controllers\Api\Auth\PasswordController;
use App\Http\Controllers\Api\Auth\PasswordResetLinkController;
use App\Http\Controllers\Api\Auth\Web\AuthController;
use App\Http\Controllers\Api\Banner\BannerController;
use App\Http\Controllers\Api\Cart\CartController;
use App\Http\Controllers\Api\Coupon\CouponController;
use App\Http\Controllers\Api\General\GeneralApiController;
use App\Http\Controllers\Api\Order\InvoiceController;
use App\Http\Controllers\Api\Order\OrderController;
use App\Http\Controllers\Api\Page\PageController;
use App\Http\Controllers\Api\Product\FilterController;
use App\Http\Controllers\Api\Product\ProductController;
use App\Http\Controllers\Api\Testimony\TestimonyController;
use App\Http\Controllers\Api\User\AddressController;
use App\Http\Controllers\Api\User\BankController;
use App\Http\Controllers\Api\User\ProfileController;
use App\Http\Controllers\Api\Video\VideoController;
use App\Http\Controllers\Webhook\DelivereeController;
use App\Http\Controllers\Webhook\MidtransController;
use App\Http\Controllers\Webhook\XenditController;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function () {
    Route::prefix('web')->group(function () {
        Route::post('login', [AuthController::class, 'login'])->middleware('guest');
        Route::post('register', [AuthController::class, 'register'])->middleware('guest');
        Route::post('logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

        Route::prefix('google')->middleware('guest')->group(function () {
            Route::get('/', [AuthController::class, 'google'])->middleware('guest');
            Route::get('callback', [AuthController::class, 'googleCallback'])->middleware('guest');
        });
    });

    Route::prefix('mobile')->group(function () {
        Route::post('login', [MobileAuthController::class, 'login'])->middleware('guest');
        Route::post('register', [MobileAuthController::class, 'register'])->middleware('guest');
        Route::post('google', [MobileAuthController::class, 'google'])->middleware('guest');
        Route::post('logout', [MobileAuthController::class, 'logout'])->middleware('auth:sanctum');
    });

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('guest');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->middleware('guest');
    Route::post('password', [PasswordController::class, 'update'])->middleware('auth:sanctum');
    Route::get('email-verification/{id}/{hash}', [EmailVerificationController::class, 'index'])->middleware('auth:sanctum');
});

Route::prefix('general')->group(function () {
    Route::get('available-banks', [GeneralApiController::class, 'banks']);
    Route::get('floating-button', [GeneralApiController::class, 'floatingButton']);
    Route::prefix('wholesale-form')->group(function () {
        Route::get('budget', [GeneralApiController::class, 'wholesaleFormBudget']);
        Route::post('send', [GeneralApiController::class, 'storeWholesaleForm']);
    });
    Route::get('reviews', [GeneralApiController::class, 'reviews']);
});

Route::prefix('user')->middleware('auth:sanctum')->group(function () {
    Route::get('/', function (Request $request) {
        $user = $request->user();
        return new UserResource($user);
    });

    Route::prefix('profile')->group(function () {
        Route::put('/update', [ProfileController::class, 'update']);
        Route::post('/profile-picture', [ProfileController::class, 'changeProfilePicture']);
        Route::post('/delete-account', [ProfileController::class, 'deleteAccount']);
    })->middleware('auth:sanctum');

    Route::prefix('address')->group(function () {
        Route::get('/', [AddressController::class, 'getAddresses']);
        Route::post('/create', [AddressController::class, 'createAddress']);
        Route::put('/edit', [AddressController::class, 'editAddress']);
        Route::get('/detail/{address}', [AddressController::class, 'getAddressDetail']);
        Route::delete('/delete/{address}', [AddressController::class, 'deleteAddress']);
        Route::put('/set-primary/{address}', [AddressController::class, 'setPrimary']);
    });

    Route::prefix('banks')->group(function () {
        Route::get('/', [BankController::class, 'index']);
        Route::post('/create', [BankController::class, 'create']);
        Route::put('/update/{userBank}', [BankController::class, 'update']);
        Route::delete('/delete/{userBank}', [BankController::class, 'delete']);
    });
});

Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index']);
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'getProducts']);
    Route::get('/detail/{slug}', [ProductController::class, 'detail']);
    Route::get('/related/{slug}', [ProductController::class, 'relatedProduct']);

    Route::prefix('filter')->group(function () {
        Route::get('warehouse', [FilterController::class, 'warehouse']);
        Route::get('categories', [FilterController::class, 'categories']);
        Route::get('brands', [FilterController::class, 'brands']);
        Route::get('conditions', [FilterController::class, 'conditions']);
        Route::get('statuses', [FilterController::class, 'statuses']);
    });
});

Route::prefix('carts')->middleware('auth:sanctum')->group(function () {
    Route::get('/', [CartController::class, 'getCart']);
    Route::post('add', [CartController::class, 'add']);
    Route::delete('remove-item/{product}', [CartController::class, 'removeItem']);
    Route::patch('set-selected-item', [CartController::class, 'setSelectedItem']);
    Route::get('search-friend', [CartController::class, 'searchFriend']);
    Route::patch('set-address', [CartController::class, 'setAddress']);
    Route::patch('set-shipping-method', [CartController::class, 'setShippingMethod']);
    Route::get('shipping-cost', [CartController::class, 'getShippingCost']);
    Route::post('apply-coupon', [CartController::class, 'applyCoupon']);
    Route::delete('clear-coupon', [CartController::class, 'clearCoupon']);
    Route::post('place-order', [CartController::class, 'placeOrder']);
    Route::get('get-pickup-info', [CartController::class, 'getPickupInfo']);
});

Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::get('get-orders', [OrderController::class, 'getOrders']);
    Route::get('get-detail/{order}', [OrderController::class, 'getDetailOrder']);
    Route::post('review/{order}', [OrderController::class, 'review']);
    Route::put('complete/{order}', [OrderController::class, 'complete']);

    Route::prefix('invoice')->group(function () {
        Route::get('get-payment-methods', [InvoiceController::class, 'getPaymentMethod']);
        Route::get('get-invoices-by-order/{order}', [InvoiceController::class, 'getInvoicesByOrder']);
        Route::get('get-my-invoice-by-order/{order}', [InvoiceController::class, 'getMyInvoiceByOrder']);
        Route::patch('set-invoice-amount', [InvoiceController::class, 'setInvoiceAmount']);
        Route::post('create-payment', [InvoiceController::class, 'createPayment']);
    });
});

Route::prefix('videos')->group(function () {
    Route::get('/', [VideoController::class, 'index']);
    Route::get('/show/{video}', [VideoController::class, 'show']);
    Route::get('/next/{video}', [VideoController::class, 'next']);
});

Route::prefix('area')->group(function () {
    Route::get('provinces', [AreaController::class, 'province']);
    Route::get('cities/{province}', [AreaController::class, 'cities']);
    Route::get('districts/{city}', [AreaController::class, 'districts']);
    Route::get('sub-districts/{district}', [AreaController::class, 'subDistricts']);
});

Route::prefix('pages')->group(function () {
    Route::get('/', [PageController::class, 'index']);
    Route::get('/{slug}', [PageController::class, 'view']);
});

Route::prefix('testimony')->group(function () {
    Route::get('/', [TestimonyController::class, 'index']);
});

Route::prefix('coupons')->group(function () {
    Route::get('unassigned-coupon', [CouponController::class, 'getUnassignedCoupons']);
    Route::get('user-assigned-coupon', [CouponController::class, 'getUserAssignedCoupons'])->middleware('auth:sanctum');
    Route::get('product-assigned-coupon/{product}', [CouponController::class, 'getProductAssignedCoupons']);
});

Route::prefix('webhook')->group(function () {
//    Route::prefix('midtrans')->group(function () {
//        Route::post('order', [MidtransController::class, 'order'])->name('webhook.midtrans.order');
//        Route::post('invoice', [MidtransController::class, 'invoice'])->name('webhook.midtrans.invoice');
//    });

    Route::prefix('deliveree')->group(function () {
        Route::post('/', [DelivereeController::class, 'index']);
    });

    Route::prefix('xendit')->group(function () {
        Route::post('/', [XenditController::class, 'handleInvoice']);
    });
});
