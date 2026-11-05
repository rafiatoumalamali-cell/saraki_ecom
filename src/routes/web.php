<?php

use App\Http\Controllers\Admin\CustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::pattern('locale', 'fr|en');

/*
|--------------------------------------------------------------------------
| Storefront (locale prefixed)
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('home', app()->getLocale()))->name('storefront');

Route::prefix('{locale}')->group(function () {
    Route::view('/', 'home')->name('home');
});

Route::post('/locale/{locale}', fn (Request $request) => back())->name('locale.update');

/*
|--------------------------------------------------------------------------
| Customer account
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->get('/dashboard', function () {
    return view('dashboard');
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
$adminSections = 'orders|catalogue|appointments|content|messages|settings';

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () use ($adminSections) {
    Route::get('/', fn () => redirect()->route('admin.dashboard'))->name('home');

    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('customers', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('customers/{customer}', [CustomerController::class, 'show'])->name('customers.show');

    Route::get('{section}', fn (string $section) => view('admin.placeholder', ['section' => $section]))
        ->where('section', $adminSections)
        ->name('section');
});

require __DIR__.'/auth.php';
