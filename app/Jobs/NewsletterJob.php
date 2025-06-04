<?php

namespace App\Jobs;

use App\Mail\Sendmail;
use App\Models\Newsletter;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class NewsletterJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $newsletter;

    protected $email;

    protected $type;

    public $tries = 5;

    public $backoff = 600;

    /**
     * Create a new job instance.
     */
    public function __construct(Newsletter $newsletter, $type = 'others', $email = null)
    {
        $this->newsletter = $newsletter;
        $this->type = $type;
        $this->email = $email;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $data['view'] = 'emails.main';
        $data['subject'] = $this->newsletter->subject;
        $data['email'] = env('MAIL_FROM_ADDRESS');
        $data['message'] = $this->newsletter->message;

        // send email
        $roles = ['contestants' => 'contestant', 'voters' => 'voter', 'organizers' => 'organizer'];

        if (array_key_exists($this->type, $roles)) {
            $users = User::where('status', 1)
                ->whereRole($roles[$this->type])
                ->get(['username', 'email'])
                ->pluck('username', 'email');

            foreach ($users as $email => $username) {
                $data['title'] = "Hi, $username";
                Mail::to($email)->queue(new Sendmail($data));
            }
        } else {
            // other emails
            if ($this->email) {
                $data['title'] = 'Hello, ';
                Mail::to($this->email)->queue(new Sendmail($data));
            }
        }
    }
}
