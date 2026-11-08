@extends('mail.layout')

@section('content')
    <h1 style="margin:0 0 12px;font-size:20px;line-height:1.4;">
        {{ $forAdmin ? __('mail.contact.admin_title', ['name' => $contact->name]) : __('mail.contact.title') }}
    </h1>

    <p style="margin:0 0 16px;">
        {{ $forAdmin ? __('mail.contact.admin_intro') : __('mail.contact.intro', ['name' => $contact->name]) }}
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;font-size:14px;">
        <tr>
            <td style="padding:6px 0;color:#6b6459;width:120px;">{{ __('mail.labels.customer') }}</td>
            <td style="padding:6px 0;">{{ $contact->name }}</td>
        </tr>
        <tr>
            <td style="padding:6px 0;color:#6b6459;">{{ __('mail.contact.email') }}</td>
            <td style="padding:6px 0;">
                <a href="mailto:{{ $contact->email }}" style="color:#1c1c1c;">{{ $contact->email }}</a>
            </td>
        </tr>
        @if (filled($contact->phone))
            <tr>
                <td style="padding:6px 0;color:#6b6459;">{{ __('mail.contact.phone') }}</td>
                <td style="padding:6px 0;">{{ $contact->phone }}</td>
            </tr>
        @endif
        @if (filled($contact->subject))
            <tr>
                <td style="padding:6px 0;color:#6b6459;">{{ __('mail.contact.subject_label') }}</td>
                <td style="padding:6px 0;">{{ $contact->subject }}</td>
            </tr>
        @endif
    </table>

    <div style="margin:18px 0 0;padding:16px;background:#f4efe6;border-left:3px solid #d4af37;">
        {!! nl2br(e($contact->message)) !!}
    </div>

    @if ($forAdmin)
        <p style="margin:22px 0 0;">
            <a
                href="{{ route('admin.messages.show', $contact) }}"
                style="display:inline-block;background:#1c1c1c;color:#f4efe6;padding:12px 22px;text-decoration:none;font-size:13px;letter-spacing:1px;text-transform:uppercase;"
            >{{ __('mail.contact.admin_cta') }}</a>
        </p>
    @else
        <p style="margin:18px 0 0;color:#6b6459;">{{ __('mail.contact.reply_note', ['email' => $contact->email]) }}</p>
    @endif
@endsection
