<?php

use App\Enums\UserRole;
use App\Models\AppointmentService;
use App\Models\Category;
use App\Models\Product;
use App\Models\Setting;
use App\Models\User;
use App\Models\WorkingHour;

test('the seeder builds a demo store with an admin', function () {
    $this->seed();

    expect(User::where('email', config('saraki.admin_email'))
        ->where('role', UserRole::Admin->value)
        ->count())->toBe(1)
        ->and(Category::count())->toBe(6)
        ->and(Product::count())->toBe(20)
        ->and(Product::featured()->count())->toBe(4)
        ->and(Product::has('variants')->count())->toBe(20)
        ->and(Setting::count())->toBe(10)
        ->and(Setting::firstWhere('key', 'currency')->value)->toBe('GHS')
        ->and(AppointmentService::count())->toBe(3)
        ->and(WorkingHour::count())->toBe(7)
        ->and(WorkingHour::firstWhere('day_of_week', 0)->is_closed)->toBeTrue()
        ->and(WorkingHour::firstWhere('day_of_week', 3)->isOpenDay())->toBeTrue();
});

test('every seeded product resolves a price in GHS', function () {
    $this->seed();

    foreach (Product::with('variants')->get() as $product) {
        expect($product->variants)->not->toBeEmpty()
            ->and((float) $product->variants->first()->price)->toBe((float) $product->base_price);
    }
});

test('seeding twice leaves the demo data unchanged', function () {
    $this->seed();
    $counts = [Product::count(), Setting::count(), User::count()];

    $this->seed();

    expect([Product::count(), Setting::count(), User::count()])->toBe($counts);
});
