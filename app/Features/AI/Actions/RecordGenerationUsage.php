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

namespace App\Features\AI\Actions;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class RecordGenerationUsage
{
    public function execute(User $user, array $data): void
    {
        $wordsUsed = (int) ($data['words_used'] ?? 0);
        $tokensUsed = (int) ($data['tokens_used'] ?? 0);
        $modelSlug = $data['model_slug'] ?? 'default';
        $generationId = $data['generation_id'] ?? null;

        // Deduct from user's quota
        $user->consumeQuota($wordsUsed);

        // Record in generation_usage audit table
        DB::table('generation_usage')->insert([
            'user_id' => $user->id,
            'generation_id' => $generationId,
            'words_used' => $wordsUsed,
            'tokens_used' => $tokensUsed,
            'model_slug' => $modelSlug,
            'recorded_at' => now(),
        ]);
    }
}