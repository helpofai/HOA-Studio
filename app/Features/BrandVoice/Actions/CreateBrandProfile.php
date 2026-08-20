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

namespace App\Features\BrandVoice\Actions;

use App\Features\BrandVoice\Models\BrandProfile;
use App\Models\User;

class CreateBrandProfile
{
    public function execute(User $user, array $data): BrandProfile
    {
        if (!empty($data['is_default'])) {
            BrandProfile::where('user_id', $user->id)->update(['is_default' => false]);
        }

        $forbidden = $data['forbidden_words'] ?? [];
        if (is_string($forbidden)) {
            $forbidden = array_filter(array_map('trim', explode(',', $forbidden)));
        }

        return BrandProfile::create([
            'user_id' => $user->id,
            'project_id' => $data['project_id'] ?? null,
            'name' => $data['name'],
            'tone_description' => $data['tone_description'],
            'target_audience' => $data['target_audience'] ?? null,
            'guidelines' => $data['guidelines'] ?? null,
            'forbidden_words' => $forbidden,
            'sample_content' => $data['sample_content'] ?? null,
            'is_default' => $data['is_default'] ?? false,
        ]);
    }
}