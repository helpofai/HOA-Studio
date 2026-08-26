<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Dynamic Mail & Transport Config Service
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

namespace App\Features\Admin\Services;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class DynamicMailConfigService
{
    /**
     * Bootstrap dynamic mail configurations from database settings table.
     */
    public static function applyDatabaseMailConfig(): void
    {
        try {
            $settings = DB::table('settings')
                ->where('group', 'mail')
                ->orWhere('key', 'like', 'mail_%')
                ->pluck('value', 'key');

            if ($settings->isEmpty()) {
                return;
            }

            $driver = $settings['mail_mailer'] ?? config('mail.default', 'smtp');
            Config::set('mail.default', $driver);

            // Configure From Address
            if (!empty($settings['mail_from_address'])) {
                Config::set('mail.from.address', $settings['mail_from_address']);
            }
            if (!empty($settings['mail_from_name'])) {
                Config::set('mail.from.name', $settings['mail_from_name']);
            }

            // Configure SMTP details
            if ($driver === 'smtp') {
                if (!empty($settings['mail_host'])) {
                    Config::set('mail.mailers.smtp.host', $settings['mail_host']);
                }
                if (!empty($settings['mail_port'])) {
                    Config::set('mail.mailers.smtp.port', (int) $settings['mail_port']);
                }
                if (isset($settings['mail_encryption'])) {
                    $scheme = $settings['mail_encryption'] === 'none' ? null : $settings['mail_encryption'];
                    Config::set('mail.mailers.smtp.scheme', $scheme);
                }
                if (isset($settings['mail_username'])) {
                    Config::set('mail.mailers.smtp.username', $settings['mail_username']);
                }
                if (isset($settings['mail_password'])) {
                    Config::set('mail.mailers.smtp.password', $settings['mail_password']);
                }
            }

            // Configure Resend details
            if ($driver === 'resend' && !empty($settings['mail_resend_api_key'])) {
                Config::set('services.resend.key', $settings['mail_resend_api_key']);
            }

            // Configure Mailgun details
            if ($driver === 'mailgun') {
                if (!empty($settings['mail_mailgun_domain'])) {
                    Config::set('services.mailgun.domain', $settings['mail_mailgun_domain']);
                }
                if (!empty($settings['mail_mailgun_secret'])) {
                    Config::set('services.mailgun.secret', $settings['mail_mailgun_secret']);
                }
                if (!empty($settings['mail_mailgun_endpoint'])) {
                    Config::set('services.mailgun.endpoint', $settings['mail_mailgun_endpoint']);
                }
            }

            // Configure Postmark details
            if ($driver === 'postmark' && !empty($settings['mail_postmark_token'])) {
                Config::set('services.postmark.token', $settings['mail_postmark_token']);
            }

            // Configure Amazon SES details
            if ($driver === 'ses') {
                if (!empty($settings['mail_ses_key'])) {
                    Config::set('services.ses.key', $settings['mail_ses_key']);
                }
                if (!empty($settings['mail_ses_secret'])) {
                    Config::set('services.ses.secret', $settings['mail_ses_secret']);
                }
                if (!empty($settings['mail_ses_region'])) {
                    Config::set('services.ses.region', $settings['mail_ses_region']);
                }
            }
        } catch (\Throwable $e) {
            // Silently fall back to static config if DB is not ready during migration
            Log::debug('DynamicMailConfigService: fallback to static config', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Send a live test email and return status diagnosis.
     *
     * @param string $recipientEmail
     * @return array{success: bool, message: string}
     */
    public function sendTestEmail(string $recipientEmail): array
    {
        self::applyDatabaseMailConfig();

        try {
            $siteName = config('app.name', 'HelpOfAi Studio');
            $mailer = config('mail.default', 'smtp');

            Mail::raw("Hello,\n\nThis is a test email sent from {$siteName} Mail Engine using [{$mailer}] transport.\n\nAll email delivery systems, credentials, and notification pipelines are operational.\n\nTimestamp: " . now()->toIso8601String(), function ($message) use ($recipientEmail, $siteName, $mailer) {
                $message->to($recipientEmail)
                        ->subject("✓ [{$siteName}] Mail Delivery Test — Transport: " . strtoupper($mailer));
            });

            return [
                'success' => true,
                'message' => "Test email dispatched successfully to {$recipientEmail} via [{$mailer}].",
            ];
        } catch (\Throwable $e) {
            Log::error('Mail Test Delivery Failed', [
                'recipient' => $recipientEmail,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => "Mail delivery error: " . $e->getMessage(),
            ];
        }
    }
}
