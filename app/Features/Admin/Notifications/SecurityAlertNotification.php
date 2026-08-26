<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - System Security Alert Notification
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

namespace App\Features\Admin\Notifications;

use App\Features\Admin\Services\DynamicMailConfigService;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecurityAlertNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $description,
        public string $severity = 'warning', // 'info', 'warning', 'critical', 'success'
        public ?string $actionUrl = null,
        public ?string $actionText = null,
        public array $metadata = []
    ) {}

    public function via(object $notifiable): array
    {
        // Deliver via database in-app notification center and email
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        DynamicMailConfigService::applyDatabaseMailConfig();

        $appName = config('app.name', 'HelpOfAi Studio');

        $mail = (new MailMessage)
            ->subject("🚨 [{$appName}] Security Alert: {$this->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line($this->description);

        if (!empty($this->metadata['ip'])) {
            $mail->line("**Origin IP Address:** " . $this->metadata['ip']);
        }
        if (!empty($this->metadata['location'])) {
            $mail->line("**Detected Location / Network:** " . $this->metadata['location']);
        }
        if (!empty($this->metadata['timestamp'])) {
            $mail->line("**Incident Time:** " . $this->metadata['timestamp']);
        }

        if ($this->actionUrl && $this->actionText) {
            $mail->action($this->actionText, $this->actionUrl);
        } else {
            $mail->action('Review Security Center', url('/admin/auth-settings'));
        }

        return $mail->line('If you did not initiate this action, please review your account security settings immediately.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'severity' => $this->severity,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
            'metadata' => $this->metadata,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
