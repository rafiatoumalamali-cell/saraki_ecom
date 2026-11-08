<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * The receipt: what was paid, by which channel, against which reference.
 */
class PaymentReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public Payment $payment,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: __('mail.received.subject', ['number' => $this->order->order_number]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.orders.payment-received',
            with: [
                'order' => $this->order->loadMissing('items'),
                'payment' => $this->payment,
            ],
        );
    }
}
