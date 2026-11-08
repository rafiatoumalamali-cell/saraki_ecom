<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;line-height:1.6;">
    <tr>
        <td style="padding:4px 0;width:130px;color:#6b6459;vertical-align:top;">{{ __('mail.labels.service') }}</td>
        <td style="padding:4px 0;">{{ $appointment->service->translate('name') }}</td>
    </tr>
    <tr>
        <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.date') }}</td>
        <td style="padding:4px 0;">{{ localized_date($appointment->appointment_date) }}</td>
    </tr>
    <tr>
        <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.time') }}</td>
        <td style="padding:4px 0;">
            {{ localized_time($appointment->start_time) }} – {{ localized_time($appointment->end_time) }}
        </td>
    </tr>
    <tr>
        <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.status') }}</td>
        <td style="padding:4px 0;">{{ $appointment->status->label() }}</td>
    </tr>
    <tr>
        <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.customer') }}</td>
        <td style="padding:4px 0;">
            {{ $appointment->user->name }}<br>
            <span style="color:#6b6459;">{{ $appointment->user->email }}</span><br>
            <span style="color:#6b6459;">{{ $appointment->user->phone }}</span>
        </td>
    </tr>
    @if (filled($appointment->notes))
        <tr>
            <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.notes') }}</td>
            <td style="padding:4px 0;">{{ $appointment->notes }}</td>
        </tr>
    @endif
    @if (filled($appointment->admin_notes))
        <tr>
            <td style="padding:4px 0;color:#6b6459;vertical-align:top;">{{ __('mail.labels.admin_note') }}</td>
            <td style="padding:4px 0;">{{ $appointment->admin_notes }}</td>
        </tr>
    @endif
</table>
