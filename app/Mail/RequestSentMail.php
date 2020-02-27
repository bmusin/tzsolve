<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class RequestSentMail extends Mailable
{
    use Queueable, SerializesModels;

    private $data;
    private $attachment_path;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $data, $attachment_path)
    {
        $this->data            = $data;
        $this->attachment_path = $attachment_path;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $msg = $this->from(config('mail.tz_mail_username'))
                    ->subject('Manager notification')
                    ->text('emails.request-sent_plaintext', $this->data);
        if ($this->attachment_path) {
            $msg->attach($this->attachment_path);
        }
        return $msg;
    }
}
