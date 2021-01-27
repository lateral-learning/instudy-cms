<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ResetPassword extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(String $to, String $passwordRef)
    {
        $this->__to = $to;
        $this->passwordRef = $passwordRef;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this
            ->subject("Reset Password InStudy")
            ->view('reset')
            ->with([
                'email' => md5($this->__to),
                'pass' => md5($this->passwordRef)
            ]);
    }
}
