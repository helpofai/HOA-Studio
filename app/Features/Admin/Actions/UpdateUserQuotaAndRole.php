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

namespace App\Features\Admin\Actions;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UpdateUserQuotaAndRole
{
    public function execute(User $user, array $data): User
    {
        if (isset($data['name'])) {
            $user->name = trim($data['name']);
        }

        if (isset($data['email'])) {
            $user->email = strtolower(trim($data['email']));
        }

        if (isset($data['role'])) {
            $user->role = $data['role'];
        }

        if (isset($data['plan'])) {
            $user->plan = $data['plan'];
        }

        if (isset($data['monthly_word_quota'])) {
            $user->monthly_word_quota = (int) $data['monthly_word_quota'];
        }

        if (isset($data['used_word_quota'])) {
            $user->used_word_quota = (int) $data['used_word_quota'];
        }

        if (isset($data['bonus_word_quota'])) {
            $user->bonus_word_quota = max(0, (int) $data['bonus_word_quota']);
        }

        if (isset($data['is_active'])) {
            $user->is_active = (bool) $data['is_active'];
        }

        if (isset($data['email_verified'])) {
            $user->email_verified_at = $data['email_verified'] ? ($user->email_verified_at ?: now()) : null;
        }

        if (!empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }

        $user->save();

        return $user;
    }
}