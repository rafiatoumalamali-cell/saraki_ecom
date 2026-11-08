<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AppointmentStatus;
use App\Enums\ContactStatus;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\ContactMessage;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $paidThisMonth = (float) Payment::where('status', PaymentStatus::Success)
            ->where('paid_at', '>=', now()->startOfMonth())
            ->sum('amount');

        return view('admin.dashboard', [
            'stats' => [
                [
                    'label' => __('admin.stats.orders'),
                    'value' => number_format(Order::count()),
                    'hint' => __('admin.stats.orders_pending', ['count' => Order::where('status', OrderStatus::Pending)->count()]),
                ],
                [
                    'label' => __('admin.stats.revenue_this_month'),
                    'value' => money($paidThisMonth),
                    'hint' => __('admin.stats.paid_payments', ['count' => Payment::where('status', PaymentStatus::Success)->count()]),
                ],
                [
                    'label' => __('admin.stats.appointments'),
                    'value' => number_format(Appointment::whereIn('status', AppointmentStatus::blocking())->count()),
                    'hint' => __('admin.stats.appointments_today', ['count' => Appointment::blocking()->onDate(today())->count()]),
                ],
                [
                    'label' => __('admin.stats.products'),
                    'value' => number_format(Product::active()->count()),
                    'hint' => __('admin.stats.out_of_stock', ['count' => ProductVariant::where('stock_qty', 0)->count()]),
                ],
                [
                    'label' => __('admin.stats.messages'),
                    'value' => number_format(ContactMessage::where('status', ContactStatus::New)->count()),
                    'hint' => __('admin.stats.messages_unread'),
                ],
            ],
            'recentOrders' => Order::with('user')->latest()->limit(5)->get(),
        ]);
    }
}
