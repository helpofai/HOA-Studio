<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin Mail & Notification Control Center
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Features\Admin\Livewire;

use App\Features\Admin\Notifications\GeneralSystemNotification;
use App\Features\Admin\Notifications\SecurityAlertNotification;
use App\Features\Admin\Services\DynamicMailConfigService;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('Mail Server & Notifications — HelpOfAi Studio')]
class AdminMailNotificationPage extends Component
{
    // Active Tab
    public string $activeTab = 'mail_server'; // 'mail_server', 'templates', 'notification_channels', 'broadcast', 'test_delivery'

    // Mail Templates State & Editor
    public string $selectedTemplateKey = 'welcome_registration';
    public string $template_subject = '';
    public string $template_heading = '';
    public string $template_body = '';
    public string $template_action_text = '';
    public string $template_action_url = '';
    public bool $showPreviewModal = false;
    public string $previewHtml = '';

    // Mail Server Gateway State
    public string $mail_mailer = 'smtp'; // 'smtp', 'resend', 'mailgun', 'postmark', 'ses', 'log'
    public string $mail_host = 'smtp.mailtrap.io';
    public int $mail_port = 587;
    public string $mail_username = '';
    public string $mail_password = '';
    public string $mail_encryption = 'tls'; // 'tls', 'ssl', 'none'
    public string $mail_from_address = 'support@helpofai.com';
    public string $mail_from_name = 'HelpOfAi Studio';

    // API-Based Mailer Secrets
    public string $mail_resend_api_key = '';
    public string $mail_mailgun_domain = '';
    public string $mail_mailgun_secret = '';
    public string $mail_mailgun_endpoint = 'api.mailgun.net';
    public string $mail_postmark_token = '';
    public string $mail_ses_key = '';
    public string $mail_ses_secret = '';
    public string $mail_ses_region = 'us-east-1';

    // Notification Triggers & Channels Matrix
    public bool $notify_on_failed_login = true;
    public bool $notify_on_ip_autoblock = true;
    public bool $notify_on_user_registered = true;
    public bool $notify_on_quota_low = true;
    public string $admin_alert_email = '';

    // Broadcast Announcement State
    public string $broadcast_title = '';
    public string $broadcast_message = '';
    public string $broadcast_type = 'announcement'; // 'announcement', 'info', 'warning', 'success'
    public string $broadcast_target = 'all'; // 'all', 'admin', 'pro_users'
    public bool $broadcast_send_email = false;

    // Test Delivery State
    public string $test_recipient_email = '';
    public ?array $test_result = null;
    public bool $is_testing = false;

