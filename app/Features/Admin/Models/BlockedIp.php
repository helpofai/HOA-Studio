<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Blocked IP Eloquent Model
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

namespace App\Features\Admin\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlockedIp extends Model
{
    use HasFactory;

    protected $table = 'blocked_ips';

    protected $fillable = [
        'ip_address',
        'reason',
        'blocked_by',
        'blocked_until',
    ];

    protected $casts = [
        'blocked_until' => 'datetime',
    ];

    /**
     * Check if a specific IP address is actively blocked.
     */
    public static function isIpBlocked(string $ip): bool
    {
        $blocked = static::where('ip_address', $ip)->first();
        if (!$blocked) {
            return false;
        }

        // If blocked permanently (null) or block expiry is in future
        if ($blocked->blocked_until === null || $blocked->blocked_until->isFuture()) {
            return true;
        }

        // Auto-prune expired blocks
        $blocked->delete();
        return false;
    }
}
