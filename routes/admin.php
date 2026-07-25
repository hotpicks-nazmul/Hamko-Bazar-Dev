<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\OrderController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ThemeController;
use App\Http\Controllers\Admin\PaymentController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\JakatController;
use App\Http\Controllers\Admin\ChatController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SliderController;
use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\HeroController;
use App\Http\Controllers\Admin\HomePageController;
use App\Http\Controllers\Admin\CustomerGroupController;
use App\Http\Controllers\Admin\FormBuilderController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application.
|
*/

// Admin Dashboard
Route::middleware('permission:dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/sales-chart', [DashboardController::class, 'salesChart'])->name('sales-chart');
});

// Analytics - Separate permission
Route::middleware('permission:analytics')->group(function () {
    Route::get('/analytics', [DashboardController::class, 'analytics'])->name('analytics');
    Route::get('/analytics/export', [DashboardController::class, 'exportAnalytics'])->name('analytics.export');
});

// Jakat Calculator
Route::middleware('permission:reports')->group(function () {
Route::get('/jakat', [JakatController::class, 'index'])->name('jakat.index');
Route::post('/jakat/calculate', [JakatController::class, 'calculate'])->name('jakat.calculate');
Route::post('/jakat/prices', [JakatController::class, 'updatePrices'])->name('jakat.prices');
});

// Products Management - Custom routes (must be before resource routes to avoid conflicts)
Route::middleware('permission:products')->group(function () {
Route::get('/products/in-house', [ProductController::class, 'index'])->name('products.in-house');
Route::get('/products/in-house/export', [ProductController::class, 'exportInHouse'])->name('products.export-in-house');
Route::post('/products/in-house/bulk-action', [ProductController::class, 'inHouseBulkAction'])->name('products.in-house.bulk-action');
Route::post('/products/bulk-stock-update', [ProductController::class, 'bulkStockUpdate'])->name('products.bulk-stock-update');
Route::get('/products/low-stock-alerts', [ProductController::class, 'lowStockAlerts'])->name('products.low-stock-alerts');
Route::post('/products/bulk-action', [ProductController::class, 'bulkAction'])->name('products.bulk-action');
Route::get('/products-export', [ProductController::class, 'export'])->name('products.export');
Route::get('/products/{product}/duplicate', [ProductController::class, 'duplicate'])->name('products.duplicate');
Route::post('/products/{product}/images', [ProductController::class, 'uploadImages'])->name('products.images.upload');
Route::delete('/products/{product}/images/{image}', [ProductController::class, 'deleteImage'])->name('products.images.delete');
Route::delete('/products/{product}/featured-image', [ProductController::class, 'deleteFeaturedImage'])->name('products.featured-image.delete');
Route::delete('/products/{product}/attributes/{attrId}/{valueId}/image', [ProductController::class, 'deleteAttributeImage'])->name('products.attributes.image.delete');
Route::delete('/products/{product}/colors/{colorId}/image', [ProductController::class, 'deleteColorImage'])->name('products.colors.image.delete');
Route::delete('/products/{product}/colors/{colorId}/values/{valueId}/image', [ProductController::class, 'deleteColorValueImage'])->name('products.colors.values.image.delete');
Route::post('/products/{product}/toggle-status', [ProductController::class, 'toggleStatus'])->name('products.toggle-status');
Route::post('/products/{product}/toggle-featured', [ProductController::class, 'toggleFeatured'])->name('products.toggle-featured');
Route::post('/products/{product}/quick-update', [ProductController::class, 'quickUpdate'])->name('products.quick-update');
Route::post('/products/{product}/update-stock', [ProductController::class, 'updateStock'])->name('products.update-stock');
Route::post('/products/{product}/update-low-stock-threshold', [ProductController::class, 'updateLowStockThreshold'])->name('products.update-low-stock-threshold');

// Bulk Import, Export, Discount Routes (must be before resource routes)
Route::get('/products/bulk-import', [ProductController::class, 'bulkImport'])->name('products.bulk-import');
Route::post('/products/bulk-import', [ProductController::class, 'processBulkImport'])->name('products.bulk-import.process');
Route::get('/products/bulk-export', [ProductController::class, 'bulkExport'])->name('products.bulk-export');
Route::get('/products/bulk-export/download', [ProductController::class, 'exportProducts'])->name('products.bulk-export.download');
Route::get('/products/bulk-discount', [ProductController::class, 'bulkDiscount'])->name('products.bulk-discount');
Route::post('/products/bulk-discount', [ProductController::class, 'applyBulkDiscount'])->name('products.bulk-discount.apply');
Route::post('/products/bulk-discount/remove', [ProductController::class, 'removeBulkDiscount'])->name('products.bulk-discount.remove');
Route::get('/products/bulk-discount/products', [ProductController::class, 'getProductsForSelection'])->name('products.bulk-discount.products');
Route::put('/products/{product}/discount', [ProductController::class, 'updateProductDiscount'])->name('products.discount.update');
Route::delete('/products/{product}/discount', [ProductController::class, 'removeProductDiscount'])->name('products.discount.remove');

// Related Products Management
Route::get('/products/{product}/related', [ProductController::class, 'relatedProducts'])->name('products.related');
Route::get('/products/{product}/related/search', [ProductController::class, 'searchRelatedProducts'])->name('products.related.search');
Route::post('/products/{product}/related', [ProductController::class, 'addRelatedProducts'])->name('products.related.add');
Route::delete('/products/{product}/related/{relatedId}', [ProductController::class, 'removeRelatedProduct'])->name('products.related.remove');
Route::post('/products/{product}/related/order', [ProductController::class, 'updateRelatedProductsOrder'])->name('products.related.order');
Route::post('/products/{product}/related/bulk-remove', [ProductController::class, 'bulkRemoveRelatedProducts'])->name('products.related.bulk-remove');
Route::get('/products/{product}/related/auto-suggest', [ProductController::class, 'autoSuggestRelatedProducts'])->name('products.related.auto-suggest');

}); // end permission:products group for custom routes

// Digital Products Management (must be before resource routes)
Route::middleware('permission:products')->group(function () {
Route::prefix('products/digital')->name('products.digital.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DigitalProductController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\DigitalProductController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\DigitalProductController::class, 'store'])->name('store');
    Route::get('/export', [\App\Http\Controllers\Admin\DigitalProductController::class, 'export'])->name('export');
    Route::post('/bulk-action', [\App\Http\Controllers\Admin\DigitalProductController::class, 'bulkAction'])->name('bulk-action');
    Route::get('/{id}/edit', [\App\Http\Controllers\Admin\DigitalProductController::class, 'edit'])->name('edit');
    Route::put('/{id}', [\App\Http\Controllers\Admin\DigitalProductController::class, 'update'])->name('update');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\DigitalProductController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/toggle-status', [\App\Http\Controllers\Admin\DigitalProductController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{id}/toggle-featured', [\App\Http\Controllers\Admin\DigitalProductController::class, 'toggleFeatured'])->name('toggle-featured');
    
    // License Key Management
    Route::post('/{id}/generate-license-keys', [\App\Http\Controllers\Admin\DigitalProductController::class, 'generateLicenseKeys'])->name('generate-license-keys');
    Route::get('/{id}/license-keys', [\App\Http\Controllers\Admin\DigitalProductController::class, 'getLicenseKeys'])->name('license-keys');
    Route::get('/{id}/license-keys/export', [\App\Http\Controllers\Admin\DigitalProductController::class, 'exportLicenseKeys'])->name('export-license-keys');
    Route::delete('/{id}/license-keys/{keyId}', [\App\Http\Controllers\Admin\DigitalProductController::class, 'deleteLicenseKey'])->name('delete-license-key');
    Route::post('/{id}/license-keys/{keyId}/disable', [\App\Http\Controllers\Admin\DigitalProductController::class, 'disableLicenseKey'])->name('disable-license-key');
    
    // Additional Files Management
    Route::delete('/{id}/additional-files/{index}', [\App\Http\Controllers\Admin\DigitalProductController::class, 'deleteAdditionalFile'])->name('delete-additional-file');
    
    // Download Statistics
    Route::get('/{id}/download-stats', [\App\Http\Controllers\Admin\DigitalProductController::class, 'downloadStats'])->name('download-stats');
});

// Digital Categories Management
Route::resource('digital-categories', \App\Http\Controllers\Admin\DigitalCategoryController::class);
Route::post('/digital-categories/{digitalCategory}/toggle-status', [\App\Http\Controllers\Admin\DigitalCategoryController::class, 'toggleStatus'])->name('digital-categories.toggle-status');
Route::post('/digital-categories/update-order', [\App\Http\Controllers\Admin\DigitalCategoryController::class, 'updateOrder'])->name('digital-categories.update-order');
Route::get('/digital-categories/api/categories', [\App\Http\Controllers\Admin\DigitalCategoryController::class, 'getCategories'])->name('digital-categories.api.categories');
Route::delete('/digital-categories/{digitalCategory}/delete-image', [\App\Http\Controllers\Admin\DigitalCategoryController::class, 'deleteImage'])->name('digital-categories.delete-image');
}); // end permission:products group for digital products

// Products Resource Routes
Route::resource('products', ProductController::class)->middleware('permission:products');

// Categories Management
Route::middleware('permission:products')->group(function () {
Route::resource('categories', CategoryController::class);
Route::post('/categories/bulk-action', [CategoryController::class, 'bulkAction'])->name('categories.bulk-action');
Route::post('/categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
Route::get('/categories/tree', [CategoryController::class, 'tree'])->name('categories.tree');
Route::get('/categories-export', [CategoryController::class, 'export'])->name('categories.export');
Route::post('/categories/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('categories.toggle-status');
Route::post('/categories/{category}/toggle-menu', [CategoryController::class, 'toggleMenu'])->name('categories.toggle-menu');
Route::post('/categories/{category}/toggle-homepage', [CategoryController::class, 'toggleHomepage'])->name('categories.toggle-homepage');
Route::get('/categories/{category}/products', [CategoryController::class, 'getProducts'])->name('categories.products');
Route::post('/categories/{category}/move-products', [CategoryController::class, 'moveProducts'])->name('categories.move-products');
Route::get('/categories-select-options', [CategoryController::class, 'getSelectOptions'])->name('categories.select-options');
}); // end permission:products group for categories

Route::middleware('permission:orders')->group(function () {
 // Inhouse Orders - MUST be before any wildcard routes
Route::get('/orders/in-house', [OrderController::class, 'inHouse'])->name('orders.in-house');
Route::get('/orders/in-house/create', [OrderController::class, 'create'])->name('orders.in-house.create');
Route::post('/orders/in-house', [OrderController::class, 'store'])->name('orders.in-house.store');
Route::get('/orders/in-house/{order}', [OrderController::class, 'inHouseShow'])->name('orders.in-house.show');
Route::post('/orders/in-house/{order}/add-item', [OrderController::class, 'inHouseAddItem'])->name('orders.in-house.add-item');
Route::post('/orders/in-house/{order}/add-items', [OrderController::class, 'inHouseAddItems'])->name('orders.in-house.add-items');
Route::put('/orders/in-house/{order}/items/{item}', [OrderController::class, 'inHouseUpdateItem'])->name('orders.in-house.update-item');
Route::delete('/orders/in-house/{order}/items/{item}', [OrderController::class, 'inHouseRemoveItem'])->name('orders.in-house.remove-item');
Route::put('/orders/in-house/{order}/warehouse', [OrderController::class, 'inHouseChangeWarehouse'])->name('orders.in-house.change-warehouse');
Route::get('/orders/search-products', [OrderController::class, 'searchProducts'])->name('orders.search-products');
Route::get('/orders/product-detail/{product}', [OrderController::class, 'getProductDetail'])->name('orders.product-detail');
Route::get('/orders/search-customers', [OrderController::class, 'searchCustomers'])->name('orders.search-customers');

// Seller Orders - MUST be before resource routes
Route::get('/orders/seller', [OrderController::class, 'seller'])->name('orders.seller');
Route::get('/orders/seller/{order}', [OrderController::class, 'sellerShow'])->name('orders.seller.show');

// Pickup Point Orders - MUST be before resource routes
Route::get('/orders/pickup-point', [OrderController::class, 'pickupPoint'])->name('orders.pickup-point');
Route::get('/orders/pickup-point/{order}', [OrderController::class, 'pickupPointShow'])->name('orders.pickup-point.show');
Route::post('/orders/pickup-point/{order}/picked-up', [OrderController::class, 'markAsPickedUp'])->name('orders.pickup-point.picked-up');
});

Route::middleware('permission:delivery')->group(function () {
// Pickup Points Management (Delivery Section)
Route::resource('pickup-points', \App\Http\Controllers\Admin\PickupPointController::class);
Route::post('/pickup-points/{pickupPoint}/toggle-status', [\App\Http\Controllers\Admin\PickupPointController::class, 'toggleStatus'])->name('pickup-points.toggle-status');
Route::get('/api/pickup-points', [\App\Http\Controllers\Admin\PickupPointController::class, 'getPickupPoints'])->name('api.pickup-points');
});

// Orders Management - Resource route MUST be LAST
Route::resource('orders', OrderController::class)->only(['index', 'show', 'update'])->middleware('permission:orders');

Route::middleware('permission:orders')->group(function () {
Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
Route::post('/orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.payment-status');
Route::get('/orders/{order}/invoice', [OrderController::class, 'invoice'])->name('orders.invoice');
Route::post('/orders/{order}/ship', [OrderController::class, 'ship'])->name('orders.ship');
Route::post('/orders/bulk-status', [OrderController::class, 'bulkUpdateStatus'])->name('orders.bulk-status');
});

Route::middleware('permission:customers')->group(function () {

// Customer Groups - Must be defined BEFORE the customers resource route
Route::prefix('customers/groups')->name('customers.groups.')->group(function () {
    Route::get('/', [CustomerGroupController::class, 'index'])->name('index');
    Route::get('/create', [CustomerGroupController::class, 'create'])->name('create');
    Route::post('/', [CustomerGroupController::class, 'store'])->name('store');
    Route::get('/{customerGroup}/edit', [CustomerGroupController::class, 'edit'])->name('edit');
    Route::put('/{customerGroup}', [CustomerGroupController::class, 'update'])->name('update');
    Route::delete('/{customerGroup}', [CustomerGroupController::class, 'destroy'])->name('destroy');
    Route::post('/{customerGroup}/toggle-status', [CustomerGroupController::class, 'toggleStatus'])->name('toggle-status');
});

// Customer Segmentation - MUST be defined BEFORE customers resource route
Route::prefix('customers/segmentation')->name('customers.segmentation.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\CustomerSegmentationController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\CustomerSegmentationController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\CustomerSegmentationController::class, 'store'])->name('store');
    // General preview route - must be before {segment} routes to avoid being matched as segment ID
    Route::get('/preview', [\App\Http\Controllers\Admin\CustomerSegmentationController::class, 'preview'])->name('preview');
    // Segment-specific routes - {segment} parameter routes must come after specific routes
    Route::get('/{segment}/edit', [\App\Http\Controllers\Admin\CustomerSegmentationController::class, 'edit'])->name('edit');
    Route::put('/{segment}', [\App\Http\Controllers\Admin\CustomerSegmentationController::class, 'update'])->name('update');
    Route::delete('/{segment}', [\App\Http\Controllers\Admin\CustomerSegmentationController::class, 'destroy'])->name('destroy');
    Route::post('/{segment}/toggle-status', [\App\Http\Controllers\Admin\CustomerSegmentationController::class, 'toggleStatus'])->name('toggle-status');
    Route::get('/{segment}/preview', [\App\Http\Controllers\Admin\CustomerSegmentationController::class, 'preview'])->name('preview.single');
    Route::get('/{segment}/export', [\App\Http\Controllers\Admin\CustomerSegmentationController::class, 'export'])->name('export');
    Route::get('/{segment}', [\App\Http\Controllers\Admin\CustomerSegmentationController::class, 'show'])->name('show');
});

// Loyalty Points routes - MUST be defined BEFORE the resource route to avoid 404 errors
Route::prefix('customers/loyalty')->name('customers.loyalty.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'index'])->name('index');
    Route::get('/settings', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'settings'])->name('settings');
    Route::post('/settings', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'updateSettings'])->name('settings.update');
    Route::get('/export', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'export'])->name('export');
    Route::get('/{customerId}', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'show'])->name('show');
    Route::get('/{customerId}/transactions', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'transactions'])->name('transactions');
    Route::post('/add-points', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'addPoints'])->name('addPoints');
    Route::post('/deduct-points', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'deductPoints'])->name('deductPoints');
    Route::post('/rewards', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'createReward'])->name('createReward');
    Route::put('/rewards/{rewardId}', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'updateReward'])->name('updateReward');
    Route::delete('/rewards/{rewardId}', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'deleteReward'])->name('deleteReward');
    Route::post('/rewards/{rewardId}/toggle', [\App\Http\Controllers\Admin\LoyaltyPointsController::class, 'toggleReward'])->name('toggleReward');
});

