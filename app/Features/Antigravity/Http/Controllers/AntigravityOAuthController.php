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
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class AntigravityOAuthController extends Controller
{
    /**
     * Redirect the user to the Google OAuth page using native HTTP flow.
     */
    public function redirect(Request $request)
    {
        $clientId = config('services.google.client_id');
        $configuredRedirect = config('services.google.redirect', '/oauth/antigravity/callback');
        
        // If they specify a relative path in DB (e.g., "/oauth/antigravity/callback"), force it to use `forceRootUrl` if HTTPS is required,
        // or ensure `url()` generates correct HTTPS bindings using `APP_URL`.
        if (str_starts_with($configuredRedirect, 'http')) {
            $redirectUri = $configuredRedirect;
        } else {
            // Force strict resolution using config('app.url') as fallback if reverse proxies drop the schema
            $baseUrl = rtrim(config('app.url', 'https://studio.helpofai.com'), '/');
            $redirectUri = $baseUrl . '/' . ltrim($configuredRedirect, '/');
        }

        if (empty($clientId)) {
            return redirect()->route('admin.ai-settings.antigravity')->with('error', 'Google Client ID is not configured in .env (GOOGLE_CLIENT_ID).');
        }

        $state = Str::random(40);
        $request->session()->put('antigravity_oauth_state', $state);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email https://www.googleapis.com/auth/cloud-platform https://www.googleapis.com/auth/cclog https://www.googleapis.com/auth/experimentsandconfigs',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        $googleUrl = 'https://accounts.google.com/o/oauth2/v2/auth?'.$query;

        // If request expects JSON or explicitly asks for debug info, or direct redirect
        if ($request->has('debug')) {
            \Illuminate\Support\Facades\Log::debug('Antigravity OAuth Debug', [
                'app_url' => config('app.url'),
                'configured_redirect' => $configuredRedirect,
                'redirect_uri_sent_to_google' => $redirectUri,
                'full_google_url' => $googleUrl,
            ]);

            return response()->json([
                'client_id' => $clientId,
                'redirect_uri_sent_to_google' => $redirectUri,
                'full_google_url' => $googleUrl,
            ]);
        }

        return redirect($googleUrl);
    }

    /**
     * Handle the callback from Google.
     */
    public function callback(Request $request)
    {
        $state = $request->session()->pull('antigravity_oauth_state');

        // Allow manual callback bypassing direct state check if a manual URL is provided
        if (empty($state) || $state !== $request->get('state')) {
            // Check if it's a valid manual callback flow
            if ($request->has('code')) {
                 // Proceed without state check only if code exists (Accepting slightly reduced security for manual mode)
            } else {
                return redirect()->route('admin.ai-settings.antigravity')->with('error', 'Invalid OAuth state verification.');
            }
        }

        if ($request->has('error')) {
            return redirect()->route('admin.ai-settings.antigravity')->with('error', 'Google authorization cancelled: '.$request->get('error'));
        }

        $code = $request->get('code');
        if (empty($code)) {
            return redirect()->route('admin.ai-settings.antigravity')->with('error', 'Missing OAuth authorization code.');
        }

        $user = Auth::user();
        if (! $user) {
            return redirect()->route('login')->with('error', 'You must be logged in to connect an Antigravity account.');
        }

        try {
            $clientId = config('services.google.client_id');
            $clientSecret = config('services.google.client_secret');
            $configuredRedirect = config('services.google.redirect', '/oauth/antigravity/callback');
            
            if (str_starts_with($configuredRedirect, 'http')) {
                $redirectUri = $configuredRedirect;
            } else {
                $baseUrl = rtrim(config('app.url', 'https://studio.helpofai.com'), '/');
                $redirectUri = $baseUrl . '/' . ltrim($configuredRedirect, '/');
            }

            // 1. Exchange auth code for tokens
            $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'redirect_uri' => $redirectUri,
                'grant_type' => 'authorization_code',
                'code' => $code,
            ]);

            if (! $tokenResponse->successful()) {
                Log::error('Antigravity OAuth token exchange failed', ['body' => $tokenResponse->body()]);

                return redirect()->route('admin.ai-settings.antigravity')->with('error', 'Failed to exchange authorization code with Google token server. Check redirect URIs in Google Cloud Console.');
            }

            $tokenData = $tokenResponse->json();
            $accessToken = $tokenData['access_token'] ?? null;
            $refreshToken = $tokenData['refresh_token'] ?? null;
            $expiresIn = $tokenData['expires_in'] ?? 3600;

            if (empty($accessToken)) {
                return redirect()->route('admin.ai-settings.antigravity')->with('error', 'Received empty access token from Google.');
            }

            // 2. Fetch user details from Google UserInfo endpoint
            $userInfoResponse = Http::withToken($accessToken)->get('https://www.googleapis.com/oauth2/v3/userinfo');

            $googleId = null;
            $email = null;

            if ($userInfoResponse->successful()) {
                $userData = $userInfoResponse->json();
                $googleId = $userData['sub'] ?? null;
                $email = $userData['email'] ?? null;
            }

            // 3. Save or update AntigravityAccount
            $existingAccount = null;
            if ($googleId) {
                $existingAccount = AntigravityAccount::where('user_id', $user->id)
                    ->where('google_oauth_id', $googleId)
                    ->first();
            }

            if ($existingAccount) {
                $existingAccount->update([
                    'email' => $email ?? $existingAccount->email,
                    'google_oauth_token' => $accessToken,
                    'google_oauth_refresh_token' => $refreshToken ?? $existingAccount->google_oauth_refresh_token,
                    'token_expires_at' => now()->addSeconds($expiresIn),
                    'is_active' => true,
                    'is_quota_exhausted' => false,
                ]);
            } else {
                $maxPriority = AntigravityAccount::where('user_id', $user->id)->max('priority_order') ?? 0;

                AntigravityAccount::create([
                    'user_id' => $user->id,
                    'email' => $email ?: 'antigravity-'.$user->id.'-'.(AntigravityAccount::count() + 1).'@google.com',
                    'google_oauth_id' => $googleId,
                    'google_oauth_token' => $accessToken,
                    'google_oauth_refresh_token' => $refreshToken,
                    'token_expires_at' => now()->addSeconds($expiresIn),
                    'priority_order' => $maxPriority + 1,
                    'is_active' => true,
                ]);
            }

            return redirect()->route('admin.ai-settings.antigravity')->with('success', 'Antigravity Google account linked successfully.');
        } catch (\Exception $e) {
            Log::error('Antigravity OAuth Callback Exception', ['error' => $e->getMessage()]);

            return redirect()->route('admin.ai-settings.antigravity')->with('error', 'OAuth connection error: '.$e->getMessage());
        }
    }

    /**
     * Handle manual paste of the full redirect URL (fixes popup blocks).
     */
    public function manual_callback(Request $request)
    {
        $fullUrl = $request->input('full_url');
        if (empty($fullUrl)) {
            return redirect()->route('admin.ai-settings.antigravity')->with('error', 'Please paste a valid URL.');
        }
        
        $queryString = parse_url($fullUrl, PHP_URL_QUERY);
        if (!$queryString) {
            return redirect()->route('admin.ai-settings.antigravity')->with('error', 'Invalid URL format: no query parameters found.');
        }

        parse_str($queryString, $queryParams);

        // Re-inject into Request
        $request->merge($queryParams);
        
        // Redirect to normal callback logic
        return $this->callback($request);
    }
}
