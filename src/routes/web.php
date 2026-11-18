<?php

use App\Http\Controllers\Admin\AppointmentController as AdminAppointmentController;
use App\Http\Controllers\Admin\AppointmentServiceController;
use App\Http\Controllers\Admin\BlogPostController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\ContentController;
use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GalleryController as AdminGalleryController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\PageController as AdminPageController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductImageController;
use App\Http\Controllers\Admin\ProductVariantController;
use App\Http\Controllers\Admin\RefundController;
use App\Http\Controllers\Admin\ScheduleController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Shop\AppointmentController;
use App\Http\Controllers\Shop\BlogController;
use App\Http\Controllers\Shop\CartController;
use App\Http\Controllers\Shop\CatalogueController;
use App\Http\Controllers\Shop\CheckoutController;
use App\Http\Controllers\Shop\ContactController;
use App\Http\Controllers\Shop\FaqController;
use App\Http\Controllers\Shop\MadeToMeasureController;
use App\Http\Controllers\Shop\GalleryController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Controllers\Shop\OrderController;
use App\Http\Controllers\Shop\PageController;
use App\Http\Controllers\Shop\PaymentController;
use App\Http\Controllers\Shop\SitemapController;
use App\Http\Controllers\Shop\WishlistController;
use App\Http\Controllers\Webhooks\PaystackWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::pattern('locale', 'fr|en');

