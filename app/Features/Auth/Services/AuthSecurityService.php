<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Authentication Security Service
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

namespace App\Features\Auth\Services;

use App\Features\Admin\Models\AuthSecurityLog;
use App\Features\Admin\Models\BlockedIp;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthSecurityService
{
    /**
     * Check if client IP is actively blocked.
     */
    public function checkIpBlock(): void
    {
        $ip = request()->ip() ?? '127.0.0.1';
        if (BlockedIp::isIpBlocked($ip)) {
            Log::warning('Blocked IP attempted authentication', ['ip' => $ip]);

            AuthSecurityLog::create([
                'ip_address' => $ip,
                'event_type' => 'blocked_ip_rejected',
                'user_agent' => request()->userAgent(),
                'is_blocked' => true,
            ]);

            throw ValidationException::withMessages([
                'email' => __('Access from your network has been temporarily blocked for security reasons.'),
            ]);
        }
    }

    /**
     * Verify invisible honeypot field.
     * Real users will leave the honeypot field empty; bots/scrapers auto-fill all inputs.
     *
     * @param string|null $honeypotValue
     * @param int|null $formLoadedAt Timestamp when form was loaded in frontend
     * @throws ValidationException
     */
    public function verifyHoneypot(?string $honeypotValue, ?int $formLoadedAt = null): void
    {
        $ip = request()->ip() ?? '127.0.0.1';

        // 1. If honeypot is populated, it's definitely an automated bot/scraper
        if (!empty($honeypotValue)) {
            Log::warning('Honeypot bot trigger detected on authentication form', [
                'ip' => $ip,
                'user_agent' => request()->userAgent(),
            ]);

            AuthSecurityLog::create([
                'ip_address' => $ip,
                'event_type' => 'honeypot_triggered',
                'user_agent' => request()->userAgent(),
                'details' => ['trap_value' => $honeypotValue],
                'is_blocked' => false,
            ]);

            throw ValidationException::withMessages([
                'email' => __('Automated submission detected. Request blocked for security reasons.'),
            ]);
        }

        // 2. Timing attack / Instant submission check (human takes > 1.0s to fill form in production)
        if ($formLoadedAt !== null && !app()->runningUnitTests()) {
            $elapsedSeconds = time() - $formLoadedAt;
            // If submitted faster than 1 second, it's an automated headless bot script
            if ($elapsedSeconds < 1) {
                Log::warning('Instant automated bot submission detected (<1s)', [
                    'ip' => request()->ip(),
                    'elapsed_seconds' => $elapsedSeconds,
                ]);

                throw ValidationException::withMessages([
                    'email' => __('Suspicious automated submission speed detected. Please try again.'),
                ]);
            }
        }
    }

    /**
     * Verify Cloudflare Turnstile token if configured and enabled.
     *
     * @param string|null $token
     * @throws ValidationException
     */
    public function verifyTurnstile(?string $token): void
    {
        // 1. Check if Turnstile is explicitly enabled in settings or env
        $isEnabled = static::isTurnstileEnabled();
        if (!$isEnabled) {
            return;
        }

        $secretKey = static::getTurnstileSecretKey();
        if (empty($secretKey)) {
            // Secret key not provided, bypass gracefully
            return;
        }

        if (empty($token)) {
            throw ValidationException::withMessages([
                'turnstile' => __('Please complete the security challenge before submitting.'),
            ]);
        }

        try {
            $response = Http::asForm()
                ->timeout(5)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secretKey,
                    'response' => $token,
                    'remoteip' => request()->ip(),
                ]);

            $result = $response->json();

            if (!($result['success'] ?? false)) {
                Log::warning('Cloudflare Turnstile verification failed', [
                    'ip' => request()->ip(),
                    'errors' => $result['error-codes'] ?? [],
                ]);

                throw ValidationException::withMessages([
                    'turnstile' => __('Security verification failed. Please refresh and try again.'),
                ]);
            }
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('Cloudflare Turnstile verification error: ' . $e->getMessage());
            if (config('services.turnstile.strict', false)) {
                throw ValidationException::withMessages([
                    'turnstile' => __('Unable to verify security challenge at this time.'),
                ]);
            }
        }
    }

    /**
     * Check if Turnstile is enabled in DB settings or env.
     */
    public static function isTurnstileEnabled(): bool
    {
        try {
            $dbSetting = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'turnstile_enabled')->value('value');
            if ($dbSetting !== null) {
                return (bool) $dbSetting;
            }
        } catch (\Throwable $e) {}

        return !empty(static::getTurnstileSiteKey()) && !empty(static::getTurnstileSecretKey());
    }

    /**
     * Get Turnstile Site Key from DB settings or config.
     */
    public static function getTurnstileSiteKey(): string
    {
        try {
            $dbVal = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'turnstile_site_key')->value('value');
            if (!empty($dbVal)) {
                return trim($dbVal);
            }
        } catch (\Throwable $e) {}

        return trim(config('services.turnstile.site_key') ?? '');
    }

    /**
     * Get Turnstile Secret Key from DB settings or config.
     */
    public static function getTurnstileSecretKey(): string
    {
        try {
            $dbVal = \Illuminate\Support\Facades\DB::table('settings')->where('key', 'turnstile_secret_key')->value('value');
            if (!empty($dbVal)) {
                return trim($dbVal);
            }
        } catch (\Throwable $e) {}

        return trim(config('services.turnstile.secret_key') ?? '');
    }
}
