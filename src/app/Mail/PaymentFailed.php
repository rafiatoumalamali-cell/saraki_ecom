<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when the gateway reports a failed or abandoned attempt, with a link back to
 * the order so the customer can pay again before the stock is released.
 */
class PaymentFailed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.failed.subject', ['number' => $this->order->order_number]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.orders.payment-failed',
            with: [
                'order' => $this->order->loadMissing('items'),
                // A GET link, so the mail can never trigger a payment by itself; the
                // order page carries the retry button.
                'retryUrl' => route('orders.show', [
                    'locale' => $this->order->user->locale ?? config('app.locale'),
                    'order' => $this->order->order_number,
                ]),
            ],
        );
    }
}
