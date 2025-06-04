<?php

namespace App\Services;

use App\Events\PushNotificationEvent;
use App\Jobs\FcmNotificationJob;
use App\Models\Admin;
use App\Models\AdminNotify;
use App\Models\NotifyTemplate;
use App\Models\User;
use InvalidArgumentException;

class NotificationService
{
    private $templates;

    public function __construct()
    {
        $this->templates = NotifyTemplate::all();
    }

    /**
     * Send notification based on type
     */
    public function send(string $type, User $user, array $shortcodes = [], $customData = [])
    {
        try {
            $template = $this->getTemplate($type);

            if (! $template) {
                throw new InvalidArgumentException("Template {$type} not found");
            }

            // Send Email Notification
            if ($template->email_status == 1 && in_array('email', $template->channels)) {
                $this->sendEmail($user, $template, $shortcodes);
            }

            // Send In-App Notification
            if ($template->push_status == 1 && in_array('inapp', $template->channels)) {
                $this->sendInApp($user, $template, $shortcodes, $customData);
            }

            // Send Push Notification
            if ($template->push_status == 1 && in_array('push', $template->channels)) {
                $this->sendPush($user, $template, $shortcodes, $customData);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Notification failed', [
                'type'  => $type,
                'user'  => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send custom notification to a user
     *
     * @param  array  $customData
     * @return bool
     */
    public function sendCustom(User $user, array $data, array $channels = [], $customData = [])
    {
        try {
            $shortcodes = [
                'name'     => $user->full_name,
                'username' => $user->username,
                'email'    => $user->email,
            ];
            $template = $data;

            // Send Email Notification
            if (in_array('email', $channels)) {
                $this->sendEmail($user, $template, $shortcodes);
            }

            // Send In-App Notification
            if (in_array('inapp', $channels)) {
                $this->sendInApp($user, $template, $shortcodes);
            }

            // Send Push Notification
            if (in_array('push', $channels)) {
                $this->sendPush($user, $template, $shortcodes);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Notification failed', [
                'type'  => 'custom',
                'user'  => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Send bulk notifications
     */
    public function sendBulk(string $type, $users, array $shortcodes = []): array
    {
        $results = [];
        foreach ($users as $user) {
            $results[$user->id] = $this->send($type, $user, $shortcodes);
        }

        return $results;
    }

    /**
     * Get template and validate
     */
    private function getTemplate(string $type): ?NotifyTemplate
    {
        return collect($this->templates)
            ->firstWhere('type', $type);
    }

    /**
     * Replace shortcodes in template
     */
    private function replaceShortcodes($template, array $shortcodes): array
    {
        $replacements = array_merge($this->defaultShortcodes(), $shortcodes);  // combine shortcodes

        return [
            'title'         => $this->replaceText($template['title'], $replacements),
            'message'       => $this->replaceText($template['message'], $replacements),
            'email_subject' => $this->replaceText($template['email_subject'] ?? '', $replacements),
            'email_content' => $this->replaceText($template['email_content'] ?? '', $replacements),
        ];
    }

    /**
     * Replace text with data
     */
    private function replaceText(string $text, array $replacements): string
    {
        return preg_replace_callback(
            '/{([^}]+)}/',
            fn ($match) => $replacements[$match[1]] ?? $match[0],
            $text
        );
    }

    /**
     * Default system shortcodes.
     *
     * @return array
     */
    private function userShortcodes($user = null)
    {
        return [
            'username'   => $user->username ?? '',
            'email'      => $user->email ?? '',
            'phone'      => $user->phone ?? '',
            'name'       => $user->full_name ?? '',
            'last_name'  => $user->last_name ?? '',
            'first_name' => $user->first_name ?? '',
            'role'       => $user->role ?? '',
        ];
    }

    /**
     * Default system shortcodes.
     *
     * @return array
     */
    private function defaultShortcodes()
    {
        $settings = get_setting();

        return [
            'site_name'     => $settings->name,
            'site_email'    => $settings->email,
            'site_phone'    => $settings->phone,
            'support_email' => $settings->email,
            'currency'      => $settings->currency,
            'site_address'  => $settings->address,
            'date'          => date('Y-m-d'),
            'datetime'      => date('Y-m-d H:m:s'),
            'time'          => date('H:m:s'),
        ];
    }

    /**
     * Send email notification
     */

    /**
     * Send email notification.
     *
     * @param  User|array|int  $users
     * @param  NotificationTemplate  $template
     * @param  array  $shortcodes
     */
    private function sendEmail($user, $template, $shortcodes)
    {
        $shortcodes = array_merge($this->defaultShortcodes(), $shortcodes);  // combine shortcodes
        $subject = $this->replaceText($template['email_subject'], $shortcodes);
        $content = $this->replaceText($template['email_content'], $shortcodes);

        if (! $user->email) {
            return;
        }
        // send using mail helper
        general_email($user->email, $subject, $content);
    }

    /**
     * Send push notification
     */
    private function sendPush(User $user, $template, $shortcodes = [], $data = [])
    {
        $content = $this->replaceShortcodes($template, $shortcodes);

        if (! $user->push_token) {
            return;
        }
        // TODO: Add image to notifications  and sound?
        // send using fcm
        dispatch(new FcmNotificationJob($user, $content['title'], $content['message'], $data));
    }

    /**
     * Send in-app notification
     */
    private function sendInapp(User $user, $template, $shortcodes = [], $data = [])
    {
        $content = $this->replaceShortcodes($template, $shortcodes);
        // Store in database notifications table
        $user->notifys()->create([
            'type'    => 'message',
            'title'   => $content['title'],
            'message' => $content['message'],
            'url'     => null,
            'data'    => $data,
        ]);
        // also send using websocket
        $notificationData = [
            'title'   => $content['title'],
            'message' => $content['message'],
            'type'    => 'message',
            'data'    => $data,
        ];
        // event(new PushNotificationEvent($notificationData));

    }

    /**
     * Schedule a notification
     */
    public function schedule(string $type, User $user, array $data, \DateTime $sendAt): void
    {
        // You can implement scheduling logic here using Laravel's queue system
        dispatch(function () use ($type, $user, $data) {
            $this->send($type, $user, $data);
        })->delay($sendAt);
    }

    // add admin notification
    public function sendAdmin(string $type, array $shortcodes = [], $customData = [])
    {
        try {
            $template = $this->getTemplate($type);

            if (! $template) {
                throw new InvalidArgumentException("Template {$type} not found");
            }

            $admins = Admin::all();
            foreach ($admins as $admin) {
                // Send Email Notification
                if ($template->email_status == 1 && in_array('email', $template->channels)) {
                    $this->adminEmail($admin, $template, $shortcodes, $customData);
                }

                // Send Push Notification
                if ($template->push_status == 1 && in_array('push', $template->channels)) {
                    $this->adminPush($admin, $template, $shortcodes, $customData);
                }
            }

            // Send In-App Notification
            if ($template->push_status == 1 && in_array('inapp', $template->channels)) {
                $this->adminInApp($template, $shortcodes, $customData);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Notification failed', [
                'type'  => $type,
                'user'  => 'Admin',
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    public function sendAdminCustom(Admin $user, array $data, array $channels = [], $customData = [])
    {
        try {
            $shortcodes = [
                'name'     => $user->name,
                'username' => $user->username,
                'email'    => $user->email,
            ];
            $template = $data;

            // Send Email Notification
            if (in_array('email', $channels)) {
                $this->adminEmail($user, $template, $shortcodes);
            }

            // Send In-App Notification
            if (in_array('inapp', $channels)) {
                $this->adminInApp($user, $template, $shortcodes);
            }

            // Send Push Notification
            if (in_array('push', $channels)) {
                $this->adminPush($user, $template, $shortcodes);
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Notification failed', [
                'type'  => 'custom',
                'user'  => $user->id,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function adminEmail(Admin $user, $template, $shortcodes)
    {
        $shortcodes = array_merge($this->defaultShortcodes(), $shortcodes);  // combine shortcodes
        $subject = $this->replaceText($template['email_subject'], $shortcodes);
        $content = $this->replaceText($template['email_content'], $shortcodes);

        if (! $user->email) {
            return;
        }
        // send using mail helper
        general_email($user->email, $subject, $content);
    }

    /**
     * Send push notification
     */
    private function adminPush(Admin $user, $template, $shortcodes = [], $data = [])
    {
        $content = $this->replaceShortcodes($template, $shortcodes);

        if (! $user->push_token) {
            return;
        }
        // send using fcm
        dispatch(new FcmNotificationJob($user, $content['title'], $content['message'], $data));
    }

    /**
     * Send in-app notification
     */
    private function adminInapp($template, $shortcodes = [], $data = [])
    {
        $content = $this->replaceShortcodes($template, $shortcodes);

        $notify = AdminNotify::create([
            'message' => $content['message'],
            'title'   => $content['title'],
            'url'     => $shortcodes['link'] ?? null,
        ]);
        // also send using websocket
        $notificationData = [
            'title'   => $content['title'],
            'message' => $content['message'],
            'type'    => 'admin_notification',
            'data'    => $data,
        ];
        // event(new PushNotificationEvent($notificationData));

    }
}