    public function mount()
    {
        $settings = DB::table('settings')
            ->where('group', 'mail')
            ->orWhere('key', 'like', 'mail_%')
            ->orWhere('key', 'like', 'notify_%')
            ->pluck('value', 'key')
            ->toArray();

        $this->mail_mailer = (string) ($settings['mail_mailer'] ?? config('mail.default', 'smtp'));
        $this->mail_host = (string) ($settings['mail_host'] ?? (config('mail.mailers.smtp.host') ?: 'smtp.mailtrap.io'));
        $this->mail_port = (int) ($settings['mail_port'] ?? (config('mail.mailers.smtp.port') ?: 587));
        $this->mail_username = (string) ($settings['mail_username'] ?? (config('mail.mailers.smtp.username') ?: ''));
        $this->mail_password = (string) ($settings['mail_password'] ?? (config('mail.mailers.smtp.password') ?: ''));
        $this->mail_encryption = (string) ($settings['mail_encryption'] ?? (config('mail.mailers.smtp.scheme') ?: 'tls'));
        $this->mail_from_address = (string) ($settings['mail_from_address'] ?? (config('mail.from.address') ?: 'support@helpofai.com'));
        $this->mail_from_name = (string) ($settings['mail_from_name'] ?? (config('mail.from.name') ?: 'HelpOfAi Studio'));

        // Third-party API keys
        $this->mail_resend_api_key = (string) ($settings['mail_resend_api_key'] ?? (config('services.resend.key') ?: ''));
        $this->mail_mailgun_domain = (string) ($settings['mail_mailgun_domain'] ?? (config('services.mailgun.domain') ?: ''));
        $this->mail_mailgun_secret = (string) ($settings['mail_mailgun_secret'] ?? (config('services.mailgun.secret') ?: ''));
        $this->mail_mailgun_endpoint = (string) ($settings['mail_mailgun_endpoint'] ?? (config('services.mailgun.endpoint') ?: 'api.mailgun.net'));
        $this->mail_postmark_token = (string) ($settings['mail_postmark_token'] ?? (config('services.postmark.token') ?: ''));
        $this->mail_ses_key = (string) ($settings['mail_ses_key'] ?? (config('services.ses.key') ?: ''));
        $this->mail_ses_secret = (string) ($settings['mail_ses_secret'] ?? (config('services.ses.secret') ?: ''));
        $this->mail_ses_region = (string) ($settings['mail_ses_region'] ?? (config('services.ses.region') ?: 'us-east-1'));

        // Notification policies
        $this->notify_on_failed_login = isset($settings['notify_on_failed_login']) ? (bool) $settings['notify_on_failed_login'] : true;
        $this->notify_on_ip_autoblock = isset($settings['notify_on_ip_autoblock']) ? (bool) $settings['notify_on_ip_autoblock'] : true;
        $this->notify_on_user_registered = isset($settings['notify_on_user_registered']) ? (bool) $settings['notify_on_user_registered'] : true;
        $this->notify_on_quota_low = isset($settings['notify_on_quota_low']) ? (bool) $settings['notify_on_quota_low'] : true;
        $this->admin_alert_email = (string) ($settings['admin_alert_email'] ?? (Auth::user()?->email ?? 'admin@helpofai.com'));
        $this->test_recipient_email = (string) (Auth::user()?->email ?? 'admin@helpofai.com');

        $this->selectTemplate($this->selectedTemplateKey);
    }

    public function selectTemplate(string $key)
    {
        $this->selectedTemplateKey = $key;
        $compiled = \App\Features\Admin\Services\MailTemplateService::getCompiledTemplate($key);

        $this->template_subject = $compiled['subject'];
        $this->template_heading = $compiled['heading'];
        $this->template_body = $compiled['body'];
        $this->template_action_text = $compiled['action_text'];
        $this->template_action_url = $compiled['action_url'];
    }

    public function saveTemplate()
    {
        $this->validate([
            'template_subject' => 'required|string|max:255',
            'template_heading' => 'required|string|max:255',
            'template_body' => 'required|string|max:5000',
            'template_action_text' => 'nullable|string|max:100',
            'template_action_url' => 'nullable|string|max:500',
        ]);

        $key = $this->selectedTemplateKey;
        $items = [
            "mail_tpl_{$key}_subject" => $this->template_subject,
            "mail_tpl_{$key}_heading" => $this->template_heading,
            "mail_tpl_{$key}_body" => $this->template_body,
            "mail_tpl_{$key}_action_text" => $this->template_action_text,
            "mail_tpl_{$key}_action_url" => $this->template_action_url,
        ];

        foreach ($items as $k => $v) {
            DB::table('settings')->updateOrInsert(
                ['key' => $k],
                [
                    'value' => $v,
                    'type' => 'mail_template',
                    'group' => 'mail_templates',
                    'updated_at' => now(),
                ]
            );
        }

        session()->flash('status', "Email Template '{$key}' updated and persisted successfully.");
    }

    public function resetTemplateToDefault()
    {
        $key = $this->selectedTemplateKey;
        DB::table('settings')->where('key', 'like', "mail_tpl_{$key}_%")->delete();

        $this->selectTemplate($key);

        session()->flash('status', "Email Template '{$key}' restored to system factory defaults.");
    }