// Customer Membership Plans - MUST be defined BEFORE the customers resource route to avoid 404 errors
Route::prefix('customers/membership')->name('customers.membership.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\MembershipPlanController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\MembershipPlanController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\MembershipPlanController::class, 'store'])->name('store');
    Route::get('/{membershipPlan}/edit', [\App\Http\Controllers\Admin\MembershipPlanController::class, 'edit'])->name('edit');
    Route::put('/{membershipPlan}', [\App\Http\Controllers\Admin\MembershipPlanController::class, 'update'])->name('update');
    Route::delete('/{membershipPlan}', [\App\Http\Controllers\Admin\MembershipPlanController::class, 'destroy'])->name('destroy');
    Route::post('/{membershipPlan}/toggle-status', [\App\Http\Controllers\Admin\MembershipPlanController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{membershipPlan}/toggle-featured', [\App\Http\Controllers\Admin\MembershipPlanController::class, 'toggleFeatured'])->name('toggle-featured');
});

// Customer Wallet - MUST be defined BEFORE customers resource route to avoid 404 errors
Route::prefix('customers/wallet')->name('customers.wallet.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\CustomerWalletController::class, 'index'])->name('index');
    Route::get('/transactions', [\App\Http\Controllers\Admin\CustomerWalletController::class, 'transactions'])->name('transactions');
    Route::get('/{customerId}', [\App\Http\Controllers\Admin\CustomerWalletController::class, 'show'])->name('show');
    Route::post('/add-balance', [\App\Http\Controllers\Admin\CustomerWalletController::class, 'addBalance'])->name('add-balance');
    Route::post('/deduct-balance', [\App\Http\Controllers\Admin\CustomerWalletController::class, 'deductBalance'])->name('deduct-balance');
    Route::get('/search-customers', [\App\Http\Controllers\Admin\CustomerWalletController::class, 'searchCustomers'])->name('search-customers');
});

// Customers Management
Route::resource('customers', CustomerController::class)->only(['index', 'show', 'update', 'destroy']);
Route::get('/customers/{customer}/orders', [CustomerController::class, 'orders'])->name('customers.orders');
Route::post('/customers/{customer}/login-as', [CustomerController::class, 'loginAs'])->name('customers.login-as');
Route::post('/customers/{customer}/toggle-status', [CustomerController::class, 'toggleStatus'])->name('customers.toggle-status');
Route::post('/customers/bulk-action', [CustomerController::class, 'bulkAction'])->name('customers.bulk-action');

}); // end permission:customers group

// Coupons Management
Route::middleware('permission:marketing')->group(function () {
    Route::post('/coupons/{coupon}/toggle', [CouponController::class, 'toggle'])->name('coupons.toggle');
    Route::resource('coupons', CouponController::class);
});

// Reviews Management
Route::middleware('permission:products')->group(function () {
    Route::resource('reviews', ReviewController::class)->only(['index', 'update', 'destroy']);
    Route::post('/reviews/{review}/approve', [ReviewController::class, 'approve'])->name('reviews.approve');
    Route::post('/reviews/{review}/reject', [ReviewController::class, 'reject'])->name('reviews.reject');
    Route::post('/reviews/bulk-action', [ReviewController::class, 'bulkAction'])->name('reviews.bulk-action');
});

// Wishlist Management
Route::middleware('permission:products')->group(function () {
Route::prefix('wishlists')->name('wishlists.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\WishlistController::class, 'index'])->name('index');
    Route::delete('/delete', [\App\Http\Controllers\Admin\WishlistController::class, 'destroy'])->name('destroy');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\WishlistController::class, 'destroySingle'])->name('destroy-single');
    Route::post('/bulk-action', [\App\Http\Controllers\Admin\WishlistController::class, 'bulkAction'])->name('bulk-action');
    Route::get('/export', [\App\Http\Controllers\Admin\WishlistController::class, 'export'])->name('export');
    Route::get('/product/{productId}', [\App\Http\Controllers\Admin\WishlistController::class, 'productWishlist'])->name('product');
    Route::get('/user/{userId}', [\App\Http\Controllers\Admin\WishlistController::class, 'userWishlist'])->name('user');
});
}); // end permission:products group for wishlists

// Pages Management
Route::middleware('permission:content')->group(function () {
    Route::post('/pages/{page}/toggle', [PageController::class, 'toggle'])->name('pages.toggle');
    Route::resource('pages', PageController::class);
});

// Sliders Management
Route::middleware('permission:appearance')->group(function () {
    Route::post('/sliders/reorder', [SliderController::class, 'reorder'])->name('sliders.reorder');
    Route::resource('sliders', SliderController::class);
});

// Banners Management
Route::middleware('permission:appearance')->group(function () {
    Route::post('/banners/{banner}/toggle', [BannerController::class, 'toggle'])->name('banners.toggle');
    Route::post('/banners/reorder', [BannerController::class, 'reorder'])->name('banners.reorder');
    Route::post('/banners/bulk-action', [BannerController::class, 'bulkAction'])->name('banners.bulkAction');
    Route::resource('banners', BannerController::class);
});

// Hero Section Settings
Route::middleware('permission:appearance')->group(function () {
    Route::prefix('hero')->name('hero.')->group(function () {
        Route::get('/', [HeroController::class, 'index'])->name('index');
        Route::put('/', [HeroController::class, 'update'])->name('update');
        Route::post('/type', [HeroController::class, 'updateType'])->name('update-type');
    });
});

// Home Page Settings
Route::middleware('permission:appearance')->group(function () {
    Route::prefix('homepage')->name('homepage.')->group(function () {
        Route::get('/', [HomePageController::class, 'index'])->name('index');
        Route::put('/', [HomePageController::class, 'update'])->name('update');
        Route::post('/section-order', [HomePageController::class, 'updateSectionOrder'])->name('section-order');
    });
});

// Theme Management
Route::middleware('permission:appearance')->group(function () {
    Route::prefix('themes')->name('themes.')->group(function () {
        Route::get('/', [ThemeController::class, 'index'])->name('index');
        Route::post('/activate', [ThemeController::class, 'activate'])->name('activate');
        Route::get('/settings', [ThemeController::class, 'settings'])->name('settings');
        Route::post('/settings', [ThemeController::class, 'updateSettings'])->name('settings.update');
        Route::post('/reset', [ThemeController::class, 'reset'])->name('reset');
    });
});

// Media Management
Route::middleware('permission:media')->group(function () {
    Route::get('/media', [\App\Http\Controllers\Admin\MediaController::class, 'index'])->name('media.index');
    Route::post('/media/upload', [\App\Http\Controllers\Admin\MediaController::class, 'upload'])->name('media.upload');
    Route::delete('/media', [\App\Http\Controllers\Admin\MediaController::class, 'destroy'])->name('media.destroy');
    Route::post('/media/bulk-delete', [\App\Http\Controllers\Admin\MediaController::class, 'bulkDelete'])->name('media.bulk-delete');
    Route::get('/media/show', [\App\Http\Controllers\Admin\MediaController::class, 'show'])->name('media.show');
});

// Blog Management
Route::middleware('permission:content')->group(function () {
    Route::post('/blogs/{blog}/toggle', [\App\Http\Controllers\Admin\BlogController::class, 'toggle'])->name('blogs.toggle');
    Route::resource('blogs', \App\Http\Controllers\Admin\BlogController::class);
    Route::prefix('blog-settings')->name('blog-settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BlogController::class, 'settings'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\BlogController::class, 'updateSettings'])->name('update');
    });
});

// Settings Management
Route::middleware('permission:settings')->group(function () {
    Route::prefix('settings')->name('settings.')->group(function () {
        Route::get('/', [SettingController::class, 'general'])->name('index');
        Route::get('/general', [SettingController::class, 'general'])->name('general');
        Route::post('/general', [SettingController::class, 'updateGeneral'])->name('general.update');
        Route::get('/store', [SettingController::class, 'store'])->name('store');
        Route::post('/store', [SettingController::class, 'updateStore'])->name('store.update');
        Route::get('/email', [SettingController::class, 'email'])->name('email');
        Route::post('/email', [SettingController::class, 'updateEmail'])->name('email.update');
        Route::post('/email/test', [SettingController::class, 'testEmail'])->name('email.test');
        Route::get('/seo', [SettingController::class, 'seo'])->name('seo');
        Route::post('/seo', [SettingController::class, 'updateSeo'])->name('seo.update');
        Route::get('/social', [SettingController::class, 'social'])->name('social');
        Route::post('/social', [SettingController::class, 'updateSocial'])->name('social.update');
        Route::get('/social-login', [SettingController::class, 'socialLogin'])->name('social-login');
        Route::put('/social-login', [SettingController::class, 'updateSocialLogin'])->name('social-login.update');
        Route::get('/whatsapp', [SettingController::class, 'whatsapp'])->name('whatsapp');
        Route::post('/whatsapp', [SettingController::class, 'updateWhatsapp'])->name('whatsapp.update');
        Route::get('/footer', [SettingController::class, 'footer'])->name('footer');
        Route::post('/footer', [SettingController::class, 'updateFooter'])->name('footer.update');
        Route::get('/maintenance', [SettingController::class, 'maintenance'])->name('maintenance');
        Route::post('/maintenance', [SettingController::class, 'updateMaintenance'])->name('maintenance.update');

        // Email Templates
        Route::prefix('email-templates')->name('email-templates.')->group(function () {
            Route::get('/', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'index'])->name('index');
            Route::get('/{emailTemplate}/edit', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'edit'])->name('edit');
            Route::put('/{emailTemplate}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'update'])->name('update');
            Route::patch('/{emailTemplate}/toggle-status', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'toggleStatus'])->name('toggle-status');
            Route::post('/{emailTemplate}/preview', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'preview'])->name('preview');
        });
    });
});

