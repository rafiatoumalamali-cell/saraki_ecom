<?php

namespace App\Http\Controllers\Admin;

use App\Enums\UserRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q'));

        $customers = User::query()
            ->with('measurements')
            ->where('role', UserRole::Customer)
            ->when($search !== '', function ($query) use ($search) {
                $query->where(fn ($or) => $or
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%"));
            })
            ->orderBy('name')
            ->paginate(25)
            ->withQueryString();

        return view('admin.customers.index', [
            'customers' => $customers,
            'search' => $search,
        ]);
    }

    /**
     * The tailor's reading view: contact details and the measurement sheet.
     */
    public function show(User $customer): View
    {
        abort_unless($customer->role === UserRole::Customer, 404);

        return view('admin.customers.show', [
            'customer' => $customer->load('measurements'),
        ]);
    }
}
