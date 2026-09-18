<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Antigravity Account Manager
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

namespace App\Features\Antigravity\Services;

use App\Features\Antigravity\Models\AntigravityAccount;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AntigravityAccountManager
{
    /**
     * Get the best available Antigravity account for the user.
     * Auto-rotates: skips exhausted accounts, picks by priority order.
     */
    public function getActiveAccount(User $user): ?AntigravityAccount
    {
        // Reset any accounts whose quota reset time has passed
        AntigravityAccount::where('user_id', $user->id)
            ->where('is_quota_exhausted', true)
            ->where('quota_reset_at', '<=', now())
            ->update([
                'is_quota_exhausted' => false,
                'quota_reset_at' => null,
            ]);

        // Get the first active, non-exhausted account ordered by priority
        return AntigravityAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('is_quota_exhausted', false)
            ->orderBy('priority_order')
            ->first();
    }

    /**
     * Mark the current account as quota-exhausted and return the next available one.
     */
    public function rotateToNext(User $user, AntigravityAccount $exhaustedAccount): ?AntigravityAccount
    {
        $exhaustedAccount->update([
            'is_quota_exhausted' => true,
            'quota_reset_at' => now()->addHours(24), // Reset after 24 hours by default
        ]);

        return $this->getActiveAccount($user);
    }

    /**
     * Get all accounts for a user, ordered by priority.
     */
    public function getAllAccounts(User $user)
    {
        return AntigravityAccount::where('user_id', $user->id)
            ->orderBy('priority_order')
            ->get();
    }

    /**
     * Remove an account link.
     */
    public function removeAccount(int $accountId, User $user): bool
    {
        return AntigravityAccount::where('id', $accountId)
            ->where('user_id', $user->id)
            ->delete() > 0;
    }

    /**
     * Reorder account priorities.
     */
    public function reorderAccounts(User $user, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            AntigravityAccount::where('id', $id)
                ->where('user_id', $user->id)
                ->update(['priority_order' => $index]);
        }
    }

    /**
     * Get the bearer token (OAuth or API key) for the active account.
     */
    public function getActiveToken(User $user): ?string
    {
        $account = $this->getActiveAccount($user);

        if (! $account) {
            return null;
        }

        // Prefer the Antigravity API key if set directly
        if (! empty($account->antigravity_key)) {
            return $account->antigravity_key;
        }

        // Fall back to the Google OAuth token
        if (! empty($account->google_oauth_token)) {
            // Check if token is expired
            if ($account->token_expires_at && $account->token_expires_at->isPast()) {
                // Token expired — try to refresh, or mark exhausted
                $refreshed = $this->refreshOAuthToken($account);
                if (! $refreshed) {
                    // Rotation: mark as exhausted and try next
                    return $this->rotateToNext($user, $account)
                        ? $this->getActiveToken($user)
                        : null;
                }
                $account->refresh();
            }

            return $account->google_oauth_token;
        }

        return null;
    }

    /**
     * Attempt to refresh an expired Google OAuth token.
     */
    protected function refreshOAuthToken(AntigravityAccount $account): bool
    {
        if (empty($account->google_oauth_refresh_token)) {
            return false;
        }

        try {
            // Hotfix for Local SSL/Certificate issues in local dev
            $client = Http::asForm();
            if (config('app.env') === 'local') {
                $client->withoutVerifying();
            }

            $response = $client->post('https://oauth2.googleapis.com/token', [
                    'client_id' => config('services.google.client_id'),
                    'client_secret' => config('services.google.client_secret'),
                    'refresh_token' => $account->google_oauth_refresh_token,
                    'grant_type' => 'refresh_token',
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $account->update([
                    'google_oauth_token' => $data['access_token'],
                    'token_expires_at' => now()->addSeconds($data['expires_in'] ?? 3600),
                ]);

                return true;
            }
        } catch (\Exception $e) {
            Log::warning('Antigravity OAuth refresh failed', [
                'account_id' => $account->id,
                'error' => $e->getMessage(),
            ]);
        }

        return false;
    }

    /**
     * Count available (non-exhausted) accounts for a user.
     */
    public function availableCount(User $user): int
    {
        return AntigravityAccount::where('user_id', $user->id)
            ->where('is_active', true)
            ->where('is_quota_exhausted', false)
            ->count();
    }
}