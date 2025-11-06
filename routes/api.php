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
use App\Http\Controllers\Api\Disclaimer\DisclaimerController;
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
        Route::post('login', [AuthController::class, 'login'])->name('auth.web.login')->middleware('guest');
        Route::post('register', [AuthController::class, 'register'])->name('auth.web.register')->middleware('guest');
        Route::post('logout', [AuthController::class, 'logout'])->name('auth.web.logout')->middleware('auth:sanctum');

        Route::prefix('google')->middleware('guest')->group(function () {
            Route::get('/', [AuthController::class, 'google'])->name('auth.web.google')->middleware('guest');
            Route::get('callback', [AuthController::class, 'googleCallback'])->name('auth.web.google.callback')->middleware('guest');
        });
    });

    Route::prefix('mobile')->group(function () {
        Route::post('login', [MobileAuthController::class, 'login'])->name('auth.mobile.login')->middleware('guest');
        Route::post('register', [MobileAuthController::class, 'register'])->name('auth.mobile.register')->middleware('guest');
        Route::post('google', [MobileAuthController::class, 'google'])->name('auth.mobile.google')->middleware('guest');
        Route::post('logout', [MobileAuthController::class, 'logout'])->name('auth.mobile.logout')->middleware('auth:sanctum');
    });

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])->middleware('guest');
    Route::post('reset-password', [NewPasswordController::class, 'store'])->middleware('guest');
    Route::post('password', [PasswordController::class, 'update'])->name('auth.password.update')->middleware('auth:sanctum');
    Route::get('email-verification/{id}/{hash}', [EmailVerificationController::class, 'index'])->name('auth.email-verification')->middleware('auth:sanctum');
});

Route::prefix('general')->group(function () {
    Route::get('available-banks', [GeneralApiController::class, 'banks']);
    Route::get('floating-button', [GeneralApiController::class, 'floatingButton']);
    Route::prefix('wholesale-form')->group(function () {
        Route::get('budget', [GeneralApiController::class, 'wholesaleFormBudget']);
        Route::post('send', [GeneralApiController::class, 'storeWholesaleForm'])->name('general.wholesale.send');
    });
    Route::get('reviews', [GeneralApiController::class, 'reviews']);
});

Route::prefix('user')->middleware('auth:sanctum')->group(function () {
    Route::get('/', function (Request $request) {
        $user = $request->user();
        return new UserResource($user);
    })->name('user.profile.me');

    Route::prefix('profile')->group(function () {
        Route::put('/update', [ProfileController::class, 'update'])->name('user.profile.update');
        Route::post('/profile-picture', [ProfileController::class, 'changeProfilePicture'])->name('user.profile.picture');
        Route::post('/delete-account', [ProfileController::class, 'deleteAccount'])->name('user.profile.delete');
    })->middleware('auth:sanctum');

    Route::prefix('address')->group(function () {
        Route::get('/', [AddressController::class, 'getAddresses'])->name('user.address.index');
        Route::post('/create', [AddressController::class, 'createAddress'])->name('user.address.create');
        Route::put('/edit', [AddressController::class, 'editAddress'])->name('user.address.edit');
        Route::get('/detail/{address}', [AddressController::class, 'getAddressDetail'])->name('user.address.detail');
        Route::delete('/delete/{address}', [AddressController::class, 'deleteAddress'])->name('user.address.delete');
        Route::put('/set-primary/{address}', [AddressController::class, 'setPrimary'])->name('user.address.primary');
    });

    Route::prefix('banks')->group(function () {
        Route::get('/', [BankController::class, 'index'])->name('user.banks.index');
        Route::post('/create', [BankController::class, 'create'])->name('user.banks.create');
        Route::put('/update/{userBank}', [BankController::class, 'update'])->name('user.banks.update');
        Route::delete('/delete/{userBank}', [BankController::class, 'delete'])->name('user.banks.delete');
    });
});

Route::prefix('banners')->group(function () {
    Route::get('/', [BannerController::class, 'index'])->name('banners.index');
    Route::get('home', [BannerController::class, 'home'])->name('banners.home');
    Route::get('product', [BannerController::class, 'product'])->name('banners.product');
});

