<?php

namespace App\Jobs;

use App\Services\WhatsApp\WhatsApp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendWhatsappToSeller implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public $phoneNumber, public $message)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        WhatsApp::sendMessage($this->phoneNumber, $this->message);
    }
}
