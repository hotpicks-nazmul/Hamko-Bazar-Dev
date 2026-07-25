<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\Frontend\LanguageController;
use App\Http\Controllers\Frontend\CurrencyController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application.
|
*/

// Installation Routes (only accessible before installation)
Route::prefix('install')->group(base_path('routes/install.php'));

// Frontend Routes
Route::get('/', [HomeController::class, 'index'])->name('home');

// Language Switcher Route
Route::get('/language/switch', [LanguageController::class, 'switch'])->name('language.switch');

// Store Switcher Route
Route::get('/store/switch/{storeId?}', [\App\Http\Controllers\Frontend\StoreController::class, 'switch'])->name('store.switch');
Route::get('/store/current', [\App\Http\Controllers\Frontend\StoreController::class, 'currentStore'])->name('store.current');
Route::get('/stores', [\App\Http\Controllers\Frontend\StoreController::class, 'getStores'])->name('stores.list');

// Currency Switcher Route
Route::get('/currency/switch/{code}', [CurrencyController::class, 'switch'])->name('currency.switch');

Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('pages.contact');
Route::post('/contact', [PageController::class, 'sendContact'])->name('contact.send');
Route::get('/faq', [PageController::class, 'faq'])->name('faq');
Route::get('/terms', [PageController::class, 'terms'])->name('terms');
Route::get('/privacy', [PageController::class, 'privacy'])->name('privacy');
Route::get('/pages/{slug}', [PageController::class, 'show'])->name('pages.show');

// Newsletter Routes
Route::post('/newsletter/subscribe', [PageController::class, 'newsletterSubscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe', [PageController::class, 'newsletterUnsubscribe'])->name('newsletter.unsubscribe');

// Blog Routes
Route::prefix('blogs')->name('blogs.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Frontend\BlogController::class, 'index'])->name('index');
    Route::get('/{slug}', [\App\Http\Controllers\Frontend\BlogController::class, 'show'])->name('show');
});

// Form Routes (must be before generic pages route to avoid conflicts)
Route::get('/forms', [FormController::class, 'list'])->name('forms.list');
Route::get('/form/{slug}', [FormController::class, 'show'])->name('forms.show');
Route::post('/form/{slug}', [FormController::class, 'submit'])->name('forms.submit');

// Live Chat Route
Route::get('/chat/live', [ChatController::class, 'live'])->name('chat.live');

// Search Routes
Route::get('/search', [SearchController::class, 'index'])->name('search.index');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

// Product Routes
Route::prefix('products')->name('products.')->group(function () {
    Route::get('/', [ProductController::class, 'index'])->name('index');
    Route::get('/category/{slug}', [ProductController::class, 'byCategory'])->name('category');
    Route::get('/{slug}', [ProductController::class, 'show'])->name('show');
});

// Variant Image Route (must be before products routes to prevent slug conflict)
Route::get('/api/product/{product}/variant-image', [ProductController::class, 'getVariantImage'])->name('product.variant-image');

// Category Routes
Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::get('/categories/{slug}', [CategoryController::class, 'show'])->name('categories.show');

// Bundle Routes
Route::prefix('bundles')->name('bundles.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Frontend\BundleController::class, 'index'])->name('index');
    Route::get('/{slug}', [\App\Http\Controllers\Frontend\BundleController::class, 'show'])->name('show');
    Route::post('/add-to-cart/{id}', [\App\Http\Controllers\Frontend\BundleController::class, 'addToCart'])->name('add-to-cart');
});

// Cart Routes
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::post('/remove', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'count'])->name('count');
});

