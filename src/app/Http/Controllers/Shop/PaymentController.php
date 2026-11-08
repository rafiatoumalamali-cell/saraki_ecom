<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private readonly PaymentService $payments) {}

    /**
     * Where Paystack sends the browser after the hosted checkout. The reference in
     * the query string is only a lookup key; the transaction is verified with the
     * gateway before anything is marked paid.
     */
    public function callback(Request $request, string $locale): RedirectResponse
    {
        $reference = (string) ($request->query('reference') ?: $request->query('trxref') ?: '');

        if ($reference === '') {
            return redirect()
                ->route('orders.index', $locale)
                ->with('error', __('shop.payment.missing_reference'));
        }

        $payment = $this->payments->completeFromCallback($reference);

        if ($payment === null) {
            return redirect()
                ->route('orders.index', $locale)
                ->with('error', __('shop.payment.unknown_reference'));
        }

        $target = route('orders.show', [
            'locale' => $locale,
            'order' => $payment->order->order_number,
        ]);

        return $payment->isSuccessful()
            ? redirect($target)->with('success', __('shop.payment.received'))
            : redirect($target)->with('error', __('shop.payment.failed'));
    }
}