// Payment Settings
Route::middleware('permission:settings')->group(function () {
    Route::prefix('payment')->name('payment.')->group(function () {
    // List all payment methods
    Route::get('/', [PaymentController::class, 'index'])->name('index');
    
    // Create new payment method
    Route::post('/', [PaymentController::class, 'store'])->name('store');
    
    // Update payment method details
    Route::put('/{id}', [PaymentController::class, 'update'])->name('update');
    
    // Delete payment method
    Route::delete('/{id}', [PaymentController::class, 'destroy'])->name('destroy');
    
    // Toggle payment method status
    Route::put('/toggle/{id}', [PaymentController::class, 'toggle'])->name('toggle');
    
    // Set as default payment method
    Route::put('/set-default/{id}', [PaymentController::class, 'setDefault'])->name('set-default');
    
    // Update credentials for specific gateway (using slug)
    Route::post('/credentials/{slug}', [PaymentController::class, 'updateCredentials'])->name('credentials');
    
    // Update order/sort
    Route::post('/order', [PaymentController::class, 'updateOrder'])->name('order');
});
    Route::get('/payment-gateways', [PaymentController::class, 'index'])->name('payment-gateways.index');
}); // end permission:settings group for payment

// SEO Management
Route::middleware('permission:settings')->group(function () {
    Route::prefix('seo')->name('seo.')->group(function () {
        Route::get('/', [SeoController::class, 'index'])->name('index');
        Route::post('/meta', [SeoController::class, 'updateMeta'])->name('meta.update');
        Route::post('/sitemap/generate', [SeoController::class, 'generateSitemap'])->name('sitemap.generate');
        Route::get('/redirects', [SeoController::class, 'redirects'])->name('redirects');
        Route::post('/redirects', [SeoController::class, 'storeRedirect'])->name('redirects.store');
        Route::delete('/redirects/{id}', [SeoController::class, 'deleteRedirect'])->name('redirects.destroy');
    });
});

// Chat Management
Route::middleware('permission:support')->group(function () {
    Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    
    // AJAX Routes - specific routes before parameterized routes to avoid 404
    Route::get('/conversations', [ChatController::class, 'conversations'])->name('conversations');
    Route::get('/conversation/{id}', [ChatController::class, 'conversation'])->name('conversation');
    Route::post('/send', [ChatController::class, 'send'])->name('send')->middleware('throttle:60,1');
    Route::post('/typing', [ChatController::class, 'typing'])->name('typing')->middleware('throttle:30,1');
    Route::get('/check-typing', [ChatController::class, 'checkTyping'])->name('check-typing')->middleware('throttle:60,1');
    Route::post('/conversation/{id}/status', [ChatController::class, 'updateStatus'])->name('update-status');
    Route::post('/conversation/{id}/close', [ChatController::class, 'close'])->name('close');
    Route::post('/conversation/{id}/mark-unread', [ChatController::class, 'markAsUnread'])->name('mark-unread');
    Route::post('/conversation/{id}/mark-read', [ChatController::class, 'markAsRead'])->name('mark-read');
    Route::delete('/conversation/{id}', [ChatController::class, 'destroyConversation'])->name('conversation.destroy');
    Route::get('/online-users', [ChatController::class, 'getOnlineUsers'])->name('online-users');
    
    // AI Settings
    Route::post('/ai-settings', [ChatController::class, 'aiSettings'])->name('ai-settings');
    Route::get('/ai-settings', [ChatController::class, 'aiSettingsPage'])->name('ai-settings.index');
    
    // Chat Widget Settings
    Route::post('/widget-settings', [ChatController::class, 'widgetSettings'])->name('widget-settings');
    Route::get('/widget-settings', [ChatController::class, 'widgetSettingsPage'])->name('widget-settings.index');
    
    // Predefined Messages - CRUD
    Route::get('/predefined', [ChatController::class, 'predefinedMessages'])->name('predefined.index');
    Route::get('/predefined/messages', [ChatController::class, 'getPredefinedMessages'])->name('predefined.messages');
    Route::post('/predefined', [ChatController::class, 'storePredefinedMessage'])->name('predefined.store');
    Route::get('/predefined/{id}/edit', [ChatController::class, 'editPredefinedMessage'])->name('predefined.edit');
    Route::put('/predefined/{id}', [ChatController::class, 'updatePredefinedMessage'])->name('predefined.update');
    Route::delete('/predefined/{id}', [ChatController::class, 'destroyPredefinedMessage'])->name('predefined.destroy');
    Route::post('/predefined/toggle/{id}', [ChatController::class, 'togglePredefinedMessage'])->name('predefined.toggle');
    Route::post('/predefined/reorder', [ChatController::class, 'reorderPredefinedMessages'])->name('predefined.reorder');
});
}); // end permission:support group for chat

// Reports
Route::prefix('reports')->middleware('permission:reports')->name('reports.')->group(function () {
    // Products Wishlist - MUST be before resource routes to avoid 404
    Route::get('/wishlist', [ReportController::class, 'productsWishlist'])->name('wishlist');
    Route::get('/wishlist/export', [ReportController::class, 'productsWishlistExport'])->name('wishlist.export');
    
    // In-House Product Sale - MUST be before resource routes to avoid 404
    Route::get('/in-house-product-sale', [ReportController::class, 'inHouseProductSale'])->name('in-house-product-sale');
    Route::get('/in-house-product-sale/export', [ReportController::class, 'inHouseProductSaleExport'])->name('in-house-product-sale.export');
    
    Route::get('/sales', [ReportController::class, 'sales'])->name('sales');
    Route::get('/products', [ReportController::class, 'products'])->name('products');
    Route::get('/customers', [ReportController::class, 'customers'])->name('customers');
    Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
    Route::get('/inventory/export', [ReportController::class, 'inventoryExport'])->name('inventory.export');
    Route::get('/export/{type}', [ReportController::class, 'export'])->name('export');
});

// Profile
Route::middleware('permission:dashboard')->group(function () {
    Route::get('/profile', [DashboardController::class, 'profile'])->name('profile');
    Route::post('/profile', [DashboardController::class, 'updateProfile'])->name('profile.update');
    Route::post('/password', [DashboardController::class, 'updatePassword'])->name('password.update');
});

// Backup & Restore
Route::middleware('permission:settings')->group(function () {
    Route::get('/backup', [SettingController::class, 'backup'])->name('backup');
    Route::post('/backup/create', [SettingController::class, 'createBackup'])->name('backup.create');
    Route::get('/backup/download/{file}', [SettingController::class, 'downloadBackup'])->name('backup.download');
    Route::post('/backup/restore', [SettingController::class, 'restoreBackup'])->name('backup.restore');
    Route::post('/backup/delete', [SettingController::class, 'deleteBackup'])->name('backup.delete');
});

// Attributes Management
Route::middleware('permission:products')->group(function () {
    Route::resource('attributes', \App\Http\Controllers\Admin\AttributeController::class);
Route::post('/attributes/{attribute}/toggle-status', [\App\Http\Controllers\Admin\AttributeController::class, 'toggleStatus'])->name('attributes.toggle-status');
Route::post('/attributes/{attribute}/toggle-filterable', [\App\Http\Controllers\Admin\AttributeController::class, 'toggleFilterable'])->name('attributes.toggle-filterable');
Route::post('/attributes/bulk-action', [\App\Http\Controllers\Admin\AttributeController::class, 'bulkAction'])->name('attributes.bulk-action');
Route::get('/attributes-export', [\App\Http\Controllers\Admin\AttributeController::class, 'export'])->name('attributes.export');
Route::post('/attributes/{attribute}/values', [\App\Http\Controllers\Admin\AttributeController::class, 'storeValue'])->name('attributes.values.store');
Route::put('/attributes/{attribute}/values/{value}', [\App\Http\Controllers\Admin\AttributeController::class, 'updateValue'])->name('attributes.values.update');
Route::delete('/attributes/{attribute}/values/{value}', [\App\Http\Controllers\Admin\AttributeController::class, 'destroyValue'])->name('attributes.values.destroy');
Route::post('/attributes/{attribute}/values/{value}/toggle-status', [\App\Http\Controllers\Admin\AttributeController::class, 'toggleValueStatus'])->name('attributes.values.toggle-status');
Route::get('/attributes/{attribute}/values', [\App\Http\Controllers\Admin\AttributeController::class, 'getValues'])->name('attributes.values.list');
Route::get('/attributes/{attribute}/products', [\App\Http\Controllers\Admin\AttributeController::class, 'getProducts'])->name('attributes.products.list');
}); // end permission:products group for attributes

// Colors Management
Route::middleware('permission:products')->group(function () {
    Route::resource('colors', \App\Http\Controllers\Admin\ColorController::class);
    Route::post('/colors/{color}/toggle-status', [\App\Http\Controllers\Admin\ColorController::class, 'toggleStatus'])->name('colors.toggle-status');
    Route::post('/colors/{color}/toggle-filterable', [\App\Http\Controllers\Admin\ColorController::class, 'toggleFilterable'])->name('colors.toggle-filterable');
    Route::post('/colors/bulk-action', [\App\Http\Controllers\Admin\ColorController::class, 'bulkAction'])->name('colors.bulk-action');
    Route::get('/colors-export', [\App\Http\Controllers\Admin\ColorController::class, 'export'])->name('colors.export');
    Route::get('/colors/api/list', [\App\Http\Controllers\Admin\ColorController::class, 'getColors'])->name('colors.api.list');
    Route::delete('/colors/{color}/image', [\App\Http\Controllers\Admin\ColorController::class, 'deleteImage'])->name('colors.image.delete');
    Route::post('/colors/{color}/values', [\App\Http\Controllers\Admin\ColorController::class, 'storeValue'])->name('colors.values.store');
    Route::put('/colors/{color}/values/{value}', [\App\Http\Controllers\Admin\ColorController::class, 'updateValue'])->name('colors.values.update');
    Route::delete('/colors/{color}/values/{value}', [\App\Http\Controllers\Admin\ColorController::class, 'destroyValue'])->name('colors.values.destroy');
    Route::post('/colors/{color}/values/{value}/toggle-status', [\App\Http\Controllers\Admin\ColorController::class, 'toggleValueStatus'])->name('colors.values.toggle-status');
    Route::get('/colors/{color}/values', [\App\Http\Controllers\Admin\ColorController::class, 'getValues'])->name('colors.values.list');
    Route::get('/colors/{color}/products', [\App\Http\Controllers\Admin\ColorController::class, 'getProducts'])->name('colors.products.list');
}); // end permission:products group for colors

// Brands Management
Route::middleware('permission:products')->group(function () {
    Route::resource('brands', \App\Http\Controllers\Admin\BrandController::class);
    Route::post('/brands/{brand}/toggle-status', [\App\Http\Controllers\Admin\BrandController::class, 'toggleStatus'])->name('brands.toggle-status');
    Route::post('/brands/{brand}/toggle-featured', [\App\Http\Controllers\Admin\BrandController::class, 'toggleFeatured'])->name('brands.toggle-featured');
    Route::post('/brands/bulk-action', [\App\Http\Controllers\Admin\BrandController::class, 'bulkAction'])->name('brands.bulk-action');
    Route::get('/brands-export', [\App\Http\Controllers\Admin\BrandController::class, 'export'])->name('brands.export');
    Route::get('/brands/api/list', [\App\Http\Controllers\Admin\BrandController::class, 'getBrands'])->name('brands.api.list');
}); // end permission:products group for brands

// Product Bundles
Route::middleware('permission:products')->group(function () {
    Route::resource('product-bundles', \App\Http\Controllers\Admin\ProductBundleController::class);
    Route::post('/product-bundles/{productBundle}/toggle-status', [\App\Http\Controllers\Admin\ProductBundleController::class, 'toggleStatus'])->name('product-bundles.toggle-status');
    Route::post('/product-bundles/{productBundle}/toggle-featured', [\App\Http\Controllers\Admin\ProductBundleController::class, 'toggleFeatured'])->name('product-bundles.toggle-featured');
    Route::post('/product-bundles/bulk-action', [\App\Http\Controllers\Admin\ProductBundleController::class, 'bulkAction'])->name('product-bundles.bulk-action');
    Route::get('/product-bundles-export', [\App\Http\Controllers\Admin\ProductBundleController::class, 'export'])->name('product-bundles.export');
    Route::get('/product-bundles/api/products', [\App\Http\Controllers\Admin\ProductBundleController::class, 'getProducts'])->name('product-bundles.api.products');
}); // end permission:products group for product-bundles

// Product Q&A
Route::middleware('permission:products')->group(function () {
    Route::resource('product-qa', \App\Http\Controllers\Admin\ProductQAController::class);
    Route::post('/product-qa/bulk-action', [\App\Http\Controllers\Admin\ProductQAController::class, 'bulkAction'])->name('product-qa.bulk-action');
    Route::post('/product-qa/{product_qa}/toggle-featured', [\App\Http\Controllers\Admin\ProductQAController::class, 'toggleFeatured'])->name('product-qa.toggle-featured');
    Route::post('/product-qa/{product_qa}/quick-answer', [\App\Http\Controllers\Admin\ProductQAController::class, 'quickAnswer'])->name('product-qa.quick-answer');
    Route::post('/product-qa/{product_qa}/update-status', [\App\Http\Controllers\Admin\ProductQAController::class, 'updateStatus'])->name('product-qa.update-status');
}); // end permission:products group for product-qa

