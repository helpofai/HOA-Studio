<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Anti-Bot Protection (Cloudflare Turnstile)
    |--------------------------------------------------------------------------
    |
    | Cloudflare Turnstile provides smart, invisible bot protection without
    | frustrating user CAPTCHAs. Keys are optional and degrade gracefully.
    |
    */
    'turnstile' => [
        'site_key' => env('CLOUDFLARE_TURNSTILE_SITE_KEY'),
        'secret_key' => env('CLOUDFLARE_TURNSTILE_SECRET_KEY'),
        'strict' => env('CLOUDFLARE_TURNSTILE_STRICT', false),
    ],

    /*
     * Authentication Security Features
     * All disabled by default for smooth out-of-the-box experience.
     * Enable individually as needed via env or config.
     */
    'auth_security' => [

        /*
         * IP Blocking: Block IPs that show malicious patterns.
         */
        'ip_blocking_enabled' => env('AUTH_SECURITY_IP_BLOCKING_ENABLED', false),

        /*
         * Honeypot Trap: Invisible form field to catch bots.
         */
        'honeypot_enabled' => env('AUTH_SECURITY_HONEYPOT_ENABLED', false),

        /*
         * Rate Limiting: Throttle login attempts by IP and account.
         */
        'rate_limiting_enabled' => env('AUTH_SECURITY_RATE_LIMITING_ENABLED', false),

        /*
         * Login Notifications: Email admins on admin login.
         */
        'login_notifications_enabled' => env('AUTH_SECURITY_LOGIN_NOTIFICATIONS_ENABLED', false),
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URL', '/oauth/antigravity/callback'),
    ],

    'antigravity' => [
        'client_id' => env('ANTIGRAVITY_CLIENT_ID'),
        'client_secret' => env('ANTIGRAVITY_CLIENT_SECRET'),
        'redirect' => env('ANTIGRAVITY_REDIRECT_URL'),
    ],

    'facebook' => [
        'client_id' => env('FACEBOOK_CLIENT_ID'),
        'client_secret' => env('FACEBOOK_CLIENT_SECRET'),
        'redirect' => env('FACEBOOK_REDIRECT_URL', '/auth/facebook/callback'),
    ],

    'twitter' => [
        'client_id' => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect' => env('TWITTER_REDIRECT_URL', '/auth/twitter/callback'),
    ],

    'github' => [
        'client_id' => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect' => env('GITHUB_REDIRECT_URL', '/auth/github/callback'),
    ],
];
