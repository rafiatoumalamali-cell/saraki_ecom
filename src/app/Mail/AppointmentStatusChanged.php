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
 * Sent on every state change — approved, rescheduled, completed or cancelled —
 * to the customer and, as a copy, to the atelier.
 */
class AppointmentStatusChanged extends Mailable
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
                ? __('mail.appointment.status_admin_subject', [
                    'name' => $this->appointment->user->name,
                    'status' => $this->appointment->status->label(),
                ])
                : __('mail.appointment.status_subject', ['status' => $this->appointment->status->label()]),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.appointments.status',
            with: [
                'appointment' => $this->appointment->loadMissing(['user', 'service']),
                'forAdmin' => $this->forAdmin,
            ],
        );
    }
}
