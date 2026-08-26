<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - General System Notification
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

class GeneralSystemNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $description,
        public string $type = 'info', // 'info', 'success', 'warning', 'announcement'
        public bool $sendEmail = false,
        public ?string $actionUrl = null,
        public ?string $actionText = null,
        public array $metadata = []
    ) {}

    public function via(object $notifiable): array
    {
        return $this->sendEmail ? ['database', 'mail'] : ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        DynamicMailConfigService::applyDatabaseMailConfig();

        $appName = config('app.name', 'HelpOfAi Studio');

        $mail = (new MailMessage)
            ->subject("[{$appName}] {$this->title}")
            ->greeting("Hello {$notifiable->name},")
            ->line($this->description);

        if ($this->actionUrl && $this->actionText) {
            $mail->action($this->actionText, $this->actionUrl);
        }

        return $mail->line('Thank you for using ' . $appName . '!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'action_url' => $this->actionUrl,
            'action_text' => $this->actionText,
            'metadata' => $this->metadata,
            'created_at' => now()->toIso8601String(),
        ];
    }
}