// API Routes
Route::prefix('api')->name('api.')->group(function () {
    Route::get('/cart/count', [CartController::class, 'count'])->name('cart.count');
    Route::get('/cart/items', [CartController::class, 'items'])->name('cart.items');
    Route::post('/cart/update', [CartController::class, 'update'])->name('cart.update');
    Route::post('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
    Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
    Route::get('/wishlist/items', [WishlistController::class, 'items'])->name('wishlist.items');
    Route::post('/chat/send', [ChatController::class, 'send'])->name('chat.send');
    Route::post('/chat/typing', [ChatController::class, 'typing'])->name('chat.typing');
    Route::get('/chat/check-typing', [ChatController::class, 'checkTyping'])->name('chat.check-typing');
    Route::post('/chat/register-guest', [ChatController::class, 'registerGuest'])->name('chat.register-guest');
    Route::get('/chat/check-guest', [ChatController::class, 'checkGuest'])->name('chat.check-guest');
    Route::post('/chat/register-logged-in', [ChatController::class, 'registerLoggedIn'])->name('chat.register-logged-in');
    Route::get('/products/quick-view/{id}', [ProductController::class, 'quickView'])->name('products.quick-view');
    
    // Public System API
    Route::get('/system/version', [\App\Http\Controllers\Admin\SystemController::class, 'getVersionApi'])->name('system.version');
    
    // Addons API
    Route::get('/addons', function() {
        $addons = \App\Models\Addon::active()->orderBy('sort_order')->get();
        return response()->json([
            'addons' => $addons->map(function($addon) {
                return [
                    'id' => $addon->id,
                    'name' => $addon->name,
                    'slug' => $addon->slug,
                    'description' => $addon->description,
                    'version' => $addon->version,
                    'author' => $addon->author,
                    'icon' => $addon->icon,
                    'settings' => $addon->settings,
                ];
            })
        ]);
    })->name('addons.index');

    Route::get('/addons/{slug}', function($slug) {
        $addon = \App\Models\Addon::active()->where('slug', $slug)->first();
        
        if (!$addon) {
            return response()->json(['error' => 'Addon not found'], 404);
        }
        
        return response()->json([
            'addon' => [
                'id' => $addon->id,
                'name' => $addon->name,
                'slug' => $addon->slug,
                'description' => $addon->description,
                'version' => $addon->version,
                'author' => $addon->author,
                'author_website' => $addon->author_website,
                'website' => $addon->website,
                'icon' => $addon->icon,
                'settings' => $addon->settings,
            ]
        ]);
    })->name('addons.show');
});

// Wishlist Routes
Route::prefix('wishlist')->name('wishlist.')->middleware('auth')->group(function () {
    Route::get('/', [WishlistController::class, 'index'])->name('index');
    Route::post('/add', [WishlistController::class, 'add'])->name('add');
    Route::post('/remove', [WishlistController::class, 'remove'])->name('remove');
    Route::post('/toggle', [WishlistController::class, 'toggle'])->name('toggle');
});

// Checkout Routes
// Public routes for AJAX
Route::get('/checkout/get-states', [CheckoutController::class, 'getStates'])->name('checkout.get-states');
Route::get('/checkout/shipping-options', [CheckoutController::class, 'getShippingOptions'])->name('checkout.shipping-options');
Route::get('/checkout/get-cities', [CheckoutController::class, 'getCities'])->name('checkout.get-cities');
Route::get('/checkout/get-areas', [CheckoutController::class, 'getAreas'])->name('checkout.get-areas');

Route::prefix('checkout')->name('checkout.')->group(function () {
    Route::get('/', [CheckoutController::class, 'index'])->name('index');
    Route::post('/process', [CheckoutController::class, 'process'])->name('process');
    Route::get('/success/{order}', [CheckoutController::class, 'success'])->name('success');
    Route::get('/cancel', [CheckoutController::class, 'cancel'])->name('cancel');
});

// Review Routes
Route::prefix('reviews')->name('reviews.')->middleware('auth')->group(function () {
    Route::post('/', [ReviewController::class, 'store'])->name('store');
    Route::put('/{review}', [ReviewController::class, 'update'])->name('update');
    Route::delete('/{review}', [ReviewController::class, 'destroy'])->name('destroy');
    Route::post('/{review}/vote', [ReviewController::class, 'vote'])->name('vote');
});

// Payment Routes
Route::prefix('payment')->name('payment.')->group(function () {
    // SSLCommerz callbacks
    Route::any('/sslcommerz/success', [PaymentController::class, 'sslcommerzSuccess'])->name('sslcommerz.success');
    Route::any('/sslcommerz/fail', [PaymentController::class, 'sslcommerzFail'])->name('sslcommerz.fail');
    Route::any('/sslcommerz/cancel', [PaymentController::class, 'sslcommerzCancel'])->name('sslcommerz.cancel');
    Route::post('/sslcommerz/ipn', [PaymentController::class, 'sslcommerzIpn'])->name('sslcommerz.ipn');

    // bKash callbacks
    Route::get('/bkash/callback/{order_number}', [PaymentController::class, 'bkashCallback'])->name('bkash.callback');
    Route::post('/bkash/execute', [PaymentController::class, 'bkashExecute'])->name('bkash.execute');
    Route::post('/bkash/query', [PaymentController::class, 'bkashQuery'])->name('bkash.query');
});

// Product Q&A Routes
Route::prefix('product-qa')->name('product-qa.')->group(function () {
    Route::post('/', [\App\Http\Controllers\Frontend\ProductQAController::class, 'store'])->name('store');
    Route::post('/{product_qa}/vote', [\App\Http\Controllers\Frontend\ProductQAController::class, 'vote'])->name('vote');
});

// Chat Routes
Route::prefix('chat')->name('chat.')->group(function () {
    Route::get('/', [ChatController::class, 'index'])->name('index');
    Route::post('/send', [ChatController::class, 'send'])->name('send');
    Route::get('/messages', [ChatController::class, 'messages'])->name('messages');
    Route::post('/ai', [ChatController::class, 'aiChat'])->name('ai');
});

// User Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [UserController::class, 'showLogin'])->name('login');
    Route::post('/login', [UserController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
    
    // Phone + Password Login
    Route::post('/login/phone-password', [UserController::class, 'loginWithPhone'])->name('login.phone-password');
    
    // Mobile/Phone Login
    Route::get('/login/phone', [UserController::class, 'showPhoneLogin'])->name('login.phone');
    Route::post('/login/phone/send-otp', [UserController::class, 'sendLoginOtp'])->name('login.phone.send-otp');
    Route::post('/login/phone/verify-otp', [UserController::class, 'verifyLoginOtp'])->name('login.phone.verify-otp');
    
    Route::get('/register', [UserController::class, 'showRegister'])->name('register');
    Route::post('/register', [UserController::class, 'register'])->name('register.post');
    
    // Mobile Registration
    Route::get('/register/mobile', [UserController::class, 'showMobileRegister'])->name('register.mobile');
    Route::post('/register/mobile/send-otp', [UserController::class, 'sendRegistrationOtp'])->name('register.mobile.send-otp');
    Route::post('/register/mobile/verify-otp', [UserController::class, 'verifyRegistrationOtp'])->name('register.mobile.verify-otp');
    
    Route::get('/forgot-password', [UserController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [UserController::class, 'forgotPassword'])->name('password.email');
    Route::get('/reset-password/{token}', [UserController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [UserController::class, 'resetPassword'])->name('password.update');
});

// Social Login Routes
Route::get('/login/{provider}', [UserController::class, 'redirectToProvider'])->name('social.login');
Route::get('/login/{provider}/callback', [UserController::class, 'handleProviderCallback'])->name('social.callback');

// Authenticated User Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [UserController::class, 'logout'])->name('logout');

    // Support Ticket Routes
    Route::prefix('tickets')->name('tickets.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Frontend\TicketController::class, 'index'])->name('index');
        Route::get('/create', [\App\Http\Controllers\Frontend\TicketController::class, 'create'])->name('create');
        Route::post('/', [\App\Http\Controllers\Frontend\TicketController::class, 'store'])->name('store');
        Route::get('/{id}', [\App\Http\Controllers\Frontend\TicketController::class, 'show'])->name('show');
        Route::post('/{id}/reply', [\App\Http\Controllers\Frontend\TicketController::class, 'reply'])->name('reply');
        Route::get('/{id}/close', [\App\Http\Controllers\Frontend\TicketController::class, 'close'])->name('close');
    });

    // Account Routes
    Route::prefix('account')->name('account.')->group(function () {
        Route::get('/', [UserController::class, 'dashboard'])->name('dashboard');
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::get('/notifications', [UserController::class, 'notifications'])->name('notifications');
        Route::post('/notifications', [UserController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('/my-data', [UserController::class, 'myData'])->name('my-data');
        Route::post('/my-data/export', [UserController::class, 'exportMyData'])->name('my-data.export');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
        Route::get('/addresses', [UserController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [UserController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{address}', [UserController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{address}', [UserController::class, 'deleteAddress'])->name('addresses.destroy');
    });
    
    Route::prefix('dashboard')->name('dashboard.')->group(function () {
        Route::get('/', [UserController::class, 'dashboard'])->name('index');
        Route::get('/profile', [UserController::class, 'profile'])->name('profile');
        Route::put('/profile', [UserController::class, 'updateProfile'])->name('profile.update');
        Route::put('/password', [UserController::class, 'updatePassword'])->name('password.update');
        Route::get('/notifications', [UserController::class, 'notifications'])->name('notifications');
        Route::post('/notifications', [UserController::class, 'updateNotifications'])->name('notifications.update');
        Route::get('/my-data', [UserController::class, 'myData'])->name('my-data');
        Route::post('/my-data/export', [UserController::class, 'exportMyData'])->name('my-data.export');
        Route::get('/addresses', [UserController::class, 'addresses'])->name('addresses');
        Route::post('/addresses', [UserController::class, 'storeAddress'])->name('addresses.store');
        Route::put('/addresses/{address}', [UserController::class, 'updateAddress'])->name('addresses.update');
        Route::delete('/addresses/{address}', [UserController::class, 'deleteAddress'])->name('addresses.destroy');
        Route::get('/orders', [OrderController::class, 'index'])->name('orders');
        Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist');
    });
});

// Admin Authentication Routes (General - for existing admins)
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Admin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Admin\AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
    Route::get('/verify-2fa', [\App\Http\Controllers\Admin\AuthController::class, 'showVerifyForm'])->name('verify-2fa');
    Route::post('/verify-2fa', [\App\Http\Controllers\Admin\AuthController::class, 'verifyCode'])->name('verify-2fa.post');
    Route::post('/verify-2fa/resend', [\App\Http\Controllers\Admin\AuthController::class, 'resendCode'])->name('verify-2fa.resend');
    Route::post('/logout', [\App\Http\Controllers\Admin\AuthController::class, 'logout'])->name('logout');
});

// Super Admin Authentication Routes
Route::prefix('super-admin')->name('super-admin.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\SuperAdmin\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\SuperAdmin\AuthController::class, 'login'])->name('login.post')->middleware('throttle:5,1');
    Route::get('/verify-2fa', [\App\Http\Controllers\SuperAdmin\AuthController::class, 'showVerifyForm'])->name('verify-2fa');
    Route::post('/verify-2fa', [\App\Http\Controllers\SuperAdmin\AuthController::class, 'verifyCode'])->name('verify-2fa.post');
    Route::post('/verify-2fa/resend', [\App\Http\Controllers\SuperAdmin\AuthController::class, 'resendCode'])->name('verify-2fa.resend');
    Route::post('/logout', [\App\Http\Controllers\SuperAdmin\AuthController::class, 'logout'])->name('logout');
});

// Super Admin Profile Routes (authenticated)
Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/profile', [\App\Http\Controllers\SuperAdmin\ProfileController::class, 'show'])->name('profile');
    Route::post('/profile', [\App\Http\Controllers\SuperAdmin\ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/password', [\App\Http\Controllers\SuperAdmin\ProfileController::class, 'updatePassword'])->name('profile.password');
});

// Staff Authentication Routes (only login/logout, no dashboard)
Route::prefix('staff')->name('staff.')->group(function () {
    Route::get('/login', [\App\Http\Controllers\Staff\AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [\App\Http\Controllers\Staff\AuthController::class, 'login'])->name('login.post');
    Route::post('/logout', [\App\Http\Controllers\Staff\AuthController::class, 'logout'])->name('logout');
});

// Staff Warehouse Picking Routes (requires staff role + warehouse_id)
Route::prefix('staff/warehouses/{id}/picking')->middleware(['auth', \App\Http\Middleware\StaffMiddleware::class])->name('staff.warehouses.picking.')->group(function () {
    Route::get('/', [\App\Http\Controllers\Admin\WarehouseController::class, 'picking'])->name('index');
    Route::patch('/orders/{orderId}/start-picking', [\App\Http\Controllers\Admin\WarehouseController::class, 'startPicking'])->name('orders.start-picking');
    Route::patch('/orders/{orderId}/mark-packed', [\App\Http\Controllers\Admin\WarehouseController::class, 'markPacked'])->name('orders.mark-packed');
});

// Super Admin Dashboard Routes (requires super_admin role)
Route::prefix('super-admin')->name('super-admin.')->middleware(['auth', 'super_admin'])->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\SuperAdmin\DashboardController::class, 'index'])->name('dashboard');
    // Add other super admin routes here as needed
});

