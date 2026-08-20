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
use Illuminate\Support\Str;

class UpdateProject
{
    public function execute(Project $project, array $data): Project
    {
        if (! empty($data['name']) && $data['name'] !== $project->name) {
            $project->name = trim($data['name']);
            $project->slug = Str::slug($project->name) . '-' . Str::lower(Str::random(5));
        }

        if (array_key_exists('description', $data)) {
            $project->description = $data['description'];
        }

        if (! empty($data['color'])) {
            $project->color = $data['color'];
        }

        if (! empty($data['icon'])) {
            $project->icon = $data['icon'];
        }

        $project->save();

        return $project;
    }
}