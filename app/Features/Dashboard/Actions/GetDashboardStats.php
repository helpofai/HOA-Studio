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

namespace App\Features\Dashboard\Actions;

use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentVersion;
use App\Features\Projects\Models\Project;
use App\Models\User;

class GetDashboardStats
{
    public function execute(User $user): array
    {
        $totalDocuments = Document::where('user_id', $user->id)->count();
        $totalProjects = Project::where('user_id', $user->id)->count();
        $totalWords = Document::where('user_id', $user->id)->sum('word_count');

        $remainingQuota = max(0, $user->monthly_word_quota - $user->used_word_quota);
        $quotaPercentage = $user->monthly_word_quota > 0 
            ? min(100, round(($user->used_word_quota / $user->monthly_word_quota) * 100))
            : 0;

        $recentDocuments = Document::with('project')
            ->where('user_id', $user->id)
            ->latest('updated_at')
            ->take(6)
            ->get();

        $recentVersions = DocumentVersion::with('document')
            ->where('created_by', $user->id)
            ->latest()
            ->take(5)
            ->get();

        return [
            'total_documents' => $totalDocuments,
            'total_projects' => $totalProjects,
            'total_words' => $totalWords,
            'monthly_quota' => $user->monthly_word_quota,
            'used_quota' => $user->used_word_quota,
            'remaining_quota' => $remainingQuota,
            'quota_percentage' => $quotaPercentage,
            'recent_documents' => $recentDocuments,
            'recent_versions' => $recentVersions,
        ];
    }
}