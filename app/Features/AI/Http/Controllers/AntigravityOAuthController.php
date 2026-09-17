<?php

namespace App\Features\AI\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Features\AI\Models\AntigravityAccount;
use Illuminate\Support\Facades\Auth;

class AntigravityOAuthController extends Controller
{
    /**
     * Redirect the user to the Google OAuth page.
     */
    public function redirect()
    {
        return Socialite::driver('google')
            ->scopes(['openid', 'profile', 'email'])
            ->with(['access_type' => 'offline', 'prompt' => 'consent'])
            ->redirect();
    }

    /**
     * Handle the callback from Google.
     */
    public function callback(Request $request)
    {
        try {
            $googleUser = Socialite::driver('google')->user();

            $user = Auth::user();
            if (!$user) {
                return redirect()->route('login')->with('error', 'You must be logged in to connect an Antigravity account.');
            }

            // Check if this google account is already connected for this user
            $existingAccount = AntigravityAccount::where('user_id', $user->id)
                ->where('google_oauth_id', $googleUser->getId())
                ->first();

            if ($existingAccount) {
                // Update tokens
                $existingAccount->update([
                    'google_oauth_token' => $googleUser->token,
                    'google_oauth_refresh_token' => $googleUser->refreshToken ?? $existingAccount->google_oauth_refresh_token,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn ?? 3600),
                    'is_active' => true,
                ]);
            } else {
                // Determine priority order
                $maxPriority = AntigravityAccount::where('user_id', $user->id)->max('priority_order') ?? 0;
                
                // Create a new account link
                AntigravityAccount::create([
                    'user_id' => $user->id,
                    'email' => $googleUser->getEmail(),
                    'google_oauth_id' => $googleUser->getId(),
                    'google_oauth_token' => $googleUser->token,
                    'google_oauth_refresh_token' => $googleUser->refreshToken,
                    'token_expires_at' => now()->addSeconds($googleUser->expiresIn ?? 3600),
                    'priority_order' => $maxPriority + 1,
                    // If antigravity generates a specific provider key mapping via OAuth, we handle it here,
                    // else it functions inherently as bearer token authentication.
                ]);
            }

            return redirect()->route('user.omniroute.setup')->with('success', 'Antigravity account connected successfully.');
        } catch (\Exception $e) {
            return redirect()->route('user.omniroute.setup')->with('error', 'OAuth connection failed: ' . $e->getMessage());
        }
    }
}
