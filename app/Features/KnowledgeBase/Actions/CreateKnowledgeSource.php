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

namespace App\Features\KnowledgeBase\Actions;

use App\Features\KnowledgeBase\Models\KnowledgeSource;
use App\Models\User;

class CreateKnowledgeSource
{
    public function __construct(
        protected ProcessKnowledgeSource $processAction
    ) {}

    public function execute(User $user, array $data): KnowledgeSource
    {
        $source = KnowledgeSource::create([
            'user_id' => $user->id,
            'project_id' => $data['project_id'] ?? null,
            'title' => $data['title'],
            'source_type' => $data['source_type'] ?? 'text',
            'category' => $data['category'] ?? 'general_docs',
            'file_path' => $data['file_path'] ?? null,
            'content' => $data['content'],
            'status' => 'pending',
            'is_active' => true,
        ]);

        $this->processAction->execute($source);

        return $source->fresh(['chunks']);
    }
}