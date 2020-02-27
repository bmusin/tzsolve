<?php

declare(strict_types = 1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

use App\Client;
use App\Mail\RequestSentMail;
use App\Request as ClientRequest;

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
    public function __construct($newRequestId, $attachment_path)
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
        $request        = ClientRequest::find($this->newRequestId);
        $client         = $request->client;

        Mail::to(new class('Manager', User::getManagerEmail()) {
            public $name;
            public $email;

            public function __construct(string $name, string $email)
            {
                $this->name  = $name;
                $this->email = $email;
            }
        })->send(new RequestSentMail([
            'id'          => $this->newRequestId,
            'name'        => $client->name,
            'email'       => $client->email,
            'subject'     => $request->subject,
            'description' => $request->description,
            'created_at'  => $request->created_at
        ], $this->attachment_path));
    }
}