// Inventory Management
Route::prefix('inventory')->middleware('permission:inventory')->name('inventory.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\InventoryController::class, 'index'])->name('index');
    Route::get('/stock-alerts', [\App\Http\Controllers\Admin\InventoryController::class, 'stockAlerts'])->name('stock-alerts');
    Route::get('/stock-history', [\App\Http\Controllers\Admin\InventoryController::class, 'stockHistory'])->name('stock-history');
    Route::post('/adjust', [\App\Http\Controllers\Admin\InventoryController::class, 'adjustStock'])->name('adjust');
    Route::post('/bulk-adjust', [\App\Http\Controllers\Admin\InventoryController::class, 'bulkAdjust'])->name('bulk-adjust');
    Route::get('/product/{id}', [\App\Http\Controllers\Admin\InventoryController::class, 'getProduct'])->name('product');
    Route::post('/threshold', [\App\Http\Controllers\Admin\InventoryController::class, 'updateThreshold'])->name('threshold');
});

// Delivery Management
Route::prefix('delivery')->middleware('permission:delivery')->name('delivery.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\DeliveryController::class, 'index'])->name('index');
    Route::post('/quick-ship', [\App\Http\Controllers\Admin\DeliveryController::class, 'quickShip'])->name('quick-ship');
    Route::post('/mark-delivered', [\App\Http\Controllers\Admin\DeliveryController::class, 'markDelivered'])->name('mark-delivered');
    Route::get('/orders', [\App\Http\Controllers\Admin\DeliveryController::class, 'getOrders'])->name('orders');
    Route::get('/partners', [\App\Http\Controllers\Admin\DeliveryController::class, 'partners'])->name('partners.index');
    Route::get('/partners/create', [\App\Http\Controllers\Admin\DeliveryController::class, 'createPartner'])->name('partners.create');
    Route::post('/partners', [\App\Http\Controllers\Admin\DeliveryController::class, 'storePartner'])->name('partners.store');
    Route::get('/partners/{partner}/edit', [\App\Http\Controllers\Admin\DeliveryController::class, 'editPartner'])->name('partners.edit');
    Route::put('/partners/{partner}', [\App\Http\Controllers\Admin\DeliveryController::class, 'updatePartner'])->name('partners.update');
    Route::delete('/partners/{partner}', [\App\Http\Controllers\Admin\DeliveryController::class, 'destroyPartner'])->name('partners.destroy');
    Route::post('/partners/{partner}/toggle-status', [\App\Http\Controllers\Admin\DeliveryController::class, 'togglePartnerStatus'])->name('partners.toggle-status');
    Route::post('/partners/{partner}/toggle-featured', [\App\Http\Controllers\Admin\DeliveryController::class, 'togglePartnerFeatured'])->name('partners.toggle-featured');
    Route::post('/partners/bulk-action', [\App\Http\Controllers\Admin\DeliveryController::class, 'bulkPartnerAction'])->name('partners.bulk-action');
    Route::get('/carriers', [\App\Http\Controllers\Admin\DeliveryController::class, 'carriers'])->name('carriers.index');
    Route::get('/carriers/create', [\App\Http\Controllers\Admin\DeliveryController::class, 'createCarrier'])->name('carriers.create');
    Route::post('/carriers', [\App\Http\Controllers\Admin\DeliveryController::class, 'storeCarrier'])->name('carriers.store');
    Route::get('/carriers/{carrier}/edit', [\App\Http\Controllers\Admin\DeliveryController::class, 'editCarrier'])->name('carriers.edit');
    Route::put('/carriers/{carrier}', [\App\Http\Controllers\Admin\DeliveryController::class, 'updateCarrier'])->name('carriers.update');
    Route::delete('/carriers/{carrier}', [\App\Http\Controllers\Admin\DeliveryController::class, 'destroyCarrier'])->name('carriers.destroy');
    Route::post('/carriers/{carrier}/toggle-status', [\App\Http\Controllers\Admin\DeliveryController::class, 'toggleCarrierStatus'])->name('carriers.toggle-status');
    Route::post('/carriers/{carrier}/toggle-featured', [\App\Http\Controllers\Admin\DeliveryController::class, 'toggleCarrierFeatured'])->name('carriers.toggle-featured');
    Route::post('/carriers/bulk-action', [\App\Http\Controllers\Admin\DeliveryController::class, 'bulkCarrierAction'])->name('carriers.bulk-action');
    Route::get('/tracking', [\App\Http\Controllers\Admin\DeliveryController::class, 'tracking'])->name('tracking');
    Route::post('/tracking/{order}/update-status', [\App\Http\Controllers\Admin\DeliveryController::class, 'updateTrackingStatus'])->name('tracking.update-status');
    Route::post('/tracking/{order}/generate-number', [\App\Http\Controllers\Admin\DeliveryController::class, 'generateTrackingNumber'])->name('tracking.generate-number');
    Route::post('/tracking/bulk-action', [\App\Http\Controllers\Admin\DeliveryController::class, 'bulkTrackingAction'])->name('bulk-tracking-action');
    Route::post('/tracking/bulk-update-status', [\App\Http\Controllers\Admin\DeliveryController::class, 'bulkUpdateStatus'])->name('tracking.bulk-update-status');
    Route::get('/zones', [\App\Http\Controllers\Admin\DeliveryController::class, 'zones'])->name('zones.index');
    Route::get('/zones/create', [\App\Http\Controllers\Admin\DeliveryController::class, 'createZone'])->name('zones.create');
    Route::post('/zones', [\App\Http\Controllers\Admin\DeliveryController::class, 'storeZone'])->name('zones.store');
    Route::get('/zones/{zone}/edit', [\App\Http\Controllers\Admin\DeliveryController::class, 'editZone'])->name('zones.edit');
    Route::put('/zones/{zone}', [\App\Http\Controllers\Admin\DeliveryController::class, 'updateZone'])->name('zones.update');
    Route::delete('/zones/{zone}', [\App\Http\Controllers\Admin\DeliveryController::class, 'destroyZone'])->name('zones.destroy');
    Route::post('/zones/{zone}/toggle-status', [\App\Http\Controllers\Admin\DeliveryController::class, 'toggleZoneStatus'])->name('zones.toggle-status');
    Route::post('/zones/{zone}/toggle-default', [\App\Http\Controllers\Admin\DeliveryController::class, 'toggleZoneDefault'])->name('zones.toggle-default');
    Route::post('/zones/bulk-action', [\App\Http\Controllers\Admin\DeliveryController::class, 'bulkZoneAction'])->name('zones.bulk-action');
    Route::get('/courier-integration', [\App\Http\Controllers\Admin\DeliveryController::class, 'courierIntegration'])->name('courier-integration');
    Route::post('/courier-integration/add', [\App\Http\Controllers\Admin\DeliveryController::class, 'addCourierFromTemplate'])->name('courier-integration.add');
    Route::get('/delivery-boys', [\App\Http\Controllers\Admin\DeliveryController::class, 'deliveryBoys'])->name('delivery-boys.index');
    Route::get('/delivery-boys/create', [\App\Http\Controllers\Admin\DeliveryController::class, 'createDeliveryBoy'])->name('delivery-boys.create');
    Route::post('/delivery-boys', [\App\Http\Controllers\Admin\DeliveryController::class, 'storeDeliveryBoy'])->name('delivery-boys.store');
    Route::get('/delivery-boys/{deliveryBoy}/edit', [\App\Http\Controllers\Admin\DeliveryController::class, 'editDeliveryBoy'])->name('delivery-boys.edit');
    Route::put('/delivery-boys/{deliveryBoy}', [\App\Http\Controllers\Admin\DeliveryController::class, 'updateDeliveryBoy'])->name('delivery-boys.update');
    Route::delete('/delivery-boys/{deliveryBoy}', [\App\Http\Controllers\Admin\DeliveryController::class, 'destroyDeliveryBoy'])->name('delivery-boys.destroy');
    Route::post('/delivery-boys/{deliveryBoy}/toggle-status', [\App\Http\Controllers\Admin\DeliveryController::class, 'toggleDeliveryBoyStatus'])->name('delivery-boys.toggle-status');
    Route::post('/delivery-boys/{deliveryBoy}/toggle-availability', [\App\Http\Controllers\Admin\DeliveryController::class, 'toggleDeliveryBoyAvailability'])->name('delivery-boys.toggle-availability');
    Route::post('/delivery-boys/bulk-action', [\App\Http\Controllers\Admin\DeliveryController::class, 'bulkDeliveryBoyAction'])->name('delivery-boys.bulk-action');
    Route::get('/schedules', [\App\Http\Controllers\Admin\DeliveryController::class, 'schedules'])->name('schedules.index');
    Route::get('/schedules/create', [\App\Http\Controllers\Admin\DeliveryController::class, 'createSchedule'])->name('schedules.create');
    Route::post('/schedules', [\App\Http\Controllers\Admin\DeliveryController::class, 'storeSchedule'])->name('schedules.store');
    Route::get('/schedules/{id}/edit', [\App\Http\Controllers\Admin\DeliveryController::class, 'editSchedule'])->name('schedules.edit');
    Route::put('/schedules/{id}', [\App\Http\Controllers\Admin\DeliveryController::class, 'updateSchedule'])->name('schedules.update');
    Route::delete('/schedules/{id}', [\App\Http\Controllers\Admin\DeliveryController::class, 'destroySchedule'])->name('schedules.destroy');
    Route::post('/schedules/{id}/toggle', [\App\Http\Controllers\Admin\DeliveryController::class, 'toggleScheduleStatus'])->name('schedules.toggle');
    Route::post('/schedules/bulk-action', [\App\Http\Controllers\Admin\DeliveryController::class, 'bulkScheduleAction'])->name('schedules.bulk-action');
    Route::get('/reports', [\App\Http\Controllers\Admin\DeliveryController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [\App\Http\Controllers\Admin\DeliveryController::class, 'exportReports'])->name('reports.export');
});

// Quotations
Route::prefix('quotations')->name('quotations.')->middleware('permission:orders')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\QuotationController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\QuotationController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\QuotationController::class, 'store'])->name('store');
    Route::get('/{quotation}', [\App\Http\Controllers\Admin\QuotationController::class, 'show'])->name('show');
    Route::get('/{quotation}/edit', [\App\Http\Controllers\Admin\QuotationController::class, 'edit'])->name('edit');
    Route::put('/{quotation}', [\App\Http\Controllers\Admin\QuotationController::class, 'update'])->name('update');
    Route::delete('/{quotation}', [\App\Http\Controllers\Admin\QuotationController::class, 'destroy'])->name('destroy');
    Route::post('/{quotation}/send', [\App\Http\Controllers\Admin\QuotationController::class, 'send'])->name('send');
    Route::post('/{quotation}/convert-to-order', [\App\Http\Controllers\Admin\QuotationController::class, 'convertToOrder'])->name('convert-to-order');
    Route::post('/{quotation}/status', [\App\Http\Controllers\Admin\QuotationController::class, 'updateStatus'])->name('status');
    Route::get('/{quotation}/print', [\App\Http\Controllers\Admin\QuotationController::class, 'print'])->name('print');
    Route::get('/{quotation}/download', [\App\Http\Controllers\Admin\QuotationController::class, 'download'])->name('download');
    Route::post('/bulk-action', [\App\Http\Controllers\Admin\QuotationController::class, 'bulkAction'])->name('bulk-action');
    Route::get('/api/products/search', [\App\Http\Controllers\Admin\QuotationController::class, 'searchProducts'])->name('api.products.search');
    Route::get('/api/product', [\App\Http\Controllers\Admin\QuotationController::class, 'getProduct'])->name('api.product');
});

