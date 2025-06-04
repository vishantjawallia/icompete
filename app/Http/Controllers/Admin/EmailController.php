<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\NewsletterJob;
use App\Mail\Sendmail;
use App\Models\Newsletter;
use App\Models\NotifyTemplate;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mail;

class EmailController extends Controller
{
    // Email Templates
    public function templates()
    {
        $templates = NotifyTemplate::whereJsonContains('channels', 'email')->get();

        return view('admin.etemplates.index', compact('templates'));
    }

    public function editTemplate($id)
    {
        $template = NotifyTemplate::findorFail($id);

        return view('admin.etemplates.edit', compact('template'));
    }

    public function updateTemplate($id, Request $request)
    {
        $request->validate([
            'email_subject' => 'required',
            'email_content' => 'required',
        ]);
        $template = NotifyTemplate::findOrFail($id);
        $template->email_status = $request->email_status ?? 0;
        $template->email_subject = $request->email_subject;
        $template->email_content = $request->email_content;
        $template->save();

        return response()->json([
            'status'  => 'success',
            'message' => 'Template updated successfully',
            // 'url' => route('admin.email.templates.index')
        ], 200);

        return redirect()->route('admin.email.templates.index')->withSuccess('Email template updated successfully');
    }

    // Newsletter
    public function newsletter()
    {
        $nls = Newsletter::orderByDesc('id')->get();

        return view('admin.newsletter.index', compact('nls'));
    }

    public function addNewsletter()
    {
        return view('admin.newsletter.add');
    }

    public function storeNewsletter(Request $request)
    {
        // save to db
        $nl = new Newsletter();
        $nl->contestants = $request->contestants ?? 0;
        $nl->organizers = $request->organizers ?? 0;
        $nl->voters = $request->voters ?? 0;
        $nl->other_emails = $request->other_emails;
        $nl->subject = $request->subject;
        $nl->message = $request->message;
        $nl->date = $request->date;
        $nl->status = 2;
        $nl->save();

        return to_route('admin.newsletter.index')->withSuccess('Email Scheduled Successfully');
    }

    public function editNewsletter($id)
    {
        $nl = Newsletter::findOrFail($id);

        return view('admin.newsletter.view', compact('nl'));
    }

    public function updateNewsletter(Request $request, $id)
    {
        // save to db
        $nl = Newsletter::findOrFail($id);
        $nl->contestants = $request->contestants ?? 0;
        $nl->organizers = $request->organizers ?? 0;
        $nl->voters = $request->voters ?? 0;
        $nl->other_emails = $request->other_emails;
        $nl->subject = $request->subject;
        $nl->message = $request->message;
        $nl->date = $request->date;
        $nl->status = 2;

        // check if request->date is in the pase and set status =1
        if (Carbon::parse($request->date)->isPast()) {
            $nl->status = 1;
        }
        $nl->save();

        return to_route('admin.newsletter.index')->withSuccess('Email Updated Successfully');
    }

    public function deleteNewsletter($id)
    {
        $nl = Newsletter::findOrFail($id);
        $nl->delete();

        return back()->withSuccess('Newsletter Deleted Successfully');
    }

    // Send Newsletter
    public function sendNewsletter($id)
    {
        $newsletter = Newsletter::findOrFail($id);

        return $this->processNewsletterQueue(collect([$newsletter]));
    }

    public function queue_emails()
    {
        $newsletters = Newsletter::where('status', 2)->where('date', '<=', now())->get();

        return $this->processNewsletterQueue($newsletters);
    }

    private function processNewsletterQueue($newsletters)
    {
        try {
            foreach ($newsletters as $newsletter) {
                $newsletter->status = 1;
                $newsletter->save();
                $this->dispatchNewsletterJobs($newsletter);
            }

            return to_route('admin.newsletter.index')->withSuccess('Emails Scheduled Successfully');
        } catch (\Exception $e) {
            \Log::error('Newsletter Processing Error: ' . $e->getMessage());

            return to_route('admin.newsletter.index')->withError('Unable to process: ' . $e->getMessage());
        }
    }

    public function dispatchNewsletterJobs($newsletter)
    {
        $delayTime = rand(20, 120);
        $delay = now()->addSeconds($delayTime);

        $types = [
            'contestants' => $newsletter->contestant,
            'organizers'  => $newsletter->organizers,
            'voters'      => $newsletter->voters,
        ];

        foreach ($types as $type => $enabled) {
            if ($enabled) {
                dispatch(new NewsletterJob($newsletter, $type))->delay($delay);
            }
        }

        $this->processOtherEmails($newsletter, $delay);
    }

    private function processOtherEmails($newsletter, $delay)
    {
        $otherEmails = array_filter(array_map('trim', explode(',', $newsletter->other_emails)));

        foreach ($otherEmails as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                dispatch(new NewsletterJob($newsletter, 'others', $email))->delay($delay);
            } else {
                \Log::warning("Invalid email address found in newsletter ID {$newsletter->id}: $email");
            }
        }
    }

    // send test email
    public function testEmail(Request $request)
    {
        // Step 1: Validate email input
        $request->validate([
            'email' => 'required|email',
        ]);

        // Step 2: Prepare email data
        $data = [
            'subject' => 'Test Email from ' . get_setting('title'),
            'message' => 'This is a test email. SMTP connection was successful',
            'view'    => 'emails.main',
            'title'   => 'Hello, ' . auth('admin')->name,
        ];

        // Step 3: Determine which email gateway to use and send the email
        if (sys_setting('email_gateway') == 'php') {
            // Send email using PHP mail()
            return $this->sendUsingPhpMail($request->email, $data, $request);
        }

        // Send email using SMTP
        return $this->sendUsingSmtp($request->email, $data, $request);

    }

    private function sendUsingPhpMail($recipientEmail, $data, $request)
    {
        // Prepare email headers
        $mfName = env('MAIL_FROM_NAME');
        $mfEmail = env('MAIL_FROM_ADDRESS');
        $headers = "From: $mfName <$mfEmail> \r\n";
        $headers .= "Reply-To: $mfName <$mfEmail> \r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=utf-8\r\n";

        try {
            // Render the email view
            $messageView = view($data['view'], compact('data'))->render();

            // Send email using PHP mail function
            if (@mail($recipientEmail, $data['subject'], $messageView, $headers)) {
                return $this->handleSuccess($request, 'Test Email sent successfully');
            }

            throw new \Exception('Failed to send email using PHP mail.');

        } catch (\Exception $e) {
            \Log::error('Error sending email via PHP mail: ' . $e->getMessage());

            return $this->handleError($request, 'Email not sent: ' . $e->getMessage());
        }
    }

    private function sendUsingSmtp($recipientEmail, $data, $request)
    {
        try {
            // Send email using Laravel's Mail facade (SMTP)
            Mail::to($recipientEmail)->send(new Sendmail($data));

            return $this->handleSuccess($request, 'Test Email sent successfully');
        } catch (\Exception $e) {
            \Log::error('Error sending email via SMTP: ' . $e->getMessage());

            return $this->handleError($request, 'Email not sent: ' . $e->getMessage());
        }
    }

    private function handleSuccess($request, $message)
    {
        // Return success response in JSON or redirect
        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'success',
                'message' => $message,
            ], 200);
        }

        return back()->withSuccess($message);
    }

    private function handleError($request, $message)
    {
        // Return error response in JSON or redirect
        if ($request->wantsJson()) {
            return response()->json([
                'status'  => 'error',
                'message' => $message,
            ], 200);
        }

        return back()->withError($message);
    }
}
