<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('saraki.store_name') }}</title>
    </head>
    <body style="margin:0;padding:24px 12px;background:#f4efe6;font-family:Helvetica,Arial,sans-serif;color:#1c1c1c;">
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #e2dbcd;">
            <tr>
                <td style="background:#1c1c1c;padding:20px 28px;">
                    <span style="font-size:20px;letter-spacing:6px;color:#f4efe6;font-weight:600;">{{ config('saraki.store_name') }}</span>
                </td>
            </tr>
            <tr>
                <td style="padding:28px;font-size:15px;line-height:1.6;">
                    @yield('content')
                </td>
            </tr>
            <tr>
                <td style="padding:18px 28px;border-top:1px solid #e2dbcd;font-size:12px;line-height:1.6;color:#6b6459;">
                    {{ __('mail.footer.help') }}<br>
                    {{ config('saraki.admin_email') }}
                </td>
            </tr>
        </table>
    </body>
</html>
