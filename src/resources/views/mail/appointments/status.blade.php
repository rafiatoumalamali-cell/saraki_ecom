@extends('mail.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:20px;line-height:1.4;">
        {{ __('mail.appointment.status_title', ['status' => $appointment->status->label()]) }}
    </h1>

    <p style="margin:0 0 16px;">
        {{ $forAdmin
            ? __('mail.appointment.status_admin_intro', [
                'name' => $appointment->user->name,
                'date' => localized_date($appointment->appointment_date),
                'time' => localized_time($appointment->start_time),
                'status' => $appointment->status->label(),
            ])
            : __('mail.appointment.status_intro', [
                'name' => $appointment->user->name,
                'date' => localized_date($appointment->appointment_date),
                'time' => localized_time($appointment->start_time),
                'status' => $appointment->status->label(),
            ]) }}
    </p>

    @include('mail.appointments.summary', ['appointment' => $appointment])

    <p style="margin:22px 0 0;">
        <a
            href="{{ $forAdmin
                ? route('admin.appointments.show', $appointment)
                : route('appointments.index', app()->getLocale()) }}"
            style="display:inline-block;background:#1c1c1c;color:#f4efe6;padding:12px 22px;text-decoration:none;font-size:13px;letter-spacing:1px;text-transform:uppercase;"
        >{{ $forAdmin ? __('mail.appointment.admin_cta') : __('mail.appointment.cta') }}</a>
    </p>
@endsection
