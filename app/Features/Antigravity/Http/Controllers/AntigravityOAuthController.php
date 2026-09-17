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
        $redirectUri = url(config('services.google.redirect', '/oauth/antigravity/callback'));

        if (empty($clientId)) {
            return redirect()->route('admin.ai-settings.antigravity')->with('error', 'Google Client ID is not configured in .env (GOOGLE_CLIENT_ID).');
        }

        $state = Str::random(40);
        $request->session()->put('antigravity_oauth_state', $state);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid profile email',
            'access_type' => 'offline',
            'prompt' => 'consent',
            'state' => $state,
        ]);

        return redirect('https://accounts.google.com/o/oauth2/v2/auth?'.$query);
    }

    /**
     * Handle the callback from Google.
     */
    public function callback(Request $request)
    {
        $state = $request->session()->pull('antigravity_oauth_state');

        if (empty($state) || $state !== $request->get('state')) {
            return redirect()->route('admin.ai-settings.antigravity')->with('error', 'Invalid OAuth state verification.');
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
            $redirectUri = url(config('services.google.redirect', '/oauth/antigravity/callback'));

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

                return redirect()->route('admin.ai-settings.antigravity')->with('error', 'Failed to exchange authorization code with Google token server.');
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
}