// Subscriptions
Route::prefix('subscriptions')->name('subscriptions.')->middleware('permission:orders')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\SubscriptionController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\SubscriptionController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\SubscriptionController::class, 'store'])->name('store');
    Route::get('/{subscription}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'show'])->name('show');
    Route::get('/{subscription}/edit', [\App\Http\Controllers\Admin\SubscriptionController::class, 'edit'])->name('edit');
    Route::put('/{subscription}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'update'])->name('update');
    Route::delete('/{subscription}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'destroy'])->name('destroy');
    
    // Actions
    Route::post('/{subscription}/activate', [\App\Http\Controllers\Admin\SubscriptionController::class, 'activate'])->name('activate');
    Route::post('/{subscription}/pause', [\App\Http\Controllers\Admin\SubscriptionController::class, 'pause'])->name('pause');
    Route::post('/{subscription}/cancel', [\App\Http\Controllers\Admin\SubscriptionController::class, 'cancel'])->name('cancel');
    Route::post('/{subscription}/process-billing', [\App\Http\Controllers\Admin\SubscriptionController::class, 'processBilling'])->name('process-billing');
    
    // Bulk Actions
    Route::post('/bulk-action', [\App\Http\Controllers\Admin\SubscriptionController::class, 'bulkAction'])->name('bulk-action');
    
    // API Routes
    Route::get('/api/customer/{user}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'getCustomerDetails'])->name('customer-details');
    Route::get('/api/product/{product}', [\App\Http\Controllers\Admin\SubscriptionController::class, 'getProductDetails'])->name('product-details');
});

// Refund Management
Route::prefix('refunds')->middleware('permission:refund')->name('refunds.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\RefundController::class, 'index'])->name('index');
    Route::get('/requests', [\App\Http\Controllers\Admin\RefundController::class, 'requests'])->name('requests');
    Route::get('/approved', [\App\Http\Controllers\Admin\RefundController::class, 'approved'])->name('approved');
    Route::get('/rejected', [\App\Http\Controllers\Admin\RefundController::class, 'rejected'])->name('rejected');
    Route::get('/configuration', [\App\Http\Controllers\Admin\RefundController::class, 'configuration'])->name('configuration');
    Route::post('/configuration', [\App\Http\Controllers\Admin\RefundController::class, 'updateConfiguration'])->name('configuration.update');
    Route::post('/bulk', [\App\Http\Controllers\Admin\RefundController::class, 'bulk'])->name('bulk');
    Route::get('/{id}', [\App\Http\Controllers\Admin\RefundController::class, 'show'])->name('show');
    Route::post('/{id}/approve', [\App\Http\Controllers\Admin\RefundController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [\App\Http\Controllers\Admin\RefundController::class, 'reject'])->name('reject');
    Route::post('/{id}/process', [\App\Http\Controllers\Admin\RefundController::class, 'process'])->name('process');
});

// Sellers (B2B) Management
Route::prefix('sellers')->middleware('permission:sellers')->name('sellers.')->group(function () {
    // Specific routes MUST come before resource routes to avoid 404 errors
    
    // Payout Routes - must come before /{id} route
    Route::get('/payouts', [\App\Http\Controllers\Admin\SellerController::class, 'payouts'])->name('payouts');
    Route::get('/payouts/create/{id}', [\App\Http\Controllers\Admin\SellerController::class, 'createPayout'])->name('payouts.create');
    Route::post('/payouts/create/{id}', [\App\Http\Controllers\Admin\SellerController::class, 'storePayout'])->name('payouts.store');
    Route::get('/payouts/{id}', [\App\Http\Controllers\Admin\SellerController::class, 'showPayout'])->name('payouts.show');
    
    // Payout Requests Routes
    Route::get('/payout-requests', [\App\Http\Controllers\Admin\SellerController::class, 'payoutRequests'])->name('payout-requests');
    Route::post('/payout-requests/{id}/approve', [\App\Http\Controllers\Admin\SellerController::class, 'approvePayout'])->name('payout-requests.approve');
    Route::post('/payout-requests/{id}/reject', [\App\Http\Controllers\Admin\SellerController::class, 'rejectPayout'])->name('payout-requests.reject');
    
    // Commission Routes
    Route::get('/commission', [\App\Http\Controllers\Admin\SellerController::class, 'commission'])->name('commission');
    Route::post('/commission', [\App\Http\Controllers\Admin\SellerController::class, 'updateCommission'])->name('commission.update');
    
    // Verification Routes
    Route::get('/verification', [\App\Http\Controllers\Admin\SellerController::class, 'verification'])->name('verification');
    Route::post('/verification/{id}', [\App\Http\Controllers\Admin\SellerController::class, 'processVerification'])->name('verification.process');
    
    // Seller CRUD Routes - these use {id} so must come after specific routes
    Route::get('/', [\App\Http\Controllers\Admin\SellerController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\SellerController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\SellerController::class, 'store'])->name('store');
    Route::get('/{id}', [\App\Http\Controllers\Admin\SellerController::class, 'show'])->name('show');
    Route::get('/{id}/edit', [\App\Http\Controllers\Admin\SellerController::class, 'edit'])->name('edit');
    Route::put('/{id}', [\App\Http\Controllers\Admin\SellerController::class, 'update'])->name('update');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\SellerController::class, 'destroy'])->name('destroy');
    
    // Status management
    Route::post('/{id}/approve', [\App\Http\Controllers\Admin\SellerController::class, 'approve'])->name('approve');
    Route::post('/{id}/reject', [\App\Http\Controllers\Admin\SellerController::class, 'reject'])->name('reject');
    Route::post('/{id}/suspend', [\App\Http\Controllers\Admin\SellerController::class, 'suspend'])->name('suspend');
    Route::post('/{id}/activate', [\App\Http\Controllers\Admin\SellerController::class, 'activate'])->name('activate');
    
    // Bulk actions
    Route::post('/bulk-update', [\App\Http\Controllers\Admin\SellerController::class, 'bulkUpdateStatus'])->name('bulk-update');
    Route::post('/bulk-delete', [\App\Http\Controllers\Admin\SellerController::class, 'bulkDelete'])->name('bulk-delete');
});

// Reports - Additional Routes
Route::prefix('reports')->name('reports.')->middleware('permission:reports')->group(function () {
    Route::get('/seller-sales', [ReportController::class, 'sellerSales'])->name('seller-sales');
    Route::get('/seller-sales/export', [ReportController::class, 'sellerSalesExport'])->name('seller-sales.export');
    Route::get('/user-searches', [ReportController::class, 'userSearches'])->name('user-searches');
    Route::get('/user-searches/export', [ReportController::class, 'userSearchesExport'])->name('user-searches.export');
    Route::get('/commission-history', [ReportController::class, 'commissionHistory'])->name('commission-history');
    Route::get('/commission-history/export', [ReportController::class, 'commissionHistoryExport'])->name('commission-history.export');
    Route::get('/wallet-history', [ReportController::class, 'walletHistory'])->name('wallet-history');
    Route::get('/wallet-history/export', [ReportController::class, 'walletHistoryExport'])->name('wallet-history.export');
});

// Marketing
Route::prefix('marketing')->middleware('permission:marketing')->name('marketing.')->group(function () {
    // Flash Deals - must be before wildcard routes
    Route::get('/flash-deals/create', [\App\Http\Controllers\Admin\FlashDealController::class, 'create'])->name('flash-deals.create');
    Route::post('/flash-deals', [\App\Http\Controllers\Admin\FlashDealController::class, 'store'])->name('flash-deals.store');
    Route::get('/flash-deals/{id}/edit', [\App\Http\Controllers\Admin\FlashDealController::class, 'edit'])->name('flash-deals.edit');
    Route::put('/flash-deals/{id}', [\App\Http\Controllers\Admin\FlashDealController::class, 'update'])->name('flash-deals.update');
    Route::delete('/flash-deals/{id}', [\App\Http\Controllers\Admin\FlashDealController::class, 'destroy'])->name('flash-deals.destroy');
    Route::post('/flash-deals/{id}/toggle-status', [\App\Http\Controllers\Admin\FlashDealController::class, 'toggleStatus'])->name('flash-deals.toggle-status');
    Route::get('/flash-deals/{id}/products', [\App\Http\Controllers\Admin\FlashDealController::class, 'products'])->name('flash-deals.products');
    Route::put('/flash-deals/{id}/products', [\App\Http\Controllers\Admin\FlashDealController::class, 'updateProducts'])->name('flash-deals.products.update');
    Route::post('/flash-deals/{id}/products', [\App\Http\Controllers\Admin\FlashDealController::class, 'addProducts'])->name('flash-deals.add-products');
    Route::delete('/flash-deals/{id}/products/{productId}', [\App\Http\Controllers\Admin\FlashDealController::class, 'removeProduct'])->name('flash-deals.remove-product');
    Route::put('/flash-deals/{id}/products/{productId}', [\App\Http\Controllers\Admin\FlashDealController::class, 'updateProduct'])->name('flash-deals.update-product');
    Route::get('/flash-deals', [\App\Http\Controllers\Admin\FlashDealController::class, 'index'])->name('flash-deals.index');
    
    Route::get('/newsletters', [\App\Http\Controllers\Admin\NewsletterController::class, 'index'])->name('newsletters.index');
    Route::get('/newsletters/create', [\App\Http\Controllers\Admin\NewsletterController::class, 'create'])->name('newsletters.create');
    Route::post('/newsletters', [\App\Http\Controllers\Admin\NewsletterController::class, 'store'])->name('newsletters.store');
    Route::get('/newsletters/recipient-count', [\App\Http\Controllers\Admin\NewsletterController::class, 'getRecipientCount'])->name('newsletters.recipient-count');
    Route::get('/newsletters/{newsletter}/edit', [\App\Http\Controllers\Admin\NewsletterController::class, 'edit'])->name('newsletters.edit');
    Route::put('/newsletters/{newsletter}', [\App\Http\Controllers\Admin\NewsletterController::class, 'update'])->name('newsletters.update');
    Route::delete('/newsletters/{newsletter}', [\App\Http\Controllers\Admin\NewsletterController::class, 'destroy'])->name('newsletters.destroy');
    Route::post('/newsletters/{newsletter}/send', [\App\Http\Controllers\Admin\NewsletterController::class, 'send'])->name('newsletters.send');
    Route::get('/newsletters/{newsletter}/preview', [\App\Http\Controllers\Admin\NewsletterController::class, 'preview'])->name('newsletters.preview');
    Route::post('/newsletters/{newsletter}/duplicate', [\App\Http\Controllers\Admin\NewsletterController::class, 'duplicate'])->name('newsletters.duplicate');
    
    Route::get('/bulk-sms', [\App\Http\Controllers\Admin\SmsController::class, 'index'])->name('bulk-sms.index');
    Route::post('/bulk-sms/send', [\App\Http\Controllers\Admin\SmsController::class, 'send'])->name('bulk-sms.send');
    Route::get('/bulk-sms/recipient-count', [\App\Http\Controllers\Admin\SmsController::class, 'getRecipientCount'])->name('bulk-sms.recipient-count');
    Route::delete('/bulk-sms/{id}', [\App\Http\Controllers\Admin\SmsController::class, 'destroy'])->name('bulk-sms.destroy');
    
    Route::get('/subscribers', [\App\Http\Controllers\Admin\SubscriberController::class, 'index'])->name('subscribers.index');
    Route::post('/subscribers', [\App\Http\Controllers\Admin\SubscriberController::class, 'store'])->name('subscribers.store');
    Route::get('/subscribers/export', [\App\Http\Controllers\Admin\SubscriberController::class, 'export'])->name('subscribers.export');
    Route::get('/subscribers/count', [\App\Http\Controllers\Admin\SubscriberController::class, 'getCount'])->name('subscribers.count');
    Route::delete('/subscribers/{subscriber}', [\App\Http\Controllers\Admin\SubscriberController::class, 'destroy'])->name('subscribers.destroy');
    Route::post('/subscribers/{subscriber}/unsubscribe', [\App\Http\Controllers\Admin\SubscriberController::class, 'unsubscribe'])->name('subscribers.unsubscribe');
    Route::post('/subscribers/{subscriber}/resubscribe', [\App\Http\Controllers\Admin\SubscriberController::class, 'resubscribe'])->name('subscribers.resubscribe');
    
    // Abandoned Cart Recovery
    Route::prefix('abandoned-cart')->name('abandoned-cart.')->group(function () {
        // Index - must be first
        Route::get('/', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'index'])->name('index');
        // Specific routes BEFORE wildcard
        Route::get('/settings', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'settings'])->name('settings');
        Route::post('/settings', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'updateSettings'])->name('settings.update');
        Route::get('/conversion-tracking', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'conversionTracking'])->name('conversion-tracking');
        // Wildcard routes AFTER specific routes
        Route::get('/{id}', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'show'])->name('show');
        Route::post('/{id}/send-reminder', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'sendReminder'])->name('send-reminder');
        Route::post('/{id}/mark-recovered', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'markRecovered'])->name('mark-recovered');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\AbandonedCartController::class, 'destroy'])->name('destroy');
    });
    
    // Gift Cards - Specific routes before wildcard routes (404 solution)
    Route::prefix('gift-cards')->name('gift-cards.')->group(function () {
        // Index - must be first
        Route::get('/', [\App\Http\Controllers\Admin\GiftCardController::class, 'index'])->name('index');
        // Specific routes BEFORE wildcard
        Route::get('/create', [\App\Http\Controllers\Admin\GiftCardController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\GiftCardController::class, 'store'])->name('store');
        Route::get('/generate-code', [\App\Http\Controllers\Admin\GiftCardController::class, 'generateCode'])->name('generate-code');
        // Wildcard routes AFTER specific routes
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\GiftCardController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\GiftCardController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\GiftCardController::class, 'destroy'])->name('destroy');
        Route::post('/{id}/toggle-status', [\App\Http\Controllers\Admin\GiftCardController::class, 'toggleStatus'])->name('toggle-status');
    });
    
    // Push Notifications - Specific routes before wildcard routes (404 solution)
    Route::prefix('push-notifications')->name('push-notifications.')->group(function () {
        // Index - must be first
        Route::get('/', [\App\Http\Controllers\Admin\PushNotificationController::class, 'index'])->name('index');
        // Specific routes BEFORE wildcard
        Route::get('/create', [\App\Http\Controllers\Admin\PushNotificationController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\PushNotificationController::class, 'store'])->name('store');
        Route::get('/recipient-count', [\App\Http\Controllers\Admin\PushNotificationController::class, 'getRecipientCount'])->name('recipient-count');
        // Wildcard routes AFTER specific routes
        Route::get('/{pushNotification}/edit', [\App\Http\Controllers\Admin\PushNotificationController::class, 'edit'])->name('edit');
        Route::put('/{pushNotification}', [\App\Http\Controllers\Admin\PushNotificationController::class, 'update'])->name('update');
        Route::delete('/{pushNotification}', [\App\Http\Controllers\Admin\PushNotificationController::class, 'destroy'])->name('destroy');
        Route::post('/{pushNotification}/send', [\App\Http\Controllers\Admin\PushNotificationController::class, 'send'])->name('send');
        Route::post('/{pushNotification}/duplicate', [\App\Http\Controllers\Admin\PushNotificationController::class, 'duplicate'])->name('duplicate');
    });
    
    // Price Rules - Specific routes before wildcard routes (404 solution)
    Route::prefix('price-rules')->name('price-rules.')->group(function () {
        // Index - must be first
        Route::get('/', [\App\Http\Controllers\Admin\PriceRuleController::class, 'index'])->name('index');
        // Specific routes BEFORE wildcard
        Route::get('/create', [\App\Http\Controllers\Admin\PriceRuleController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\PriceRuleController::class, 'store'])->name('store');
        Route::get('/products/{id}', [\App\Http\Controllers\Admin\PriceRuleController::class, 'products'])->name('products');
        Route::put('/products/{id}', [\App\Http\Controllers\Admin\PriceRuleController::class, 'updateProducts'])->name('products.update');
        Route::post('/products/{id}', [\App\Http\Controllers\Admin\PriceRuleController::class, 'addProducts'])->name('products.add');
        Route::delete('/products/{id}/products/{productId}', [\App\Http\Controllers\Admin\PriceRuleController::class, 'removeProduct'])->name('products.remove');
        Route::post('/{id}/toggle-status', [\App\Http\Controllers\Admin\PriceRuleController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/get-products', [\App\Http\Controllers\Admin\PriceRuleController::class, 'getProducts'])->name('get-products');
        // Wildcard routes AFTER specific routes
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\PriceRuleController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\PriceRuleController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\PriceRuleController::class, 'destroy'])->name('destroy');
    });
});