    public function previewTemplate()
    {
        $user = Auth::user();
        $ip = request()->ip() ?? '198.51.100.24';
        $location = 'Kolkata, West Bengal, India';
        $userAgent = request()->userAgent() ?: 'Chrome 132 on macOS Sequoia (Desktop)';
        $resetUrl = url('/password/reset/7c9b8e1f0a2d3e4b5c6d7e8f9a0b1c2d');
        $verifyUrl = url('/email/verify/' . ($user?->id ?? 1) . '/3f8a9b2c1d?expires=1780000000&signature=9a8b7c6d5e4f3a2b1c');

        $sampleVariables = [
            '{user_name}' => $user?->name ?? 'Alex Morgan',
            '{user_email}' => $user?->email ?? 'alex.morgan@helpofai.com',
            '{temporary_password}' => 'HOA#Studio2026!Secure',
            '{user_role}' => strtoupper($user?->role ?? 'admin'),
            '{plan_name}' => strtoupper($user?->plan ?? 'pro'),
            '{new_plan}' => 'ENTERPRISE AI PRO',
            '{monthly_words}' => number_format($user?->monthly_word_quota ?? 100000),
            '{bonus_words}' => '25,000',
            '{remaining_words}' => number_format(max(0, ($user?->monthly_word_quota ?? 100000) - ($user?->used_word_quota ?? 15000))),
            '{total_words}' => number_format($user?->monthly_word_quota ?? 100000),
            '{used_percentage}' => '92.4',
            '{ip_address}' => $ip,
            '{target_email}' => $user?->email ?? 'alex.morgan@helpofai.com',
            '{failure_count}' => '15',
            '{duration_hours}' => '24',
            '{ban_reason}' => 'Suspicious automated scraping & excessive concurrent requests',
            '{maintenance_window}' => 'Saturday, 11:00 PM – Sunday, 1:00 AM UTC',
            '{estimated_duration}' => '45 Minutes',
            '{impacted_services}' => 'AI SSE Stream Gateway & Vector Knowledge Ingestion',
            '{user_agent}' => $userAgent,
            '{location}' => $location,
            '{reset_url}' => $resetUrl,
            '{verify_url}' => $verifyUrl,
            '{expire_minutes}' => '60',
            '{app_name}' => config('app.name', 'HelpOfAi Studio'),
            '{app_url}' => config('app.url', url('/')),
            '{timestamp}' => now()->format('Y-m-d H:i:s T'),
        ];

        $renderedSubject = \App\Features\Admin\Services\MailTemplateService::render($this->template_subject, $sampleVariables);
        $renderedHeading = \App\Features\Admin\Services\MailTemplateService::render($this->template_heading, $sampleVariables);
        $renderedBody = \App\Features\Admin\Services\MailTemplateService::render($this->template_body, $sampleVariables);
        $renderedActionText = \App\Features\Admin\Services\MailTemplateService::render($this->template_action_text, $sampleVariables);
        $renderedActionUrl = \App\Features\Admin\Services\MailTemplateService::render($this->template_action_url, $sampleVariables);

        $this->previewHtml = view('emails.templated-system-mail', [
            'renderedSubject' => $renderedSubject,
            'renderedHeading' => $renderedHeading,
            'renderedBody' => $renderedBody,
            'renderedActionText' => $renderedActionText,
            'renderedActionUrl' => $renderedActionUrl,
            'templateKey' => $this->selectedTemplateKey,
            'recipientName' => $sampleVariables['{user_name}'],
            'recipientEmail' => $sampleVariables['{user_email}'],
        ])->render();

        $this->showPreviewModal = true;
    }

