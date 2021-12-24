<?php

namespace App\Mail;

use App\Models\Email;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendEmail extends Mailable
{
    use Queueable, SerializesModels;

    private $email;

    public function __construct(Email $email)
    {
        $this->email = $email;
    }

    public function build()
    {
        return $this->from(config('mail.from'))
            ->view('email.mail')
            ->with([
                'email' => $this->email
            ]);
    }
}
