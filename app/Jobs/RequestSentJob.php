<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\UserController;
use App\Mail\RequestSentMail;

use Swift_Attachment;
use Swift_Mailer;
use Swift_Message;
use Swift_SmtpTransport;

class RequestSentJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $newRequestId;
    private $attachment_path;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($newRequestId, $attachment_path = null)
    {
        $this->newRequestId = $newRequestId;
        $this->attachment_path = $attachment_path;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $request = DB::table('requests')->find($this->newRequestId);
        $associatedUser = DB::table('users')->find($request->user_id);

        $transport = (new Swift_SmtpTransport(
            config('mail.tz_mail_smtp_server'),
            config('mail.tz_mail_smtp_port')
        ))
            ->setUsername(config('mail.tz_mail_username'))
            ->setPassword(config('mail.tz_mail_password'));
        $mailer = new Swift_Mailer($transport);

        $id          = $this->newRequestId;
        $name        = $associatedUser->name;
        $email       = $associatedUser->email;
        $subject     = $request->subject;
        $description = $request->description;
        $created_at  = $request->created_at;

        ob_start();
        include(resource_path('views/emails/request-sent.php'));
        $body = ob_get_contents();
        ob_end_clean();

        $message = (new Swift_Message('Notification to manager'))
            ->setFrom([config('mail.tz_mail_username') => config('mail.tz_mail_from')])
            ->setTo([UserController::getManagerEmail() => 'Manager'])
            ->setBody($body, 'text/plain');

        if ($this->attachment_path) {
            $message->attach(
                Swift_Attachment::fromPath($this->attachment_path)
            );
        }
        $result = $mailer->send($message);
    }
}
