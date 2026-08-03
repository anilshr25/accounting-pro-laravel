<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Verification code</title>
</head>
<body style="margin:0;background:#f4f2ee;color:#1b1917;font-family:Arial,sans-serif">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="padding:32px 16px">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:560px;background:#ffffff;border:1px solid #e7e3dc;border-radius:12px;padding:32px">
                    <tr>
                        <td style="font-size:16px;line-height:1.6">
                            {!! $htmlContent !!}
                            <p style="margin:24px 0 8px;color:#6d675f;font-size:13px">Verification code</p>
                            <p style="margin:0;font-size:32px;font-weight:700;letter-spacing:8px">{{ $verificationCode }}</p>
                            <p style="margin:24px 0 0;color:#6d675f;font-size:13px">If you did not request this code, you can ignore this email.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
