<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| This file is part of the HelpOfAi Professional Software Suite.
| Unauthorized copying, modification, redistribution, reverse engineering,
| decompilation, or commercial use of this source code, in whole or in part,
| is strictly prohibited without prior written permission from the copyright owner.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
| This source code contains proprietary and confidential information.
| Any unauthorized access or distribution may violate applicable copyright laws.
|
|--------------------------------------------------------------------------
*/

namespace App\Providers;

use App\Features\Admin\Livewire\NotificationBell;
use App\Features\Documents\Services\EditorManager;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(EditorManager::class, function () {
            return new EditorManager;
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        // Force HTTPS in production or behind SSL reverse proxies (cPanel, LiteSpeed, Cloudflare, Nginx)
        if (config('app.env') === 'production' || str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        Vite::useBuildDirectory('build');

        Livewire::component('admin.notification-bell', NotificationBell::class);

        if (Schema::hasTable('settings')) {
            try {
                $settings = \Illuminate\Support\Facades\DB::table('settings')
                    ->whereIn('group', ['auth', 'social_auth', 'security'])
                    ->pluck('value', 'key');

                if (! empty($settings['google_client_id'])) {
                    config(['services.google.client_id' => $settings['google_client_id']]);
                }
                if (! empty($settings['google_client_secret'])) {
                    config(['services.google.client_secret' => $settings['google_client_secret']]);
                }
                if (! empty($settings['google_redirect_url'])) {
                    config(['services.google.redirect' => $settings['google_redirect_url']]);
                }

                if (! empty($settings['facebook_client_id'])) {
                    config(['services.facebook.client_id' => $settings['facebook_client_id']]);
                }
                if (! empty($settings['facebook_client_secret'])) {
                    config(['services.facebook.client_secret' => $settings['facebook_client_secret']]);
                }
                if (! empty($settings['facebook_redirect_url'])) {
                    config(['services.facebook.redirect' => $settings['facebook_redirect_url']]);
                }

                if (! empty($settings['twitter_client_id'])) {
                    config(['services.twitter.client_id' => $settings['twitter_client_id']]);
                }
                if (! empty($settings['twitter_client_secret'])) {
                    config(['services.twitter.client_secret' => $settings['twitter_client_secret']]);
                }
                if (! empty($settings['twitter_redirect_url'])) {
                    config(['services.twitter.redirect' => $settings['twitter_redirect_url']]);
                }

                if (! empty($settings['github_client_id'])) {
                    config(['services.github.client_id' => $settings['github_client_id']]);
                }
                if (! empty($settings['github_client_secret'])) {
                    config(['services.github.client_secret' => $settings['github_client_secret']]);
                }
                if (! empty($settings['github_redirect_url'])) {
                    config(['services.github.redirect' => $settings['github_redirect_url']]);
                }
            } catch (\Throwable $e) {
                // Ignore DB error during initial setup
            }
        }
    }
}