// Support - Additional Routes
// IMPORTANT: Specific routes must come before parameterized routes to avoid 404 errors
Route::prefix('support')->middleware('permission:support')->name('support.')->group(function () {
    // Product Queries (using ProductQAController)
    // Must come before /tickets/{id} to avoid 404 errors
    Route::get('/product-queries', [\App\Http\Controllers\Admin\ProductQAController::class, 'index'])->name('product-queries.index');
    Route::get('/product-queries/{product_qa}', [\App\Http\Controllers\Admin\ProductQAController::class, 'show'])->name('product-queries.show');
    Route::put('/product-queries/{product_qa}', [\App\Http\Controllers\Admin\ProductQAController::class, 'update'])->name('product-queries.update');
    Route::delete('/product-queries/{product_qa}', [\App\Http\Controllers\Admin\ProductQAController::class, 'destroy'])->name('product-queries.destroy');
    Route::post('/product-queries/bulk-action', [\App\Http\Controllers\Admin\ProductQAController::class, 'bulkAction'])->name('product-queries.bulk-action');
    Route::post('/product-queries/{product_qa}/toggle-featured', [\App\Http\Controllers\Admin\ProductQAController::class, 'toggleFeatured'])->name('product-queries.toggle-featured');
    Route::post('/product-queries/{product_qa}/quick-answer', [\App\Http\Controllers\Admin\ProductQAController::class, 'quickAnswer'])->name('product-queries.quick-answer');
    Route::post('/product-queries/{product_qa}/update-status', [\App\Http\Controllers\Admin\ProductQAController::class, 'updateStatus'])->name('product-queries.update-status');
    
    // Tickets - List
    Route::get('/tickets', [\App\Http\Controllers\Admin\SupportController::class, 'tickets'])->name('tickets.index');
    
    // Tickets - Bulk Actions (before parameterized routes)
    Route::post('/tickets/bulk-action', [\App\Http\Controllers\Admin\SupportController::class, 'bulkAction'])->name('tickets.bulk-action');
    
    // Tickets - Specific routes (before wildcard {id})
    Route::post('/tickets/{id}/reply', [\App\Http\Controllers\Admin\SupportController::class, 'replyTicket'])->name('tickets.reply');
    Route::post('/tickets/{id}/close', [\App\Http\Controllers\Admin\SupportController::class, 'closeTicket'])->name('tickets.close');
    Route::get('/tickets/{id}/reopen', [\App\Http\Controllers\Admin\SupportController::class, 'reopenTicket'])->name('tickets.reopen');
    Route::post('/tickets/{id}/update-status', [\App\Http\Controllers\Admin\SupportController::class, 'updateTicketStatus'])->name('tickets.update-status');
    Route::post('/tickets/{id}/assign', [\App\Http\Controllers\Admin\SupportController::class, 'assignTicket'])->name('tickets.assign');
    
    // Tickets - Show and Delete (wildcard {id} at the end)
    Route::get('/tickets/{id}', [\App\Http\Controllers\Admin\SupportController::class, 'showTicket'])->name('tickets.show');
    Route::delete('/tickets/{id}', [\App\Http\Controllers\Admin\SupportController::class, 'destroyTicket'])->name('tickets.destroy');
});

// OTP System
Route::prefix('otp')->middleware('permission:otp')->name('otp.')->group(function () {
    Route::get('/configuration', [\App\Http\Controllers\Admin\OtpController::class, 'configuration'])->name('configuration');
    Route::post('/configuration', [\App\Http\Controllers\Admin\OtpController::class, 'updateConfiguration'])->name('configuration.update');
    Route::get('/sms-templates', [\App\Http\Controllers\Admin\OtpController::class, 'smsTemplates'])->name('sms-templates');
    Route::post('/sms-templates', [\App\Http\Controllers\Admin\OtpController::class, 'updateSmsTemplates'])->name('sms-templates.update');
    Route::get('/credentials', [\App\Http\Controllers\Admin\OtpController::class, 'credentials'])->name('credentials');
    Route::post('/credentials', [\App\Http\Controllers\Admin\OtpController::class, 'updateCredentials'])->name('credentials.update');
    Route::post('/send-test-sms', [\App\Http\Controllers\Admin\OtpController::class, 'sendTestSms'])->name('send-test-sms');
    Route::post('/check-balance', [\App\Http\Controllers\Admin\OtpController::class, 'checkBalance'])->name('check-balance');
});

// Settings - Additional Routes
Route::prefix('settings')->name('settings.')->group(function () {
    Route::get('/languages', [SettingController::class, 'languages'])->name('languages');
    Route::post('/languages', [SettingController::class, 'storeLanguage'])->name('languages.store');
    Route::put('/languages/{id}', [SettingController::class, 'updateLanguage'])->name('languages.update');
    Route::delete('/languages/{id}', [SettingController::class, 'destroyLanguage'])->name('languages.destroy');
    Route::post('/languages/{id}/set-default', [SettingController::class, 'setDefaultLanguage'])->name('languages.setDefault');
    Route::post('/languages/toggle-frontend', [SettingController::class, 'toggleFrontendLanguageSwitcher'])->name('languages.toggleFrontend');
    Route::get('/currency', [SettingController::class, 'currency'])->name('currency');
    Route::post('/currency', [SettingController::class, 'storeCurrency'])->name('currency.store');
    Route::put('/currency/{id}', [SettingController::class, 'updateCurrency'])->name('currency.update');
    Route::delete('/currency/{id}', [SettingController::class, 'destroyCurrency'])->name('currency.destroy');
    Route::post('/currency/{id}/set-default', [SettingController::class, 'setDefaultCurrency'])->name('currency.setDefault');
    Route::post('/currency/toggle-frontend', [SettingController::class, 'toggleFrontendCurrencySwitcher'])->name('currency.toggleFrontend');
    
    // VAT & Tax Settings
    Route::get('/vat-tax', [SettingController::class, 'vatTax'])->name('vat-tax');
    Route::post('/vat-tax/settings', [SettingController::class, 'updateVatTax'])->name('vat-tax.updateSettings');
    Route::post('/vat-tax', [SettingController::class, 'storeTax'])->name('vat-tax.store');
    Route::put('/vat-tax/{id}', [SettingController::class, 'updateTax'])->name('vat-tax.update');
    Route::delete('/vat-tax/{id}', [SettingController::class, 'destroyTax'])->name('vat-tax.destroy');
    Route::post('/vat-tax/{id}/set-default', [SettingController::class, 'setDefaultTax'])->name('vat-tax.setDefault');
    
    Route::get('/order-configuration', [SettingController::class, 'orderConfiguration'])->name('order-configuration');
    Route::post('/order-configuration', [SettingController::class, 'updateOrderConfiguration'])->name('order-configuration.update');
    Route::get('/file-system', [SettingController::class, 'fileSystem'])->name('file-system');
    Route::post('/file-system', [SettingController::class, 'updateFileSystem'])->name('file-system.update');
    Route::post('/file-system/clear-cache', [SettingController::class, 'clearCache'])->name('file-system.clear-cache');
    
    // API endpoint for frontend to get file system settings
    Route::get('/api/file-system-settings', [SettingController::class, 'getFileSystemSettingsApi'])->name('api.file-system-settings');
    // API endpoint for frontend to get SEO settings
    Route::get('/api/seo-settings', [SettingController::class, 'getSeoSettingsApi'])->name('api.seo-settings');
    // API endpoint for frontend to get email templates
    Route::get('/api/email-templates', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'getAllTemplatesApi'])->name('api.email-templates');
    Route::get('/api/email-templates/{slug}', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'getTemplateApi'])->name('api.email-templates.show');
    Route::post('/api/email-templates/render', [\App\Http\Controllers\Admin\EmailTemplateController::class, 'renderTemplate'])->name('api.email-templates.render');
    Route::get('/shipping', [SettingController::class, 'shipping'])->name('shipping');
    Route::post('/shipping', [SettingController::class, 'updateShipping'])->name('shipping.update');
    

    // Notification Settings
    Route::prefix('notifications')->name('notifications.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SettingController::class, 'notifications'])->name('index');
        Route::post('/', [\App\Http\Controllers\Admin\SettingController::class, 'updateNotifications'])->name('update');
    });

    // API endpoint for frontend to get notification settings
    Route::get('/api/notification-settings', [SettingController::class, 'getNotificationSettingsApi'])->name('api.notification-settings');
});

// Location Management (States, Cities & Areas)
Route::prefix('locations')->middleware('permission:locations')->name('locations.')->group(function () {
    // States
    Route::prefix('states')->name('states.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\StateController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\StateController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\StateController::class, 'store'])->name('store');
        Route::get('/{state}/edit', [\App\Http\Controllers\Admin\StateController::class, 'edit'])->name('edit');
        Route::put('/{state}', [\App\Http\Controllers\Admin\StateController::class, 'update'])->name('update');
        Route::delete('/{state}', [\App\Http\Controllers\Admin\StateController::class, 'destroy'])->name('destroy');
        Route::post('/{state}/toggle-status', [\App\Http\Controllers\Admin\StateController::class, 'toggleStatus'])->name('toggle-status');
    });
    // Cities
    Route::prefix('cities')->name('cities.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\CityController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\CityController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\CityController::class, 'store'])->name('store');
        Route::get('/{city}/edit', [\App\Http\Controllers\Admin\CityController::class, 'edit'])->name('edit');
        Route::put('/{city}', [\App\Http\Controllers\Admin\CityController::class, 'update'])->name('update');
        Route::delete('/{city}', [\App\Http\Controllers\Admin\CityController::class, 'destroy'])->name('destroy');
        Route::post('/{city}/toggle-status', [\App\Http\Controllers\Admin\CityController::class, 'toggleStatus'])->name('toggle-status');
    });
    // API: get cities for AJAX
    Route::get('/get-cities', [\App\Http\Controllers\Admin\CityController::class, 'getCities'])->name('get-cities');
    // Areas
    Route::prefix('areas')->name('areas.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AreaController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\AreaController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AreaController::class, 'store'])->name('store');
        Route::get('/{area}/edit', [\App\Http\Controllers\Admin\AreaController::class, 'edit'])->name('edit');
        Route::put('/{area}', [\App\Http\Controllers\Admin\AreaController::class, 'update'])->name('update');
        Route::delete('/{area}', [\App\Http\Controllers\Admin\AreaController::class, 'destroy'])->name('destroy');
        Route::post('/{area}/toggle-status', [\App\Http\Controllers\Admin\AreaController::class, 'toggleStatus'])->name('toggle-status');
    });
    // API: get areas for AJAX
    Route::get('/get-areas', [\App\Http\Controllers\Admin\AreaController::class, 'getAreas'])->name('get-areas');

    // Country Settings
    Route::get('/country-settings', [\App\Http\Controllers\Admin\CityController::class, 'countrySettings'])->name('country-settings');
    Route::post('/country-settings', [\App\Http\Controllers\Admin\CityController::class, 'updateCountrySettings'])->name('country-settings.update');
});