/*
|--------------------------------------------------------------------------
| Crawlers — sitemap and robots are generated so the Sitemap line always
| carries the real domain instead of a placeholder baked into a static file.
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', SitemapController::class)->name('sitemap');

Route::get('/robots.txt', function () {
    $disallow = ['/admin', '/dashboard', '/profile', '/login', '/register',
        '/forgot-password', '/reset-password', '/verify-email', '/confirm-password'];

    foreach (config('saraki.supported_locales') as $locale) {
        array_push($disallow,
            "/{$locale}/cart", "/{$locale}/checkout", "/{$locale}/orders",
            "/{$locale}/wishlist", "/{$locale}/appointments/book", "/{$locale}/payment");
    }

    $body = "User-agent: *\n"
        .collect($disallow)->map(fn (string $path) => "Disallow: {$path}")->implode("\n")
        ."\n\nSitemap: ".route('sitemap')."\n";

    return response($body, 200, ['Content-Type' => 'text/plain; charset=UTF-8']);
})->name('robots');

/*
|--------------------------------------------------------------------------
| Storefront (locale prefixed)
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('home', app()->getLocale()))->name('storefront');

Route::prefix('{locale}')->group(function () {
    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('catalogue', [CatalogueController::class, 'index'])->name('shop.index');
    Route::get('catalogue/{product:slug}', [CatalogueController::class, 'show'])->name('shop.show');
    Route::post('catalogue/{product:slug}/sur-mesure', [MadeToMeasureController::class, 'store'])
        ->middleware('throttle:contact')
        ->name('products.tailoring');

    /*
    |----------------------------------------------------------------------
    | Appointments — anyone can look; signing in is required to book
    |----------------------------------------------------------------------
    */
    Route::get('appointments', [AppointmentController::class, 'index'])->name('appointments.index');

    /*
    |----------------------------------------------------------------------
    | Content — the showroom gallery, the journal and the editable pages
    |----------------------------------------------------------------------
    */
    Route::get('gallery', [GalleryController::class, 'index'])->name('gallery.index');

    Route::get('journal', [BlogController::class, 'index'])->name('journal.index');
    Route::get('journal/{post}', [BlogController::class, 'show'])->name('journal.show');

    Route::get('faq', [FaqController::class, 'index'])->name('faq.index');

    Route::get('pages/{page}', [PageController::class, 'show'])->name('pages.show');

    /*
    |----------------------------------------------------------------------
    | Contact — one form, rate limited so a script cannot flood the inbox
    |----------------------------------------------------------------------
    */
    Route::get('contact', [ContactController::class, 'show'])->name('contact.show');
    Route::post('contact', [ContactController::class, 'store'])
        ->middleware('throttle:contact')
        ->name('contact.store');

    /*
    |----------------------------------------------------------------------
    | Basket — a guest can fill one; it follows them after signing in
    |----------------------------------------------------------------------
    */
    Route::get('cart', [CartController::class, 'index'])->name('cart.index');
    Route::post('cart', [CartController::class, 'store'])->name('cart.store');
    Route::patch('cart/{item}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('cart/{item}', [CartController::class, 'destroy'])->name('cart.destroy');

    /*
    |----------------------------------------------------------------------
    | Checkout and orders — signing in is required before an order is placed
    |----------------------------------------------------------------------
    */
    Route::middleware('auth')->group(function () {
        Route::get('checkout', [CheckoutController::class, 'show'])->name('checkout.show');
        Route::post('checkout', [CheckoutController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('checkout.store');

        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('orders/{order}/pay', [OrderController::class, 'pay'])
            ->middleware('throttle:10,1')
            ->name('orders.pay');

        Route::get('appointments/book', [AppointmentController::class, 'create'])->name('appointments.create');
        Route::post('appointments', [AppointmentController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('appointments.store');
        Route::delete('appointments/{appointment}', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

        Route::get('wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
        Route::post('wishlist', [WishlistController::class, 'store'])->name('wishlist.store');
        Route::delete('wishlist/{product}', [WishlistController::class, 'destroy'])->whereNumber('product')->name('wishlist.destroy');
    });

    // Paystack sends the browser back here; the reference is re-verified server-side.
    Route::get('payment/callback', [PaymentController::class, 'callback'])
        ->middleware('throttle:30,1')
        ->name('payment.callback');
});

Route::post('/locale/{locale}', function (Request $request, string $locale) {
    $request->session()->put('locale', $locale);

    // Keep the account preference in step so the choice follows the customer
    // to other devices; guests only have the session.
    $user = $request->user();
    if ($user !== null && $user->locale !== $locale) {
        $user->forceFill(['locale' => $locale])->save();
    }

    // Swap the locale prefix so the visitor lands on the same page in the new
    // language; SetLocale would otherwise keep honouring the old URL prefix.
    $referer = (string) $request->headers->get('referer');
    $base = rtrim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');
    $path = (string) (parse_url($referer, PHP_URL_PATH) ?: '');
    $query = parse_url($referer, PHP_URL_QUERY);

    // The school host installs the app under /~user/, so the referer carries a
    // base path in front of the locale; strip it before matching the prefix.
    if ($base !== '' && str_starts_with($path, $base)) {
        $path = substr($path, strlen($base)) ?: '/';
    }

    foreach (config('saraki.supported_locales') as $supported) {
        if ($path === '/'.$supported || str_starts_with($path, '/'.$supported.'/')) {
            $rest = substr($path, strlen($supported) + 1);

            return redirect($base.'/'.$locale.$rest.($query ? '?'.$query : ''));
        }
    }

    // Non-prefixed pages (login, dashboard, admin) have no prefix to swap —
    // the session value drives the language on the next render.
    return back();
})->name('locale.update');

/*
|--------------------------------------------------------------------------
| Customer account
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->get('/dashboard', function (Request $request) {
    return view('dashboard', [
        'wishlistCount' => $request->user()->wishlist()->count(),
        'recentOrders' => $request->user()->orders()->latest()->limit(3)->get(),
    ]);
})->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/profile/photo', [ProfileController::class, 'updatePhoto'])->name('profile.photo.update');
    Route::delete('/profile/photo', [ProfileController::class, 'removePhoto'])->name('profile.photo.destroy');

    Route::patch('/profile/measurements', [ProfileController::class, 'updateMeasurements'])->name('profile.measurements.update');
});

/*
|--------------------------------------------------------------------------
| Admin
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('home');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    /*
    |----------------------------------------------------------------------
    | Appointments — requests, the month planner, services and opening hours
    |----------------------------------------------------------------------
    */
    Route::prefix('appointments')->name('appointments.')->group(function () {
        Route::get('/', [AdminAppointmentController::class, 'index'])->name('index');
        Route::get('calendar', [AdminAppointmentController::class, 'calendar'])->name('calendar');
        Route::get('{appointment}', [AdminAppointmentController::class, 'show'])->whereNumber('appointment')->name('show');
        Route::patch('{appointment}/status', [AdminAppointmentController::class, 'updateStatus'])->whereNumber('appointment')->name('status');
    });

    Route::prefix('services')->name('services.')->group(function () {
        Route::get('/', [AppointmentServiceController::class, 'index'])->name('index');
        Route::get('create', [AppointmentServiceController::class, 'create'])->name('create');
        Route::post('/', [AppointmentServiceController::class, 'store'])->name('store');
        Route::get('{service}/edit', [AppointmentServiceController::class, 'edit'])->whereNumber('service')->name('edit');
        Route::patch('{service}', [AppointmentServiceController::class, 'update'])->whereNumber('service')->name('update');
        Route::patch('{service}/toggle', [AppointmentServiceController::class, 'toggle'])->whereNumber('service')->name('toggle');
        Route::delete('{service}', [AppointmentServiceController::class, 'destroy'])->whereNumber('service')->name('destroy');
    });

    Route::get('schedule', [ScheduleController::class, 'index'])->name('schedule.index');
    Route::put('schedule', [ScheduleController::class, 'update'])->name('schedule.update');
    Route::post('schedule/blocked-dates', [ScheduleController::class, 'storeBlockedDate'])->name('schedule.blocked-dates.store');
    Route::delete('schedule/blocked-dates/{blockedDate}', [ScheduleController::class, 'destroyBlockedDate'])->whereNumber('blockedDate')->name('schedule.blocked-dates.destroy');

    /*
    |----------------------------------------------------------------------
    | Catalogue — products, their photos and their variants
    |----------------------------------------------------------------------
    */
    Route::prefix('catalogue')->name('catalogue.')->group(function () {
        Route::get('products', [ProductController::class, 'index'])->name('products.index');
        Route::get('products/create', [ProductController::class, 'create'])->name('products.create');
        Route::post('products', [ProductController::class, 'store'])->name('products.store');
        Route::get('products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
        Route::patch('products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('products/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::get('products/{product}/images', [ProductImageController::class, 'index'])->name('products.images.index');
        Route::post('products/{product}/images', [ProductImageController::class, 'store'])->name('products.images.store');
        Route::patch('products/{product}/images/order', [ProductImageController::class, 'order'])->name('products.images.order');
        Route::patch('products/{product}/images/{image}', [ProductImageController::class, 'update'])->name('products.images.update');
        Route::delete('products/{product}/images/{image}', [ProductImageController::class, 'destroy'])->name('products.images.destroy');
        Route::patch('products/{product}/images/{image}/primary', [ProductImageController::class, 'primary'])->name('products.images.primary');

        Route::get('products/{product}/variants', [ProductVariantController::class, 'index'])->name('products.variants.index');
        Route::post('products/{product}/variants', [ProductVariantController::class, 'store'])->name('products.variants.store');
        Route::post('products/{product}/variants/generate', [ProductVariantController::class, 'generate'])->name('products.variants.generate');
        Route::patch('products/{product}/variants', [ProductVariantController::class, 'batchUpdate'])->name('products.variants.batch');
        Route::delete('products/{product}/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('products.variants.destroy');

        Route::get('categories', [CategoryController::class, 'index'])->name('categories.index');
        Route::get('categories/create', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('categories/{category}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
        Route::patch('categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
        Route::delete('categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');
        Route::patch('categories/{category}/toggle', [CategoryController::class, 'toggle'])->name('categories.toggle');
        Route::patch('categories/{category}/move/{direction}', [CategoryController::class, 'move'])
            ->whereIn('direction', ['up', 'down'])
            ->name('categories.move');
    });

    /*
    |----------------------------------------------------------------------
    | Content — gallery, journal and the pages customers read
    |----------------------------------------------------------------------
    */
    Route::prefix('content')->name('content.')->group(function () {
        Route::get('/', [ContentController::class, 'index'])->name('index');

        Route::get('gallery', [AdminGalleryController::class, 'index'])->name('gallery.index');
        Route::get('gallery/create', [AdminGalleryController::class, 'create'])->name('gallery.create');
        Route::post('gallery', [AdminGalleryController::class, 'store'])->name('gallery.store');
        Route::get('gallery/{gallery}/edit', [AdminGalleryController::class, 'edit'])->whereNumber('gallery')->name('gallery.edit');
        Route::patch('gallery/{gallery}', [AdminGalleryController::class, 'update'])->whereNumber('gallery')->name('gallery.update');
        Route::patch('gallery/{gallery}/toggle', [AdminGalleryController::class, 'toggle'])->whereNumber('gallery')->name('gallery.toggle');
        Route::delete('gallery/{gallery}', [AdminGalleryController::class, 'destroy'])->whereNumber('gallery')->name('gallery.destroy');

        // Posts and pages bind by slug on the storefront, so the admin passes the
        // id explicitly rather than teaching the models a second key.
        Route::get('posts', [BlogPostController::class, 'index'])->name('posts.index');
        Route::get('posts/create', [BlogPostController::class, 'create'])->name('posts.create');
        Route::post('posts', [BlogPostController::class, 'store'])->name('posts.store');
        Route::get('posts/{post:id}/edit', [BlogPostController::class, 'edit'])->whereNumber('post')->name('posts.edit');
        Route::patch('posts/{post:id}', [BlogPostController::class, 'update'])->whereNumber('post')->name('posts.update');
        Route::delete('posts/{post:id}', [BlogPostController::class, 'destroy'])->whereNumber('post')->name('posts.destroy');

        Route::get('pages', [AdminPageController::class, 'index'])->name('pages.index');
        Route::get('pages/create', [AdminPageController::class, 'create'])->name('pages.create');
        Route::post('pages', [AdminPageController::class, 'store'])->name('pages.store');
        Route::get('pages/{page:id}/edit', [AdminPageController::class, 'edit'])->whereNumber('page')->name('pages.edit');
        Route::patch('pages/{page:id}', [AdminPageController::class, 'update'])->whereNumber('page')->name('pages.update');
        Route::delete('pages/{page:id}', [AdminPageController::class, 'destroy'])->whereNumber('page')->name('pages.destroy');
    });

    /*
    |----------------------------------------------------------------------
    | Messages — enquiries from the contact form
    |----------------------------------------------------------------------
    */
    Route::get('messages', [ContactMessageController::class, 'index'])->name('messages.index');
    Route::get('messages/{message}', [ContactMessageController::class, 'show'])->whereNumber('message')->name('messages.show');
    Route::patch('messages/{message}/status', [ContactMessageController::class, 'updateStatus'])->whereNumber('message')->name('messages.status');
    Route::delete('messages/{message}', [ContactMessageController::class, 'destroy'])->whereNumber('message')->name('messages.destroy');

    /*
    |----------------------------------------------------------------------
    | Settings — store details, delivery zones, payment and social links
    |----------------------------------------------------------------------
    */
    Route::get('settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingsController::class, 'update'])->name('settings.update');
});

/*
|--------------------------------------------------------------------------
| Admin — orders, payments and refunds
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('orders', [AdminOrderController::class, 'index'])->name('orders.index');
    Route::get('orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
    Route::patch('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('orders/{order}/reconcile', [AdminOrderController::class, 'reconcile'])->name('orders.reconcile');
    Route::post('orders/{order}/refunds', [RefundController::class, 'store'])->name('orders.refunds.store');
});

/*
|--------------------------------------------------------------------------
| Webhooks — signed by the provider, so no session and no CSRF token
|--------------------------------------------------------------------------
*/
Route::post('webhooks/paystack', PaystackWebhookController::class)->name('webhooks.paystack');

require __DIR__.'/auth.php';
