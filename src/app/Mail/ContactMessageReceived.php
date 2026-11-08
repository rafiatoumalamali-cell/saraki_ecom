<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\ContactMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when the contact form is submitted: the enquiry lands in the atelier's
 * inbox, and the sender gets a short acknowledgement in their own language.
 *
 * The property is named `$contact` because `Mailable::$message` is taken by the
 * framework.
 */
class ContactMessageReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public ContactMessage $contact,
        public bool $forAdmin = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->forAdmin
                ? __('mail.contact.admin_subject', ['name' => $this->contact->name])
                : __('mail.contact.subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.contact.received',
            with: [
                'contact' => $this->contact,
                'forAdmin' => $this->forAdmin,
            ],
        );
    }
}
