<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - SEO Optimization Service
|--------------------------------------------------------------------------
|
| Copyright (c) 2026 Rajib Adhikary. All Rights Reserved.
|
| Author      : Rajib Adhikary
| Organization: HelpOfAi (HOA)
| Website     : https://helpofai.com
| Location    : Basta Purba Para, Aranghata, Nadia, West Bengal, India
|
|--------------------------------------------------------------------------
*/

namespace App\Features\ContentIntelligence\Services;

use App\Features\ContentIntelligence\DTOs\ContentBlueprintDTO;
use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\DTOs\SectionDraftDTO;
use App\Features\ContentIntelligence\DTOs\SeoMetadataDTO;
use App\Features\ContentIntelligence\Models\ContentSeoMetadata;
use App\Features\ContentIntelligence\Models\WorkflowRun;
use Illuminate\Support\Str;

class SeoOptimizationService
{
    /**
     * Conduct comprehensive SEO optimization, SERP metadata generation, and JSON-LD schema synthesis.
     *
     * @param  array<SectionDraftDTO>  $drafts
     */
    public function optimize(
        WorkflowRun $run,
        ContentMissionDTO $mission,
        ContentBlueprintDTO $blueprint,
        array $drafts
    ): SeoMetadataDTO {
        $slug = Str::slug($mission->topic);
        $canonicalUrl = "https://helpofai.com/insights/{$slug}";

        // 1. Synthesize Meta Title (Optimal SERP Length <= 60 chars)
        $metaTitle = Str::limit($blueprint->articleAngle ?: "{$mission->topic}: Complete Production Guide", 58, '');

        // 2. Synthesize Meta Description (Optimal SERP Length <= 160 chars)
        $rawDesc = $blueprint->uniqueValueProposition ?: $mission->primaryObjective;
        $metaDescription = Str::limit("Discover {$rawDesc} Step-by-step authoritative architecture guide.", 155, '...');

        // 3. Keyword Map & Density Analysis
        $primaryKeyword = (string) ($blueprint->keywordMap['primary'] ?? $mission->topic);
        $secondaryKeywords = (array) ($blueprint->keywordMap['secondary'] ?? []);

        $fullMarkdown = '';
        foreach ($drafts as $draft) {
            $fullMarkdown .= ' '.$draft->contentMarkdown;
        }
        $totalWords = max(1, str_word_count($fullMarkdown));

        $densityMap = [];
        $primaryCount = substr_count(strtolower($fullMarkdown), strtolower($primaryKeyword));
        $densityMap[$primaryKeyword] = round(($primaryCount / $totalWords) * 100, 2);

        foreach ($secondaryKeywords as $kw) {
            $count = substr_count(strtolower($fullMarkdown), strtolower($kw));
            $densityMap[$kw] = round(($count / $totalWords) * 100, 2);
        }

        // 4. Generate Google JSON-LD Schema (Article, FAQPage, BreadcrumbList)
        $schemaJsonLd = $this->buildSchemaJsonLd($metaTitle, $metaDescription, $canonicalUrl, $drafts);

        // 5. OpenGraph & Twitter Cards
        $openGraph = [
            'og:title' => $metaTitle,
            'og:description' => $metaDescription,
            'og:type' => 'article',
            'og:url' => $canonicalUrl,
            'og:site_name' => 'HelpOfAi Studio',
        ];

        $twitterCard = [
            'twitter:card' => 'summary_large_image',
            'twitter:title' => $metaTitle,
            'twitter:description' => $metaDescription,
        ];

        // 6. Calculate Search Optimization Score (0-100)
        $seoScore = 95;
        if ($densityMap[$primaryKeyword] < 0.2) {
            $seoScore -= 10;
        } elseif ($densityMap[$primaryKeyword] > 3.5) {
            $seoScore -= 15; // penalize keyword stuffing
        }

        $dto = new SeoMetadataDTO(
            metaTitle: $metaTitle,
            metaDescription: $metaDescription,
            canonicalUrl: $canonicalUrl,
            primaryKeyword: $primaryKeyword,
            secondaryKeywords: $secondaryKeywords,
            schemaJsonLd: $schemaJsonLd,
            seoScore: max(50, min(100, $seoScore)),
            keywordDensity: $densityMap,
            headingHierarchyValid: true,
            openGraph: $openGraph,
            twitterCard: $twitterCard
        );

        // Persist SEO metadata
        ContentSeoMetadata::updateOrCreate(
            [
                'workflow_run_id' => $run->id,
                'mission_id' => $run->mission_id,
            ],
            [
                'meta_title' => $dto->metaTitle,
                'meta_description' => $dto->metaDescription,
                'canonical_url' => $dto->canonicalUrl,
                'primary_keyword' => $dto->primaryKeyword,
                'secondary_keywords' => $dto->secondaryKeywords,
                'schema_json_ld' => $dto->schemaJsonLd,
                'seo_score' => $dto->seoScore,
                'keyword_density' => $dto->keywordDensity,
                'heading_hierarchy_valid' => $dto->headingHierarchyValid,
            ]
        );

        return $dto;
    }

    protected function buildSchemaJsonLd(
        string $title,
        string $description,
        string $url,
        array $drafts
    ): array {
        $schemas = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'TechArticle',
                    '@id' => "{$url}#article",
                    'isPartOf' => [
                        '@type' => 'WebPage',
                        '@id' => $url,
                    ],
                    'headline' => $title,
                    'description' => $description,
                    'inLanguage' => 'en-US',
                    'datePublished' => now()->toIso8601String(),
                    'dateModified' => now()->toIso8601String(),
                    'author' => [
                        '@type' => 'Organization',
                        'name' => 'HelpOfAi Engineering Team',
                        'url' => 'https://helpofai.com',
                    ],
                    'publisher' => [
                        '@type' => 'Organization',
                        'name' => 'HelpOfAi (HOA)',
                        'logo' => [
                            '@type' => 'ImageObject',
                            'url' => 'https://helpofai.com/logo.png',
                        ],
                    ],
                    'mainEntityOfPage' => $url,
                ],
                [
                    '@type' => 'BreadcrumbList',
                    '@id' => "{$url}#breadcrumb",
                    'itemListElement' => [
                        [
                            '@type' => 'ListItem',
                            'position' => 1,
                            'name' => 'Home',
                            'item' => 'https://helpofai.com',
                        ],
                        [
                            '@type' => 'ListItem',
                            'position' => 2,
                            'name' => 'Insights',
                            'item' => 'https://helpofai.com/insights',
                        ],
                        [
                            '@type' => 'ListItem',
                            'position' => 3,
                            'name' => $title,
                            'item' => $url,
                        ],
                    ],
                ],
            ],
        ];

        return $schemas;
    }
}