// Warehouse Management
Route::prefix('warehouses')->middleware('permission:warehouse')->name('warehouses.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\WarehouseController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\WarehouseController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\WarehouseController::class, 'store'])->name('store');
    // Specific routes MUST come before wildcard routes (Preference.md #13 - Route Ordering)
    Route::get('/get-cities', [\App\Http\Controllers\Admin\WarehouseController::class, 'getCities'])->name('get-cities');
    Route::get('/{id}/edit', [\App\Http\Controllers\Admin\WarehouseController::class, 'edit'])->name('edit');
    Route::put('/{id}', [\App\Http\Controllers\Admin\WarehouseController::class, 'update'])->name('update');
    Route::get('/{id}', [\App\Http\Controllers\Admin\WarehouseController::class, 'show'])->name('show');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\WarehouseController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [\App\Http\Controllers\Admin\WarehouseController::class, 'bulkAction'])->name('bulk-action');
    Route::patch('/{id}/toggle-status', [\App\Http\Controllers\Admin\WarehouseController::class, 'toggleStatus'])->name('toggle-status');
    // Warehouse City Assignment API
    // Warehouse Picking/Orders
    Route::get('/{id}/orders', [\App\Http\Controllers\Admin\WarehouseController::class, 'orders'])->name('orders');
    Route::get('/{id}/picking', [\App\Http\Controllers\Admin\WarehouseController::class, 'picking'])->name('picking');
    Route::patch('/{id}/orders/{orderId}/start-picking', [\App\Http\Controllers\Admin\WarehouseController::class, 'startPicking'])->name('orders.start-picking');
    Route::patch('/{id}/orders/{orderId}/mark-packed', [\App\Http\Controllers\Admin\WarehouseController::class, 'markPacked'])->name('orders.mark-packed');
});

// Staff Management
Route::prefix('staffs')->middleware('permission:staffs')->name('staffs.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\StaffController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\StaffController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\StaffController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [\App\Http\Controllers\Admin\StaffController::class, 'edit'])->name('edit');
    Route::put('/{id}', [\App\Http\Controllers\Admin\StaffController::class, 'update'])->name('update');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\StaffController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-action', [\App\Http\Controllers\Admin\StaffController::class, 'bulkAction'])->name('bulk-action');
    Route::get('/warehouse', [\App\Http\Controllers\Admin\StaffController::class, 'warehouse'])->name('warehouse');
    Route::get('/permissions', function () {
        return redirect()->route('admin.permissions.index');
    })->name('permissions');
    Route::post('/permissions', [\App\Http\Controllers\Admin\StaffController::class, 'updatePermissions'])->name('permissions.update');
});

// Permission Management (super_admin only - dynamic CRUD)
Route::prefix('permissions')->middleware(['permission:staffs', 'auth'])->name('permissions.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\PermissionController::class, 'dashboard'])->name('index');
    Route::post('/', [\App\Http\Controllers\Admin\PermissionController::class, 'store'])->name('store');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\PermissionController::class, 'destroy'])->name('destroy');
    Route::post('/bulk-delete', [\App\Http\Controllers\Admin\PermissionController::class, 'bulkDelete'])->name('bulk-delete');
    Route::post('/toggle-key', [\App\Http\Controllers\Admin\PermissionController::class, 'toggleKey'])->name('toggle-key');
    Route::post('/toggle-submenu/{submenuKey}', [\App\Http\Controllers\Admin\PermissionController::class, 'toggleSubmenuVisibility'])->name('toggle-submenu');
    Route::post('/toggle-visibility/{module}', [\App\Http\Controllers\Admin\PermissionController::class, 'toggleModuleVisibility'])->name('toggle-visibility');
});

// Role Management (super_admin only - dynamic CRUD)
Route::prefix('roles')->middleware(['permission:staffs', 'auth'])->name('roles.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\PermissionRoleController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\PermissionRoleController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\PermissionRoleController::class, 'store'])->name('store');
    Route::get('/{id}/edit', [\App\Http\Controllers\Admin\PermissionRoleController::class, 'edit'])->name('edit');
    Route::put('/{id}', [\App\Http\Controllers\Admin\PermissionRoleController::class, 'update'])->name('update');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\PermissionRoleController::class, 'destroy'])->name('destroy');
});

// System Management
Route::prefix('system')->middleware('permission:system')->name('system.')->group(function () {
    Route::get('/update', [\App\Http\Controllers\Admin\SystemController::class, 'update'])->name('update');
    Route::post('/update', [\App\Http\Controllers\Admin\SystemController::class, 'performUpdate'])->name('update.perform');
    Route::post('/update/settings', [\App\Http\Controllers\Admin\SystemController::class, 'saveSettings'])->name('update.settings');
    Route::get('/server-status', [\App\Http\Controllers\Admin\SystemController::class, 'serverStatus'])->name('server-status');
    
    // API endpoint for frontend to get system info
    Route::get('/api/info', [\App\Http\Controllers\Admin\SystemController::class, 'getSystemInfoApi'])->name('api.info');
    Route::get('/api/version', [\App\Http\Controllers\Admin\SystemController::class, 'getVersionApi'])->name('api.version');
    
    // Activity Logs
    Route::prefix('activity-logs')->name('activity-logs.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\SystemController::class, 'activityLogs'])->name('index');
        Route::get('/admin', [\App\Http\Controllers\Admin\SystemController::class, 'adminActivityLogs'])->name('admin');
        Route::get('/customer', [\App\Http\Controllers\Admin\SystemController::class, 'customerActivityLogs'])->name('customer');
        Route::post('/export', [\App\Http\Controllers\Admin\SystemController::class, 'exportActivityLogs'])->name('export');
        Route::delete('/destroy', [\App\Http\Controllers\Admin\SystemController::class, 'destroyActivityLogs'])->name('destroy');
        Route::post('/clear', [\App\Http\Controllers\Admin\SystemController::class, 'clearActivityLogs'])->name('clear');
    });
    
    // Data Export/Import
    Route::prefix('data-export')->name('data-export.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\DataExportImportController::class, 'index'])->name('index');
        Route::get('/export', [\App\Http\Controllers\Admin\DataExportImportController::class, 'export'])->name('export');
        Route::post('/import', [\App\Http\Controllers\Admin\DataExportImportController::class, 'import'])->name('import');
        Route::get('/template', [\App\Http\Controllers\Admin\DataExportImportController::class, 'downloadTemplate'])->name('template');
    });
});

// POS Management
Route::prefix('pos')->middleware('permission:pos')->name('pos.')->group(function () {
    // Terminal - Main POS interface
    Route::get('/terminal', [\App\Http\Controllers\Admin\POSController::class, 'terminal'])->name('terminal');
    
    // Product search API
    Route::get('/products/search', [\App\Http\Controllers\Admin\POSController::class, 'searchProducts'])->name('products.search');
    
    // Cart management
    Route::post('/cart/add', [\App\Http\Controllers\Admin\POSController::class, 'addToCart'])->name('cart.add');
    Route::post('/cart/update', [\App\Http\Controllers\Admin\POSController::class, 'updateCartItem'])->name('cart.update');
    Route::post('/cart/remove', [\App\Http\Controllers\Admin\POSController::class, 'removeFromCart'])->name('cart.remove');
    Route::post('/cart/clear', [\App\Http\Controllers\Admin\POSController::class, 'clearCart'])->name('cart.clear');
    Route::post('/cart/discount', [\App\Http\Controllers\Admin\POSController::class, 'applyDiscount'])->name('cart.discount');
    
    // Checkout
    Route::post('/checkout', [\App\Http\Controllers\Admin\POSController::class, 'processCheckout'])->name('checkout');
    
    // Cash Register
    Route::get('/cash-register', [\App\Http\Controllers\Admin\POSController::class, 'cashRegister'])->name('cash-register');
    
    // Reports
    Route::get('/reports', [\App\Http\Controllers\Admin\POSController::class, 'reports'])->name('reports');
});

// Multi-Store Management - Specific routes BEFORE wildcard routes to avoid 404 errors
Route::prefix('multi-store')->middleware('permission:multistore')->name('multi-store.')->group(function () {
    // Index - must be first
    Route::get('/', [\App\Http\Controllers\Admin\StoreController::class, 'index'])->name('index');
    // Specific routes BEFORE wildcard
    Route::get('/create', [\App\Http\Controllers\Admin\StoreController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\StoreController::class, 'store'])->name('store');
    Route::get('/{store}/edit', [\App\Http\Controllers\Admin\StoreController::class, 'edit'])->name('edit');
    Route::put('/{store}', [\App\Http\Controllers\Admin\StoreController::class, 'update'])->name('update');
    Route::delete('/{store}', [\App\Http\Controllers\Admin\StoreController::class, 'destroy'])->name('destroy');
    Route::post('/{store}/toggle-status', [\App\Http\Controllers\Admin\StoreController::class, 'toggleStatus'])->name('toggle-status');
    Route::post('/{store}/set-default', [\App\Http\Controllers\Admin\StoreController::class, 'setDefault'])->name('set-default');
    Route::post('/bulk-action', [\App\Http\Controllers\Admin\StoreController::class, 'bulkAction'])->name('bulk-action');
    Route::get('/get-stores', [\App\Http\Controllers\Admin\StoreController::class, 'getStores'])->name('get-stores');
    // Show route - must be after specific routes
    Route::get('/{store}', [\App\Http\Controllers\Admin\StoreController::class, 'show'])->name('show');
});

// Addon Manager - Specific routes BEFORE wildcard routes to avoid 404 errors
Route::prefix('addons')->middleware('permission:addon')->name('addons.')->group(function () {
    // Index - must be first
    Route::get('/', [\App\Http\Controllers\Admin\AddonController::class, 'index'])->name('index');
    // Specific routes BEFORE wildcard
    Route::get('/install', [\App\Http\Controllers\Admin\AddonController::class, 'install'])->name('install');
    Route::post('/install', [\App\Http\Controllers\Admin\AddonController::class, 'processInstall'])->name('install.process');
    Route::get('/templates', [\App\Http\Controllers\Admin\AddonController::class, 'templates'])->name('templates');
    Route::post('/templates/install', [\App\Http\Controllers\Admin\AddonController::class, 'installFromTemplate'])->name('templates.install');
    Route::post('/bulk-action', [\App\Http\Controllers\Admin\AddonController::class, 'bulkAction'])->name('bulk-action');
    Route::post('/reorder', [\App\Http\Controllers\Admin\AddonController::class, 'reorder'])->name('reorder');
    // Wildcard routes AFTER specific routes
    Route::get('/{id}/edit', [\App\Http\Controllers\Admin\AddonController::class, 'edit'])->name('edit');
    Route::put('/{id}', [\App\Http\Controllers\Admin\AddonController::class, 'update'])->name('update');
    Route::post('/{id}/activate', [\App\Http\Controllers\Admin\AddonController::class, 'activate'])->name('activate');
    Route::post('/{id}/deactivate', [\App\Http\Controllers\Admin\AddonController::class, 'deactivate'])->name('deactivate');
    Route::post('/{id}/toggle', [\App\Http\Controllers\Admin\AddonController::class, 'toggleStatus'])->name('toggle');
    Route::post('/{id}/settings', [\App\Http\Controllers\Admin\AddonController::class, 'settings'])->name('settings');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\AddonController::class, 'destroy'])->name('destroy');
});

// Affiliate Management
Route::prefix('affiliate')->middleware('permission:affiliate')->name('affiliate.')->group(function () {
    // Affiliate Users
    Route::prefix('users')->name('users.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AffiliateController::class, 'users'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\AffiliateController::class, 'showUser'])->name('show');
        Route::post('/{id}/approve', [\App\Http\Controllers\Admin\AffiliateController::class, 'approveUser'])->name('approve');
        Route::post('/{id}/suspend', [\App\Http\Controllers\Admin\AffiliateController::class, 'suspendUser'])->name('suspend');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\AffiliateController::class, 'destroyUser'])->name('destroy');
    });

    // Affiliate Configuration
    Route::get('/configuration', [\App\Http\Controllers\Admin\AffiliateController::class, 'configuration'])->name('configuration');
    Route::post('/configuration', [\App\Http\Controllers\Admin\AffiliateController::class, 'updateConfiguration'])->name('configuration.update');

    // Affiliate Payouts
    Route::get('/payouts', [\App\Http\Controllers\Admin\AffiliateController::class, 'payouts'])->name('payouts');
    Route::post('/payouts/{id}/approve', [\App\Http\Controllers\Admin\AffiliateController::class, 'approvePayout'])->name('payouts.approve');
    Route::post('/payouts/{id}/reject', [\App\Http\Controllers\Admin\AffiliateController::class, 'rejectPayout'])->name('payouts.reject');

    // Affiliate Requests
    Route::get('/requests', [\App\Http\Controllers\Admin\AffiliateController::class, 'requests'])->name('requests');
    Route::post('/requests/{id}/approve', [\App\Http\Controllers\Admin\AffiliateController::class, 'approveRequest'])->name('requests.approve');
    Route::post('/requests/{id}/reject', [\App\Http\Controllers\Admin\AffiliateController::class, 'rejectRequest'])->name('requests.reject');

    // Affiliate Categories
    Route::prefix('categories')->name('categories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AffiliateCategoryController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\AffiliateCategoryController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AffiliateCategoryController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\AffiliateCategoryController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\AffiliateCategoryController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\AffiliateCategoryController::class, 'destroy'])->name('destroy');
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\AffiliateCategoryController::class, 'bulkAction'])->name('bulk-action');
    });

    // Affiliate Products
    Route::prefix('products')->name('products.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AffiliateProductController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\AffiliateProductController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AffiliateProductController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Admin\AffiliateProductController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\AffiliateProductController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\AffiliateProductController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\AffiliateProductController::class, 'destroy'])->name('destroy');
    });

    // Affiliate Links
    Route::prefix('links')->name('links.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AffiliateLinkController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\AffiliateLinkController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AffiliateLinkController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\AffiliateLinkController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\AffiliateLinkController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\AffiliateLinkController::class, 'destroy'])->name('destroy');
    });

    // Affiliate Banners
    Route::prefix('banners')->name('banners.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AffiliateBannerController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\AffiliateBannerController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\AffiliateBannerController::class, 'store'])->name('store');
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\AffiliateBannerController::class, 'bulkAction'])->name('bulk-action');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\AffiliateBannerController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\AffiliateBannerController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\AffiliateBannerController::class, 'destroy'])->name('destroy');
    });

    // Affiliate Reports
    Route::get('/reports', [\App\Http\Controllers\Admin\AffiliateController::class, 'reports'])->name('reports');
    Route::get('/reports/export', [\App\Http\Controllers\Admin\AffiliateController::class, 'exportReports'])->name('reports.export');

    // Withdrawal Requests
    Route::prefix('withdrawals')->name('withdrawals.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\AffiliateWithdrawalController::class, 'index'])->name('index');
        Route::get('/{id}', [\App\Http\Controllers\Admin\AffiliateWithdrawalController::class, 'show'])->name('show');
        Route::post('/bulk', [\App\Http\Controllers\Admin\AffiliateWithdrawalController::class, 'bulk'])->name('bulk');
        Route::post('/{id}/approve', [\App\Http\Controllers\Admin\AffiliateWithdrawalController::class, 'approve'])->name('approve');
        Route::post('/{id}/reject', [\App\Http\Controllers\Admin\AffiliateWithdrawalController::class, 'reject'])->name('reject');
    });
});

