<?php

use App\Enums\AppointmentStatus;
use App\Enums\ContactStatus;
use App\Enums\GalleryCategory;
use App\Enums\OrderPaymentStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PostStatus;
use App\Enums\UserRole;
use App\Models\Appointment;
use App\Models\BlockedDate;
use App\Models\BlogPost;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Category;
use App\Models\ContactMessage;
use App\Models\GalleryItem;
use App\Models\Order;
use App\Models\Page;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkingHour;

test('user factory produces active customers and admins', function () {
    $customer = User::factory()->create();
    $admin = User::factory()->admin()->create();
    $inactive = User::factory()->inactive()->create();

    expect($customer->role)->toBe(UserRole::Customer)
        ->and($customer->isAdmin())->toBeFalse()
        ->and($admin->role)->toBe(UserRole::Admin)
        ->and($admin->isAdmin())->toBeTrue()
        ->and($inactive->is_active)->toBeFalse()
        ->and($customer->locale)->toBe('fr');
});

test('product factory always guarantees a default variant', function () {
    $product = Product::factory()->create();

    expect($product->variants)->toHaveCount(1)
        ->and($product->variants->first()->size)->toBe(ProductVariant::DEFAULT)
        ->and($product->variants->first()->label)->toBe('');
});

test('variant price and label combine product and variant data', function () {
    $product = Product::factory()->create(['base_price' => 500]);
    $variant = ProductVariant::factory()->for($product)->create([
        'size' => 'M',
        'color' => 'Or',
        'price_delta' => 25.5,
        'stock_qty' => 4,
    ]);

    expect($variant->price)->toBe('525.50')
        ->and($variant->label)->toBe('M / Or')
        ->and($variant->isInStock())->toBeTrue();

    $variant->update(['stock_qty' => 0]);
    expect($variant->isInStock())->toBeFalse();
});

test('scopes hide inactive catalogue entries', function () {
    $category = Category::factory()->create();
    Category::factory()->inactive()->create();
    Product::factory()->for($category)->create();
    Product::factory()->for($category)->inactive()->create();
    Product::factory()->for($category)->featured()->create();

    expect(Category::active()->count())->toBe(1)
        ->and(Product::active()->count())->toBe(2)
        ->and(Product::featured()->count())->toBe(1);
});

test('translations fall back to the other locale', function () {
    $product = Product::factory()->create(['name_fr' => 'Boubou', 'name_en' => '']);

    expect($product->translate('name', 'en'))->toBe('Boubou')
        ->and($product->translate('name', 'fr'))->toBe('Boubou');

    $both = Product::factory()->create(['name_fr' => 'Kaba', 'name_en' => 'Kaftan']);
    expect($both->translate('name', 'en'))->toBe('Kaftan');
});

test('cart items derive their product from the variant', function () {
    $variant = ProductVariant::factory()->create();
    $item = CartItem::factory()->for(Cart::factory()->create())->create([
        'variant_id' => $variant->id,
    ]);

    expect($item->product_id)->toBe($variant->product_id);
});

test('guest carts carry a session instead of a user', function () {
    $cart = Cart::factory()->guest()->create();

    expect($cart->user_id)->toBeNull()->and($cart->session_id)->not->toBeNull();
});

test('order factory creates a priced order with items', function () {
    $order = Order::factory()->create();

    expect($order->items)->toHaveCount(2)
        ->and($order->status)->toBe(OrderStatus::Pending)
        ->and($order->payment_status)->toBe(OrderPaymentStatus::Unpaid)
        ->and((float) $order->subtotal)->toBeGreaterThan(0)
        ->and((float) $order->total)->toBe((float) $order->subtotal)
        ->and($order->items->first()->product_name)->not->toBeNull()
        ->and($order->order_number)->toMatch('/^SRK-\d{4}-\d{6}$/');
});

test('payment factory mirrors the order total', function () {
    $order = Order::factory()->create();
    $pending = Payment::factory()->for($order)->create();
    $paid = Payment::factory()->succeeded()->for($order)->create(['gateway_response' => ['id' => 987]]);

    expect($pending->status)->toBe(PaymentStatus::Initialized)
        ->and($pending->isSuccessful())->toBeFalse()
        ->and($paid->status)->toBe(PaymentStatus::Success)
        ->and($paid->isSuccessful())->toBeTrue()
        ->and((float) $paid->amount)->toBe((float) $order->total)
        ->and($paid->currency)->toBe($order->currency)
        ->and($paid->toArray())->not->toHaveKey('gateway_response');
});

test('appointment end time follows the service duration and status stays guarded', function () {
    $service = \App\Models\AppointmentService::factory()->create(['duration_min' => 45]);
    $appointment = Appointment::factory()->for($service, 'service')->create(['start_time' => '10:00:00']);

    expect($appointment->end_time->format('H:i'))->toBe('10:45')
        ->and($appointment->status)->toBe(AppointmentStatus::Pending)
        ->and(Appointment::blocking()->count())->toBe(1);

    $appointment->update(['status' => AppointmentStatus::Cancelled]);
    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Pending);

    $appointment->forceFill(['status' => AppointmentStatus::Cancelled])->save();
    expect(Appointment::blocking()->count())->toBe(0);
});

test('working hours and blocked dates model closures', function () {
    $open = WorkingHour::factory()->create();
    $closed = WorkingHour::factory()->closed()->create();
    $blocked = BlockedDate::factory()->create();

    expect($open->isOpenDay())->toBeTrue()
        ->and($closed->isOpenDay())->toBeFalse()
        ->and($closed->open_time)->toBeNull()
        ->and($blocked->blocked_date->isFuture())->toBeTrue();
});

test('content models expose publication states', function () {
    GalleryItem::factory()->create(['category' => GalleryCategory::Event]);
    GalleryItem::factory()->unpublished()->create();
    BlogPost::factory()->published()->create();
    BlogPost::factory()->create();
    Page::factory()->create();
    Page::factory()->unpublished()->create();

    expect(GalleryItem::published()->count())->toBe(1)
        ->and(BlogPost::published()->count())->toBe(1)
        ->and(BlogPost::published()->first()->status)->toBe(PostStatus::Published)
        ->and(Page::published()->count())->toBe(1);
});

test('contact messages start unread', function () {
    $message = ContactMessage::factory()->create();

    expect($message->status)->toBe(ContactStatus::New);
});

test('settings store typed json values', function () {
    $setting = Setting::create(['group' => 'general', 'key' => 'shipping_zones', 'value' => ['Accra' => 25]]);

    expect($setting->fresh()->value)->toBe(['Accra' => 25]);
});

test('images attach a primary flag to a product', function () {
    $product = Product::factory()->create();
    ProductImage::factory()->for($product)->primary()->create(['sort_order' => 1]);
    ProductImage::factory()->for($product)->create(['sort_order' => 2]);

    expect($product->images)->toHaveCount(2)
        ->and($product->images->first()->sort_order)->toBe(1)
        ->and($product->primaryImage->is_primary)->toBeTrue();
});
