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

class UpdateBrandProfile
{
    public function execute(BrandProfile $profile, array $data): BrandProfile
    {
        if (!empty($data['is_default'])) {
            BrandProfile::where('user_id', $profile->user_id)->where('id', '!=', $profile->id)->update(['is_default' => false]);
        }

        $forbidden = $data['forbidden_words'] ?? $profile->forbidden_words;
        if (is_string($forbidden)) {
            $forbidden = array_filter(array_map('trim', explode(',', $forbidden)));
        }

        $profile->update([
            'project_id' => $data['project_id'] ?? $profile->project_id,
            'name' => $data['name'] ?? $profile->name,
            'tone_description' => $data['tone_description'] ?? $profile->tone_description,
            'target_audience' => $data['target_audience'] ?? $profile->target_audience,
            'guidelines' => $data['guidelines'] ?? $profile->guidelines,
            'forbidden_words' => $forbidden,
            'sample_content' => $data['sample_content'] ?? $profile->sample_content,
            'is_default' => isset($data['is_default']) ? (bool) $data['is_default'] : $profile->is_default,
        ]);

        return $profile;
    }
}