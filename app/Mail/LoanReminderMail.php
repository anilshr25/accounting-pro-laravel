<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class LoanReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payment;

    public function __construct($payment)
    {
        $this->payment = $payment;
    }

    public function build()
    {
        return $this->subject('Loan Payment Reminder')
            ->view('emails.loan_reminder')
            ->with([
                'payment' => $this->payment,
                'loan' => $this->payment->loan
            ]);
    }
}
