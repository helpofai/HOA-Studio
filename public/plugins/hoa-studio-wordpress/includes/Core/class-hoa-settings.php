<?php
/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress Plugin Settings Manager
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

namespace HOA_Studio\Core;

if (!defined('ABSPATH')) {
    exit;
}

class HOA_Settings
{
    public const OPTION_ENDPOINT = 'hoa_studio_endpoint';
    public const OPTION_API_KEY = 'hoa_studio_api_key';
    public const OPTION_STATUS = 'hoa_studio_status';
    public const OPTION_USER_DATA = 'hoa_studio_user_data';
    public const OPTION_MODELS = 'hoa_studio_available_models';
    public const OPTION_BRAND_VOICES = 'hoa_studio_brand_voices';
    public const OPTION_DEFAULT_MODEL = 'hoa_studio_default_model';
    public const OPTION_DEFAULT_TONE = 'hoa_studio_default_tone';
    public const OPTION_POST_TYPES = 'hoa_studio_enabled_post_types';
    public const OPTION_AUTO_SYNC = 'hoa_studio_auto_sync';
    public const OPTION_SYNC_WEBHOOK_SECRET = 'hoa_studio_sync_webhook_secret';

    public static function getEndpoint(): string
    {
        return rtrim((string) get_option(self::OPTION_ENDPOINT, ''), '/');
    }

    public static function getApiKey(): string
    {
        return (string) get_option(self::OPTION_API_KEY, '');
    }

    public static function isConnected(): bool
    {
        return get_option(self::OPTION_STATUS, 'disconnected') === 'connected' && !empty(self::getApiKey());
    }

    public static function getUserData(): array
    {
        $data = get_option(self::OPTION_USER_DATA, []);
        return is_array($data) ? $data : [];
    }

    public static function getAvailableModels(): array
    {
        $models = get_option(self::OPTION_MODELS, []);
        return is_array($models) ? $models : [];
    }

    public static function getBrandVoices(): array
    {
        $voices = get_option(self::OPTION_BRAND_VOICES, []);
        return is_array($voices) ? $voices : [];
    }

    public static function getDefaultModel(): string
    {
        return (string) get_option(self::OPTION_DEFAULT_MODEL, 'auto');
    }

    public static function getDefaultTone(): string
    {
        return (string) get_option(self::OPTION_DEFAULT_TONE, 'Professional');
    }

    public static function getEnabledPostTypes(): array
    {
        $types = get_option(self::OPTION_POST_TYPES, ['post', 'page']);
        return is_array($types) ? $types : ['post', 'page'];
    }

    public static function isPostTypeEnabled(string $postType): bool
    {
        return in_array($postType, self::getEnabledPostTypes(), true);
    }

    public static function isAutoSyncEnabled(): bool
    {
        return get_option(self::OPTION_AUTO_SYNC, 'no') === 'yes';
    }

    public static function getWebhookSecret(): string
    {
        $secret = get_option(self::OPTION_SYNC_WEBHOOK_SECRET, '');
        if (empty($secret)) {
            $secret = wp_generate_password(32, false);
            update_option(self::OPTION_SYNC_WEBHOOK_SECRET, $secret);
        }
        return $secret;
    }
}
