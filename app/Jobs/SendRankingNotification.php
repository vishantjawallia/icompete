<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendRankingNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $notificationType;
    protected $user;
    protected $data;
    protected $additionalData;

    public function __construct($notificationType, $user, $data, $additionalData)
    {
        $this->notificationType = $notificationType;
        $this->user = $user;
        $this->data = $data;
        $this->additionalData = $additionalData;
    }

    public function handle()
    {
        sendNotification($this->notificationType, $this->user, $this->data, $this->additionalData);
    }
}
