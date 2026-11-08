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
 * Confirmation for the customer (and a copy for the shop) whenever an order is
 * recorded without a gateway — cash on delivery and bank transfer.
 */
class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Order $order,
        public bool $forAdmin = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->forAdmin
                ? __('mail.placed.admin_subject', ['number' => $this->order->order_number])
                : __('mail.placed.subject', ['number' => $this->order->order_number]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.orders.placed',
            with: [
                'order' => $this->order->loadMissing('items'),
                'forAdmin' => $this->forAdmin,
            ],
        );
    }
}
