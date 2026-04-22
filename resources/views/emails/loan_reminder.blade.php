<!DOCTYPE html>
<html>
<head>
    <title>Loan Payment Reminder</title>
</head>
<body style="margin:0; padding:0; background:#f4f6f9; font-family: Arial, sans-serif;">

<div style="max-width:600px; margin:30px auto; background:#ffffff; border-radius:10px; overflow:hidden; border:1px solid #eaeaea;">

    <div style="background:#2c3e50; padding:20px; text-align:center;">
        <h2 style="color:#ffffff; margin:0;">Loan Payment Reminder</h2>
    </div>

    <div style="padding:25px; color:#333333;">

        <p>Dear Customer,</p>

        <p>
            This is a friendly reminder that your loan installment is due soon. Please review the details below:
        </p>

        <table style="width:100%; border-collapse:collapse; margin-top:15px;">

            <tr>
                <td style="padding:10px 0;"><strong>Loan Number:</strong></td>
                <td>{{ $loan->loan_number }}</td>
            </tr>

            <tr>
                <td style="padding:10px 0;"><strong>EMI (This Month):</strong></td>
                <td style="color:#e67e22; font-weight:bold;">
                    {{ number_format($loan->emi_amount, 2) }}
                </td>
            </tr>

            <tr>
                <td style="padding:10px 0;"><strong>Remaining Amount:</strong></td>
                <td>{{ number_format($loan->remaining_amount, 2) }}</td>
            </tr>

            <tr>
                <td style="padding:10px 0;"><strong>Next Due Date:</strong></td>
                <td>{{ \Carbon\Carbon::parse($loan->next_due_date)->format('Y-m-d') }}</td>
            </tr>

        </table>

        <div style="margin-top:20px; padding:15px; background:#fff3cd; border-left:5px solid #ffc107; border-radius:5px;">
            <strong>Important:</strong>
            Please ensure you pay the EMI amount before the due date to avoid late payment charges.
        </div>

        <p style="margin-top:20px;">
            Thank you for banking with us.
        </p>

        <p>
            Regards,<br>
            <strong>Loan Management System</strong>
        </p>

    </div>

    <div style="background:#f0f0f0; text-align:center; padding:10px; font-size:12px; color:#888;">
        © {{ date('Y') }} Loan System. All rights reserved.
    </div>

</div>

</body>
</html>
