<?php

declare(strict_types=1);

namespace App\Mail;

use App\Models\Appointment;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when a customer asks for a slot: an acknowledgement for them, and the
 * notification the atelier needs to approve it.
 */
class AppointmentRequested extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public bool $forAdmin = false,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->forAdmin
                ? __('mail.appointment.requested_admin_subject')
                : __('mail.appointment.requested_subject'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.appointments.requested',
            with: [
                'appointment' => $this->appointment->loadMissing(['user', 'service']),
                'forAdmin' => $this->forAdmin,
            ],
        );
    }
}
