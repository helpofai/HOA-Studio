<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Antigravity OAuth Controller
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

namespace App\Features\Antigravity\Http\Controllers;

use App\Features\Antigravity\Models\AntigravityAccount;
use App\Features\Antigravity\Services\AntigravityAccountManager;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AntigravityOAuthController extends Controller
{
    /**
     * Resolve Google / Antigravity OAuth Client ID from DB settings, config, env, or fallback.
     */
    protected function resolveClientId(): string
    {
        return config('services.antigravity.client_id')
            ?: config('services.google.client_id')
            ?: env('ANTIGRAVITY_CLIENT_ID')
            ?: env('GOOGLE_CLIENT_ID')
            ?: env('GOOGLE_CLIENT_ID_FALLBACK', 'default_cli_id.apps.googleusercontent.com');
    }

    /**
     * Resolve Google / Antigravity OAuth Client Secret from DB settings, config, env, or fallback.
     */
    protected function resolveClientSecret(): string
    {
        return config('services.antigravity.client_secret')
            ?: config('services.google.client_secret')
            ?: env('ANTIGRAVITY_CLIENT_SECRET')
            ?: env('GOOGLE_CLIENT_SECRET')
            ?: env('GOOGLE_CLIENT_SECRET_FALLBACK', 'default_cli_secret');
    }

    /**
     * Resolve Google / Antigravity Authorized Redirect URI.
     */
    protected function resolveRedirectUri(): string
    {
        $configured = config('services.antigravity.redirect') ?: config('services.google.redirect');
        if (! empty($configured)) {
            return str_starts_with($configured, 'http') ? $configured : url($configured);
        }

        return url('/oauth/antigravity/callback');
    }

    public function redirect(Request $request)
    {
        $clientId = $this->resolveClientId();
        $redirectUri = $this->resolveRedirectUri();

        $state = Str::random(40);
        Cache::put('antigravity_oauth_state_' . $state, true, now()->addMinutes(10));

        // Official Google Cloud Code & Antigravity scopes (without openid to avoid broken nativeapp consent)
        $scopes = [
            'https://www.googleapis.com/auth/cloud-platform',
            'https://www.googleapis.com/auth/userinfo.email',
            'https://www.googleapis.com/auth/userinfo.profile',
            'https://www.googleapis.com/auth/cclog',
            'https://www.googleapis.com/auth/experimentsandconfigs',
        ];

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    public function callback(Request $request, AntigravityAccountManager $manager)
    {
        $state = $request->get('state');
        if (empty($state) || !Cache::has('antigravity_oauth_state_' . $state)) {
            return redirect()->route('ai-models.antigravity')->with('error', 'OAuth state verification failed.');
        }
        Cache::forget('antigravity_oauth_state_' . $state);

        $code = $request->get('code');
        $user = Auth::user();

        try {
            $clientId = $this->resolveClientId();
            $clientSecret = $this->resolveClientSecret();
            $redirectUri = $this->resolveRedirectUri();

            $client = Http::asForm()
                ->withHeaders([
                    'User-Agent' => 'antigravity/cli/1.0.0 (aidev_client; os_type=darwin; arch=arm64; auth_method=consumer)',
                ])
                ->connectTimeout(2.5)
                ->timeout(8);

            if (config('app.env') === 'local' || app()->environment('local')) {
                $client = $client->withoutVerifying();
            }

            $tokenResponse = $client->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

            if (!$tokenResponse->successful()) {
                throw new \Exception('Token exchange failed: ' . $tokenResponse->body());
            }

            $tokenData = $tokenResponse->json();
            
            // Fetch User Email & Profile info
            $userClient = Http::withToken($tokenData['access_token']);
            if (config('app.env') === 'local' || app()->environment('local')) {
                $userClient = $userClient->withoutVerifying();
            }
            $userInfo = $userClient->get('https://www.googleapis.com/oauth2/v1/userinfo')->json();
            $sub = $userInfo['id'] ?? ($userInfo['sub'] ?? 'google_' . md5($userInfo['email'] ?? Str::random(16)));
            
            $account = AntigravityAccount::updateOrCreate(
                ['user_id' => $user->id, 'google_oauth_id' => $sub],
                [
                    'email' => $userInfo['email'] ?? ($user->email ?? 'unknown@antigravity.ai'),
                    'google_oauth_token' => $tokenData['access_token'],
                    'google_oauth_refresh_token' => $tokenData['refresh_token'] ?? null,
                    'token_expires_at' => now()->addSeconds($tokenData['expires_in'] ?? 3600),
                    'is_active' => true,
                    'is_quota_exhausted' => false,
                ]
            );

            // Discover and bootstrap Google Cloud Code Project ID
            $manager->bootstrapAccountProject($account);

            // Sync live available models from Cloud Code PA (:fetchAvailableModels)
            $manager->syncAntigravityModels($user);

            return response(
                '<!DOCTYPE html><html class="dark bg-slate-950 text-white font-sans"><head><title>Antigravity Account Linked</title>' .
                '<script>' .
                'if (window.opener) {' .
                '   try { window.opener.postMessage({ type: "antigravity:oauth_success" }, "*"); } catch(e) {}' .
                '   setTimeout(function() { window.close(); }, 500);' .
                '} else {' .
                '   window.location.href = "' . route('ai-models.antigravity') . '";' .
                '}' .
                '</script></head><body class="flex items-center justify-center min-h-screen bg-slate-950 text-slate-200 text-center p-6">' .
                '<div class="p-6 rounded-2xl bg-slate-900 border border-white/10 shadow-2xl max-w-sm">' .
                '<div class="text-3xl mb-3">⚡</div>' .
                '<h2 class="text-base font-bold text-white mb-1">Account Connected</h2>' .
                '<p class="text-xs text-slate-400 mb-4">Your Antigravity OAuth credentials are linked. Closing window...</p>' .
                '<a href="' . route('ai-models.antigravity') . '" class="text-xs text-indigo-400 underline">Click here if window does not close</a>' .
                '</div></body></html>'
            );
        } catch (\Exception $e) {
            return response(
                '<!DOCTYPE html><html class="dark bg-slate-950 text-white font-sans"><head><title>Antigravity Auth Error</title></head><body class="flex items-center justify-center min-h-screen bg-slate-950 text-slate-200 text-center p-6">' .
                '<div class="p-6 rounded-2xl bg-slate-900 border border-red-500/20 shadow-2xl max-w-sm">' .
                '<div class="text-3xl mb-3">⚠️</div>' .
                '<h2 class="text-base font-bold text-red-300 mb-1">Connection Failed</h2>' .
                '<p class="text-xs text-slate-400 mb-4">' . htmlspecialchars($e->getMessage()) . '</p>' .
                '<button onclick="window.close()" class="px-4 py-2 text-xs font-bold bg-slate-800 text-white rounded-xl border border-white/10">Close Window</button>' .
                '</div></body></html>',
                500
            );
        }
    }
}


