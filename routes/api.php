<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\WishlistController;
use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

/*Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});*/


Route::group(['prefix' => 'v1', 'as' => 'v1.'], function () {
  Route::post('/register', [AuthController::class, 'register']);
  Route::post('/login', [AuthController::class, 'login']);


  Route::post('/social-login', [AuthController::class, 'socialLogin']);

  Route::post('/submit-for-otp', [AuthController::class, 'submitForTtp']);
  Route::post('/resend-otp', [AuthController::class, 'ResendOtp']);
  Route::post('/submit-otp', [AuthController::class, 'submitOtp']);


  Route::get('/email/verify/{id}', [HomeController::class, 'verify'])->name('verificationapi.verify');
  Route::get('/email/resend', [HomeController::class, 'resend'])->name('verificationapi.resend');
  Route::post('/banners', [HomeController::class, 'banners']);

  Route::get('/get-section-products/{section}', [HomeController::class, 'getSectionProducts']);

  Route::get('/loving-products', [HomeController::class, 'lovingProducts']);
  Route::post('/buying-products', [HomeController::class, 'buyingProducts']);
  Route::get('/recent-products', [HomeController::class, 'recentProducts']);

  Route::get('/related-products/{item_id}', [HomeController::class, 'relatedProducts']);

  Route::get('/general', [HomeController::class, 'generalSettings']);
  Route::get('/faqs', [HomeController::class, 'faqPages']);
  Route::get('/contact-us', [HomeController::class, 'contactUs']);
  Route::get('/single-page/{slug}', [HomeController::class, 'singlePages']);

  Route::get('/get-products-page-cards/{card}', [HomeController::class, 'getProductPageCards']);
  Route::get('/get-homepage-cards', [HomeController::class, 'getHomepageCards']);
  Route::get('/get-homepage-featured-items-card', [HomeController::class, 'getHomepageFeaturedItemCard']);
  Route::get('/checkout-discounts', [CatalogController::class, 'checkoutDiscounts']);
  Route::get('/footer-brands', [HomeController::class, 'footerBanners']);
  Route::get('/payment-qr-codes', [HomeController::class, 'paymentQrCodes']);
  Route::get('/get-featured-categories', [HomeController::class, 'getFeaturedCategories']);


  Route::get('/categories', [CatalogController::class, 'categories']);
  Route::get('/banners', [CatalogController::class, 'banners']);
  Route::post('/category-products/{slug}', [CatalogController::class, 'categoryProducts']);
  Route::get('/product/{id}', [CatalogController::class, 'productDetails']);
  Route::get('/product-description/{id}', [CatalogController::class, 'productDescription']);
  Route::get('/product-seller-information/{id}', [CatalogController::class, 'productSellerInfo']);

  Route::get('/vendor-products/{id}', [CatalogController::class, 'sameVendorProducts']);
  Route::get('/products-bulk-prices/{id}', [CatalogController::class, 'productBulkPrices']);


  // searching products api
  Route::post('/search-process', [CatalogController::class, 'searchProcess']);
  Route::post('/get-search-result/{searchKey}', [CatalogController::class, 'getSearchResult']);
  Route::post('/get-picture-result/{search_id}', [CatalogController::class, 'getPictureSearchResult']);
  Route::post('/search-picture', [CatalogController::class, 'searchPicture']);




  Route::group(['middleware' => ['auth:sanctum', 'verified']], function () {
    Route::post('/me', [AuthController::class, 'me']);
    Route::post('/get-wishlist', [WishlistController::class, 'getCustomerWishList']);

    Route::post('/confirm-order', [OrderController::class, 'confirmOrders']);
    Route::post('/payment-confirm', [OrderController::class, 'confirmOrderPayment']);

    Route::get('/orders', [OrderController::class, 'orders']);
    Route::post('/order/{id}', [OrderController::class, 'orderDetails']);
    Route::post('/invoices', [OrderController::class, 'invoices']);
    Route::post('/invoice/{id}', [OrderController::class, 'invoiceDetails']);

    Route::post('/add-to-wishlist', [WishlistController::class, 'AddToWishList']);
    Route::post('/remove-wishlist', [WishlistController::class, 'removeCustomerWishList']);

    Route::get('/address', [AuthController::class, 'AllAddress']);
    Route::post('/store-new-address', [AuthController::class, 'StoreNewAddress']);
    Route::post('/delete-address', [AuthController::class, 'deleteAddress']);

    Route::post('/auth/logout', [AuthController::class, 'logout']);
  });
});