    public function saveMailConfig()
    {
        $this->validate([
            'mail_mailer' => 'required|string|in:smtp,resend,mailgun,postmark,ses,log',
            'mail_from_address' => 'required|email|max:255',
            'mail_from_name' => 'required|string|max:100',
            'mail_host' => 'nullable|string|max:255',
            'mail_port' => 'nullable|integer|min:1|max:65535',
            'mail_username' => 'nullable|string|max:255',
            'mail_password' => 'nullable|string|max:255',
            'mail_encryption' => 'required|string|in:tls,ssl,none',
        ]);

        $configs = [
            'mail_mailer' => $this->mail_mailer,
            'mail_host' => trim($this->mail_host),
            'mail_port' => (string) $this->mail_port,
            'mail_username' => trim($this->mail_username),
            'mail_password' => trim($this->mail_password),
            'mail_encryption' => $this->mail_encryption,
            'mail_from_address' => trim($this->mail_from_address),
            'mail_from_name' => trim($this->mail_from_name),
            'mail_resend_api_key' => trim($this->mail_resend_api_key),
            'mail_mailgun_domain' => trim($this->mail_mailgun_domain),
            'mail_mailgun_secret' => trim($this->mail_mailgun_secret),
            'mail_mailgun_endpoint' => trim($this->mail_mailgun_endpoint),
            'mail_postmark_token' => trim($this->mail_postmark_token),
            'mail_ses_key' => trim($this->mail_ses_key),
            'mail_ses_secret' => trim($this->mail_ses_secret),
            'mail_ses_region' => trim($this->mail_ses_region),
        ];

        foreach ($configs as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => 'mail',
                    'group' => 'mail',
                    'updated_at' => now(),
                ]
            );
        }

        DynamicMailConfigService::applyDatabaseMailConfig();

        session()->flash('status', 'Mail server gateway settings saved and dynamically applied.');
    }

    public function saveNotificationChannels()
    {
        $this->validate([
            'admin_alert_email' => 'required|email|max:255',
            'notify_on_failed_login' => 'boolean',
            'notify_on_ip_autoblock' => 'boolean',
            'notify_on_user_registered' => 'boolean',
            'notify_on_quota_low' => 'boolean',
        ]);

        $configs = [
            'notify_on_failed_login' => $this->notify_on_failed_login ? '1' : '0',
            'notify_on_ip_autoblock' => $this->notify_on_ip_autoblock ? '1' : '0',
            'notify_on_user_registered' => $this->notify_on_user_registered ? '1' : '0',
            'notify_on_quota_low' => $this->notify_on_quota_low ? '1' : '0',
            'admin_alert_email' => trim($this->admin_alert_email),
        ];

        foreach ($configs as $key => $value) {
            DB::table('settings')->updateOrInsert(
                ['key' => $key],
                [
                    'value' => $value,
                    'type' => 'notification',
                    'group' => 'mail',
                    'updated_at' => now(),
                ]
            );
        }

        session()->flash('status', 'Security alert & notification routing policies updated successfully.');
    }

    public function sendTestMail(DynamicMailConfigService $service)
    {
        $this->validate([
            'test_recipient_email' => 'required|email',
        ]);

        $this->is_testing = true;
        $this->test_result = $service->sendTestEmail($this->test_recipient_email);
        $this->is_testing = false;

        if ($this->test_result['success']) {
            session()->flash('test_success', $this->test_result['message']);
        } else {
            session()->flash('test_error', $this->test_result['message']);
        }
    }

    public function sendBroadcast()
    {
        $this->validate([
            'broadcast_title' => 'required|string|min:3|max:150',
            'broadcast_message' => 'required|string|min:5|max:1000',
            'broadcast_type' => 'required|string|in:announcement,info,warning,success',
            'broadcast_target' => 'required|string|in:all,admin,pro_users',
        ]);

        $query = User::query()->where('is_active', true);
        if ($this->broadcast_target === 'admin') {
            $query->where('role', 'admin');
        } elseif ($this->broadcast_target === 'pro_users') {
            $query->whereIn('plan', ['pro', 'enterprise']);
        }

        $recipients = $query->get();
        $notification = new GeneralSystemNotification(
            title: $this->broadcast_title,
            description: $this->broadcast_message,
            type: $this->broadcast_type,
            sendEmail: $this->broadcast_send_email
        );

        foreach ($recipients as $recipient) {
            $recipient->notify($notification);
        }

        $this->broadcast_title = '';
        $this->broadcast_message = '';
        $this->broadcast_send_email = false;

        session()->flash('status', "Broadcast notification successfully dispatched to {$recipients->count()} active user accounts.");
    }

    public function render()
    {
        $totalNotificationsDelivered = DB::table('notifications')->count();
        $unreadNotificationsCount = DB::table('notifications')->whereNull('read_at')->count();

        return view('admin.mail-notifications', [
            'totalNotificationsDelivered' => $totalNotificationsDelivered,
            'unreadNotificationsCount' => $unreadNotificationsCount,
        ]);
    }
}