Route::prefix('products')->group(function () {
    Route::get('/', [ProductController::class, 'getProducts'])->name('products.index');
    Route::post('/create', [ProductController::class, 'store'])->name('products.create')->middleware('api.key');
    Route::post('/create-batch', [ProductController::class, 'storeBatch'])->name('products.create-batch')->middleware('api.key');
    Route::get('/detail/{slug}', [ProductController::class, 'detail'])->name('products.detail');
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
    Route::get('/', [CartController::class, 'getCart'])->name('carts.index');
    Route::post('add', [CartController::class, 'add'])->name('carts.add');
    Route::delete('remove-item/{product}', [CartController::class, 'removeItem'])->name('carts.remove-item');
    Route::patch('set-selected-item', [CartController::class, 'setSelectedItem'])->name('carts.selected-item');
    Route::get('search-friend', [CartController::class, 'searchFriend'])->name('carts.search-friend');
    Route::patch('set-address', [CartController::class, 'setAddress'])->name('carts.set-address');
    Route::patch('set-shipping-method', [CartController::class, 'setShippingMethod'])->name('carts.set-shipping-method');
    Route::get('shipping-cost', [CartController::class, 'getShippingCost'])->name('carts.shipping-cost');
    Route::post('apply-coupon', [CartController::class, 'applyCoupon'])->name('carts.apply-coupon');
    Route::delete('clear-coupon', [CartController::class, 'clearCoupon'])->name('carts.clear-coupon');
    Route::post('place-order', [CartController::class, 'placeOrder'])->name('carts.place-order');
    Route::get('get-pickup-info', [CartController::class, 'getPickupInfo'])->name('carts.get-pickup-info');
});

Route::prefix('orders')->middleware('auth:sanctum')->group(function () {
    Route::get('get-orders', [OrderController::class, 'getOrders'])->name('orders.index');
    Route::get('get-detail/{order}', [OrderController::class, 'getDetailOrder'])->name('orders.detail');
    Route::post('review/{order}', [OrderController::class, 'review'])->name('orders.review');
    Route::put('complete/{order}', [OrderController::class, 'complete'])->name('orders.complete');
    Route::get('tracking/{order}', [OrderController::class, 'tracking'])->name('orders.tracking');
    Route::get('disclaimers/active', [DisclaimerController::class, 'active']);

    Route::prefix('invoice')->group(function () {
        Route::get('get-payment-methods', [InvoiceController::class, 'getPaymentMethod'])->name('orders.invoice.payment-method');
        Route::get('get-invoices-by-order/{order}', [InvoiceController::class, 'getInvoicesByOrder']);
        Route::get('get-my-invoice-by-order/{order}', [InvoiceController::class, 'getMyInvoiceByOrder']);
        Route::patch('set-invoice-amount', [InvoiceController::class, 'setInvoiceAmount'])->name('orders.invoice.invoice-amount');
        Route::post('create-payment', [InvoiceController::class, 'createPayment'])->name('orders.invoice.create-payment');
    });
});

Route::prefix('videos')->group(function () {
    Route::get('/', [VideoController::class, 'index'])->name('videos.index');
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
    Route::get('user-assigned-coupon', [CouponController::class, 'getUserAssignedCoupons'])->name('coupons.user-assigned')->middleware('auth:sanctum');
    Route::get('product-assigned-coupon/{product}', [CouponController::class, 'getProductAssignedCoupons']);
});

Route::prefix('webhook')->group(function () {
    //    Route::prefix('midtrans')->group(function () {
    //        Route::post('order', [MidtransController::class, 'order'])->name('webhook.midtrans.order');
    //        Route::post('invoice', [MidtransController::class, 'invoice'])->name('webhook.midtrans.invoice');
    //    });

    // webhook deliveree & forwarder gabung
    Route::prefix('deliveree')->group(function () {
        Route::post('/', [DelivereeController::class, 'index']);
    });

    Route::prefix('xendit')->group(function () {
        Route::post('/', [XenditController::class, 'handleInvoice']);
    });
});

Route::prefix('wms')->middleware('api.key')->group(function () {
    Route::post('place-order-by-wms', [CartController::class, 'placeOrderInWMS'])->name('wms.place-order');
});
