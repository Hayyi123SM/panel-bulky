<?php

namespace App\Jobs;

use App\Mail\WholesaleEmailMail;
use App\Mail\WholesaleMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendBulkWholesaleEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected string $recipient;
    protected array $data;

    public function __construct(string $recipient, array $data = [])
    {
        $this->recipient = $recipient;
        $this->data = $data;
    }

    public function handle(): void
    {
        \Mail::to($this->recipient)->send(new WholesaleEmailMail($this->data));
    }
}
