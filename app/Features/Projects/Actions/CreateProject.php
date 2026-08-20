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

namespace App\Features\Projects\Actions;

use App\Features\Projects\Models\Project;
use App\Models\User;
use Illuminate\Support\Str;

class CreateProject
{
    public function execute(User $user, array $data): Project
    {
        $name = trim($data['name']);
        $slug = Str::slug($name) . '-' . Str::lower(Str::random(5));

        return Project::create([
            'user_id' => $user->id,
            'name' => $name,
            'slug' => $slug,
            'description' => $data['description'] ?? null,
            'color' => $data['color'] ?? '#6366f1',
            'icon' => $data['icon'] ?? 'folder',
            'settings' => $data['settings'] ?? [],
        ]);
    }
}