<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Http\Requests\Shop\MadeToMeasureRequest;
use App\Mail\ContactMessageReceived;
use App\Models\ContactMessage;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

/**
 * Sur-mesure enquiries do not touch the cart: the client picks a model and
 * sends the atelier a tailoring request — model photo reference, fabric
 * choice (SARAKI fabric or their own) and contact details. It lands in the
 * same inbox as contact messages so the admin answers it like any enquiry.
 */
class MadeToMeasureController extends Controller
{
    public function store(MadeToMeasureRequest $request, string $locale, Product $product): RedirectResponse
    {
        abort_unless($product->is_active && $product->isMadeToMeasure(), 404);

        if ($request->isSpam()) {
            return $this->thanks($locale, $product);
        }

        $data = $request->validated();
        $name = $product->translate('name');

        $message = ContactMessage::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'product_id' => $product->id,
            'subject' => __('shop.tailoring.subject', ['name' => $name]),
            'message' => $this->body($data, $product, $name),
        ]);

        $this->notify($message);

        return $this->thanks($locale, $product);
    }

    /**
     * The model is linked via product_id (the admin inbox renders its photo),
     * so the body only needs the name for email context, the fabric decision
     * and the client's note — no raw URLs.
     */
    private function body(array $data, Product $product, string $name): string
    {
        $lines = [
            __('shop.tailoring.body_model', ['name' => $name]),
            __('shop.tailoring.body_fabric', [
                'choice' => $data['fabric'] === 'saraki'
                    ? __('shop.tailoring.fabric_saraki')
                    : __('shop.tailoring.fabric_own'),
            ]),
        ];

        if (filled($data['message'] ?? null)) {
            $lines[] = '';
            $lines[] = $data['message'];
        }

        return implode("\n", $lines);
    }

    private function thanks(string $locale, Product $product): RedirectResponse
    {
        return redirect()
            ->route('shop.show', ['locale' => $locale, 'product' => $product->slug])
            ->with('success', __('shop.tailoring.sent'));
    }

    private function notify(ContactMessage $message): void
    {
        try {
            Mail::to($message->email)
                ->locale(app()->getLocale())
                ->send(new ContactMessageReceived($message));

            Mail::to(config('saraki.admin_email'))
                ->locale(config('app.locale'))
                ->send(new ContactMessageReceived($message, forAdmin: true));
        } catch (Throwable $exception) {
            Log::error('Tailoring request email failed', [
                'contact_message' => $message->id,
                'reason' => $exception->getMessage(),
            ]);
        }
    }
}
