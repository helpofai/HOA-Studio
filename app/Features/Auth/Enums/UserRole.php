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

namespace App\Features\Auth\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case EDITOR = 'editor';
    case PRO = 'pro';
    case USER = 'user';
    case MEMBER = 'member';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::EDITOR => 'Editor / Manager',
            self::PRO => 'Pro Creator',
            self::USER => 'Standard User',
            self::MEMBER => 'Team Member',
        };
    }

    public function badgeVariant(): string
    {
        return match ($this) {
            self::ADMIN => 'violet',
            self::EDITOR => 'cyan',
            self::PRO => 'amber',
            self::USER => 'emerald',
            self::MEMBER => 'standard',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}