// Blog Categories
Route::middleware('permission:content')->group(function () {
    Route::prefix('blog-categories')->name('blog-categories.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BlogCategoryController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\BlogCategoryController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\BlogCategoryController::class, 'store'])->name('store');
        Route::get('/{blogCategory}/edit', [\App\Http\Controllers\Admin\BlogCategoryController::class, 'edit'])->name('edit');
        Route::put('/{blogCategory}', [\App\Http\Controllers\Admin\BlogCategoryController::class, 'update'])->name('update');
        Route::delete('/{blogCategory}', [\App\Http\Controllers\Admin\BlogCategoryController::class, 'destroy'])->name('destroy');
        Route::post('/{blogCategory}/toggle-status', [\App\Http\Controllers\Admin\BlogCategoryController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\BlogCategoryController::class, 'bulkAction'])->name('bulk-action');
    });

    // Blog Tags
    Route::prefix('blog-tags')->name('blog-tags.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\BlogTagController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\BlogTagController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\BlogTagController::class, 'store'])->name('store');
        Route::get('/{blogTag}/edit', [\App\Http\Controllers\Admin\BlogTagController::class, 'edit'])->name('edit');
        Route::put('/{blogTag}', [\App\Http\Controllers\Admin\BlogTagController::class, 'update'])->name('update');
        Route::delete('/{blogTag}', [\App\Http\Controllers\Admin\BlogTagController::class, 'destroy'])->name('destroy');
        Route::post('/{blogTag}/toggle-status', [\App\Http\Controllers\Admin\BlogTagController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\BlogTagController::class, 'bulkAction'])->name('bulk-action');
    });

    // FAQs Management
    Route::get('/faqs', [\App\Http\Controllers\Admin\FaqController::class, 'index'])->name('faqs.index');
    Route::get('/faqs/create', [\App\Http\Controllers\Admin\FaqController::class, 'create'])->name('faqs.create');
    Route::post('/faqs', [\App\Http\Controllers\Admin\FaqController::class, 'store'])->name('faqs.store');
    Route::get('/faqs/{faq}/edit', [\App\Http\Controllers\Admin\FaqController::class, 'edit'])->name('faqs.edit');
    Route::put('/faqs/{faq}', [\App\Http\Controllers\Admin\FaqController::class, 'update'])->name('faqs.update');
    Route::delete('/faqs/{faq}', [\App\Http\Controllers\Admin\FaqController::class, 'destroy'])->name('faqs.destroy');
    Route::post('/faqs/toggle-status/{faq}', [\App\Http\Controllers\Admin\FaqController::class, 'toggleStatus'])->name('faqs.toggle-status');
    Route::post('/faqs/bulk-action', [\App\Http\Controllers\Admin\FaqController::class, 'bulkAction'])->name('faqs.bulk-action');
    Route::post('/faqs/reorder', [\App\Http\Controllers\Admin\FaqController::class, 'reorder'])->name('faqs.reorder');

    // Form Builder
    Route::get('/form-builder', [FormBuilderController::class, 'index'])->name('form-builder.index');
    Route::get('/form-builder/create', [FormBuilderController::class, 'create'])->name('form-builder.create');
    Route::post('/form-builder', [FormBuilderController::class, 'store'])->name('form-builder.store');
    Route::get('/form-builder/{id}/duplicate', [FormBuilderController::class, 'duplicate'])->name('form-builder.duplicate');
    Route::post('/form-builder/{id}/toggle-status', [FormBuilderController::class, 'toggleStatus'])->name('form-builder.toggle-status');
    Route::post('/form-builder/{id}/fields', [FormBuilderController::class, 'storeField'])->name('form-builder.fields.store');
    Route::get('/form-builder/{id}/fields/{fieldId}', [FormBuilderController::class, 'getField'])->name('form-builder.fields.get');
    Route::put('/form-builder/{id}/fields/{fieldId}', [FormBuilderController::class, 'updateField'])->name('form-builder.fields.update');
    Route::delete('/form-builder/{id}/fields/{fieldId}', [FormBuilderController::class, 'destroyField'])->name('form-builder.fields.destroy');
    Route::post('/form-builder/{id}/fields/reorder', [FormBuilderController::class, 'reorderFields'])->name('form-builder.fields.reorder');
    Route::get('/form-builder/{id}/submissions', [FormBuilderController::class, 'submissions'])->name('form-builder.submissions');
    Route::get('/form-builder/{id}/submissions/export', [FormBuilderController::class, 'exportSubmissions'])->name('form-builder.submissions.export');
    Route::get('/form-builder/{id}/submissions/{submissionId}', [FormBuilderController::class, 'showSubmission'])->name('form-builder.submissions.show');
    Route::post('/form-builder/{id}/submissions/{submissionId}/toggle-read', [FormBuilderController::class, 'toggleReadStatus'])->name('form-builder.submissions.toggle-read');
    Route::post('/form-builder/{id}/submissions/{submissionId}/note', [FormBuilderController::class, 'addNote'])->name('form-builder.submissions.note');
    Route::delete('/form-builder/{id}/submissions/{submissionId}', [FormBuilderController::class, 'destroySubmission'])->name('form-builder.submissions.destroy');
    Route::get('/form-builder/{id}', [FormBuilderController::class, 'show'])->name('form-builder.show');
    Route::get('/form-builder/{id}/edit', [FormBuilderController::class, 'edit'])->name('form-builder.edit');
    Route::put('/form-builder/{id}', [FormBuilderController::class, 'update'])->name('form-builder.update');
    Route::delete('/form-builder/{id}', [FormBuilderController::class, 'destroy'])->name('form-builder.destroy');
}); // end permission:content group

// Menu Builder
Route::middleware('permission:appearance')->group(function () {
    Route::prefix('menus')->name('menus.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'index'])->name('index');
    Route::get('/create', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'create'])->name('create');
    Route::post('/', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'store'])->name('store');

    // Link options route - must be before {id} routes to avoid conflicts
    Route::get('/link-options', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'getLinkOptions'])->name('link-options');

    // Menu items routes - must be before {id} routes to avoid conflicts
    Route::post('/{id}/items', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'storeItem'])->name('items.store');
    Route::get('/{id}/items', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'items'])->name('items');
    Route::put('/{id}/items/{itemId}', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'updateItem'])->name('items.update');
    Route::delete('/{id}/items/{itemId}', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'destroyItem'])->name('items.destroy');
    Route::post('/{id}/items/reorder', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'reorderItems'])->name('items.reorder');
    Route::post('/{id}/items/{itemId}/toggle', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'toggleItemStatus'])->name('items.toggle');

    // Toggle status
    Route::post('/{id}/toggle', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'toggleStatus'])->name('toggle');

    // CRUD routes - must be after specific routes
    Route::get('/{id}/edit', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'edit'])->name('edit');
    Route::put('/{id}', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'update'])->name('update');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\MenuBuilderController::class, 'destroy'])->name('destroy');
});
}); // end permission:appearance group for menus

// Widget Manager
Route::middleware('permission:content')->group(function () {
    Route::prefix('content/widgets')->name('content.widgets.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\WidgetController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Admin\WidgetController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Admin\WidgetController::class, 'store'])->name('store');
        Route::get('/{id}/edit', [\App\Http\Controllers\Admin\WidgetController::class, 'edit'])->name('edit');
        Route::put('/{id}', [\App\Http\Controllers\Admin\WidgetController::class, 'update'])->name('update');
        Route::delete('/{id}', [\App\Http\Controllers\Admin\WidgetController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [\App\Http\Controllers\Admin\WidgetController::class, 'reorder'])->name('reorder');
        Route::post('/toggle-status/{id}', [\App\Http\Controllers\Admin\WidgetController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/toggle-featured/{id}', [\App\Http\Controllers\Admin\WidgetController::class, 'toggleFeatured'])->name('toggle-featured');
        Route::post('/bulk-action', [\App\Http\Controllers\Admin\WidgetController::class, 'bulkAction'])->name('bulk-action');
    });
}); // end permission:content group for widgets

// API Keys & Integrations
Route::middleware('permission:settings')->group(function () {
    Route::prefix('api-keys')->name('api-keys.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\ApiKeyController::class, 'index'])->name('index');
    Route::post('/', [\App\Http\Controllers\Admin\ApiKeyController::class, 'store'])->name('store');
    Route::put('/{id}', [\App\Http\Controllers\Admin\ApiKeyController::class, 'update'])->name('update');
    Route::delete('/{id}', [\App\Http\Controllers\Admin\ApiKeyController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/regenerate', [\App\Http\Controllers\Admin\ApiKeyController::class, 'regenerate'])->name('regenerate');
    Route::post('/{id}/toggle', [\App\Http\Controllers\Admin\ApiKeyController::class, 'toggle'])->name('toggle');
    Route::get('/secret/{id}', [\App\Http\Controllers\Admin\ApiKeyController::class, 'showSecret'])->name('secret');
    
    // Webhooks
    Route::get('/webhooks', [\App\Http\Controllers\Admin\ApiKeyController::class, 'index'])->name('webhooks.index');
    Route::post('/webhooks', [\App\Http\Controllers\Admin\ApiKeyController::class, 'storeWebhook'])->name('webhooks.store');
    Route::put('/webhooks/{id}', [\App\Http\Controllers\Admin\ApiKeyController::class, 'updateWebhook'])->name('webhooks.update');
    Route::delete('/webhooks/{id}', [\App\Http\Controllers\Admin\ApiKeyController::class, 'destroyWebhook'])->name('webhooks.destroy');
    Route::post('/webhooks/{id}/test', [\App\Http\Controllers\Admin\ApiKeyController::class, 'testWebhook'])->name('webhooks.test');
    Route::post('/webhooks/{id}/toggle', [\App\Http\Controllers\Admin\ApiKeyController::class, 'toggleWebhook'])->name('webhooks.toggle');
});
}); // end permission:settings group for api-keys

// Notifications - API Routes for AJAX (must be before wildcard routes)
Route::prefix('notifications')->name('notifications.')->group(function () {
    // Get recent notifications for dropdown
    Route::get('/recent', [\App\Http\Controllers\Admin\NotificationController::class, 'recent'])->name('recent');
    
    // Get notification counts
    Route::get('/counts', [\App\Http\Controllers\Admin\NotificationController::class, 'counts'])->name('counts');
    
    // Mark notifications as read
    Route::post('/mark-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markRead'])->name('mark-read');
    
    // Mark single notification as read
    Route::post('/{id}/read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAsRead'])->name('read');
    
    // Mark all as read
    Route::post('/mark-all-read', [\App\Http\Controllers\Admin\NotificationController::class, 'markAllAsRead'])->name('mark-all-read');
    
    // Delete notification
    Route::delete('/{id}', [\App\Http\Controllers\Admin\NotificationController::class, 'destroy'])->name('destroy');
    
    // Clear read notifications
    Route::delete('/clear-read', [\App\Http\Controllers\Admin\NotificationController::class, 'clearRead'])->name('clear-read');
    
    // Clear all notifications
    Route::delete('/clear-all', [\App\Http\Controllers\Admin\NotificationController::class, 'clearAll'])->name('clear-all');
    
    // Create test notification
    Route::post('/test', [\App\Http\Controllers\Admin\NotificationController::class, 'createTestNotification'])->name('test');
    
    // Main notifications list page
    Route::get('/', [\App\Http\Controllers\Admin\NotificationController::class, 'index'])->name('index');
});

// Settings - Additional Routes
Route::prefix('settings')->name('settings.')->group(function () {
});
