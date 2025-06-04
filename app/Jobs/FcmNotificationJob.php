<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Kreait\Firebase\Messaging\CloudMessage;

class FcmNotificationJob implements ShouldQueue
{
    use Queueable;

    public $user;

    public $title;

    public $body;

    public $data;

    public $tries = 5;

    /**
     * Create a new job instance.
     */
    public function __construct($user, $title, $body, $data)
    {
        $this->user = $user;
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $fcmToken = $this->user->push_token;

        $messaging = app('firebase.messaging');

        $message = CloudMessage::new()
            ->withNotification([
                'title' => $this->title,
                'body'  => $this->body,
            ])
            ->withData($this->data ?? [])
            ->toToken($fcmToken);

        try {
            $messaging->send($message);

        } catch (\Exception $e) {
            // log error
            \Log::error('FCM Notification Error: ' . $e->getMessage());
        }
    }
}
