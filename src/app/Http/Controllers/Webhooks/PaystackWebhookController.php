<?php

declare(strict_types=1);

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Paystack calls this endpoint server-to-server. It is outside the locale prefix
 * and exempt from CSRF (see bootstrap/app.php) because the caller has no session;
 * the HMAC signature is what authenticates the request.
 */
class PaystackWebhookController extends Controller
{
    public function __invoke(Request $request, PaymentService $payments): JsonResponse
    {
        $status = $payments->handleWebhook(
            $request->getContent(),
            $request->header('x-paystack-signature'),
        );

        return response()->json(
            ['status' => $status === 200 ? 'ok' : 'rejected'],
            $status,
        );
    }
}