// Admin Routes
Route::prefix('admin')->name('admin.')->middleware(['auth', 'admin', \App\Http\Middleware\CheckSubmenuPermission::class, 'granular_permission'])->group(base_path('routes/admin.php'));

// Sitemap Route
Route::get('/sitemap.xml', function () {
    return response()->file(storage_path('app/sitemap.xml'), ['Content-Type' => 'application/xml']);
})->name('sitemap');

// Run deploy command - hit this URL after uploading updated files
Route::get('/run-deploy', function () {
    $token = request('token');
    if ($token !== 'hamko2026deploy') {
        abort(403, 'Invalid token');
    }
    
    $output = [];
    
    // Clear view cache
    $output[] = '--- Clearing view cache ---';
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    $output[] = \Illuminate\Support\Facades\Artisan::output();
    
    // Run category generator
    $output[] = '--- Generating 3rd-level categories ---';
    \Illuminate\Support\Facades\Artisan::call('categories:generate-third-level');
    $output[] = \Illuminate\Support\Facades\Artisan::output();
    
    // Generate short descriptions
    $output[] = '--- Generating short descriptions ---';
    \Illuminate\Support\Facades\Artisan::call('products:generate-short-descriptions', ['--force' => true]);
    $output[] = \Illuminate\Support\Facades\Artisan::output();
    
    // Optimize
    $output[] = '--- Optimizing ---';
    \Illuminate\Support\Facades\Artisan::call('optimize:clear');
    $output[] = \Illuminate\Support\Facades\Artisan::output();
    \Illuminate\Support\Facades\Artisan::call('optimize');
    $output[] = \Illuminate\Support\Facades\Artisan::output();
    
    $output[] = '--- Done ---';
    return response('<pre>' . implode("\n", $output) . '</pre>', 200);
})->name('run-deploy');
