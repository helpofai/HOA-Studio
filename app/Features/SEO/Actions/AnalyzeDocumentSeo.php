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

namespace App\Features\SEO\Actions;

use App\Features\Documents\Models\Document;
use App\Features\SEO\Models\SeoAnalysis;
use App\Features\SEO\Services\SeoAnalyzer;

class AnalyzeDocumentSeo
{
    public function __construct(
        protected SeoAnalyzer $analyzer
    ) {}

    public function execute(Document $document, ?string $targetKeyword = null, array $secondaryKeywords = [], string $metaDescription = ''): SeoAnalysis
    {
        $contentHtml = $document->content->content_html ?? '';
        $title = $document->title;

        $results = $this->analyzer->analyze($contentHtml, $title, $targetKeyword, $secondaryKeywords, $metaDescription);

        return SeoAnalysis::updateOrCreate(
            ['document_id' => $document->id],
            [
                'score' => $results['score'],
                'readability_score' => $results['readability_score'],
                'target_keyword' => $targetKeyword,
                'secondary_keywords' => $secondaryKeywords,
                'metrics' => $results['metrics'],
                'recommendations' => $results['rank_math'] ?? [],
            ]
        );
    }
}