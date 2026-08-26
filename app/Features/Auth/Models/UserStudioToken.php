<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - User Studio Connect Token Model
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

namespace App\Features\Auth\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class UserStudioToken extends Model
{
    use HasFactory;

    protected $table = 'user_studio_tokens';

    protected $fillable = [
        'user_id',
        'name',
        'connected_domain',
        'last_ip',
        'token_prefix',
        'token_hash',
        'abilities',
        'last_used_at',
        'expires_at',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'abilities' => 'array',
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Generate a new unique token pair (plain text token for user, hashed for DB).
     *
     * @return array{token: UserStudioToken, plainTextToken: string}
     */
    public static function createTokenForUser(User $user, string $name = 'WordPress Integration', ?array $abilities = ['*'], ?\DateTimeInterface $expiresAt = null): array
    {
        $entropy = Str::random(40);
        $prefix = 'hoa_live_' . substr(bin2hex(random_bytes(4)), 0, 8);
        $plainText = $prefix . '_' . $entropy;
        $hash = hash('sha256', $plainText);

        $token = self::create([
            'user_id' => $user->id,
            'name' => $name,
            'token_prefix' => $prefix,
            'token_hash' => $hash,
            'abilities' => $abilities ?? ['*'],
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        return [
            'token' => $token,
            'plainTextToken' => $plainText,
        ];
    }

    /**
     * Authenticate and find user from a raw bearer token.
     */
    public static function findToken(string $plainTextToken): ?self
    {
        if (empty($plainTextToken)) {
            return null;
        }

        $hash = hash('sha256', $plainTextToken);
        $token = self::where('token_hash', $hash)
            ->where('is_active', true)
            ->with('user')
            ->first();

        if (!$token) {
            return null;
        }

        if ($token->expires_at && $token->expires_at->isPast()) {
            return null;
        }

        return $token;
    }

    /**
     * Mark the token as recently utilized and record origin domain and IP.
     */
    public function touchUsage(?string $domain = null, ?string $ip = null): void
    {
        $payload = ['last_used_at' => now()];
        if ($domain) {
            $payload['connected_domain'] = $domain;
        }
        if ($ip) {
            $payload['last_ip'] = $ip;
        }
        $this->updateQuietly($payload);
    }
}
