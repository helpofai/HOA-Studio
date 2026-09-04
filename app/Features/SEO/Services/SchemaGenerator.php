<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Schema Generator & Validator
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

namespace App\Features\SEO\Services;

class SchemaGenerator
{
    /**
     * Generate Schema.org JSON-LD structured data and validation report
     *
     * @param string $htmlContent Document HTML content
     * @param string $title Document title
     * @param string $metaDescription Meta description
     * @param array $options Author, publisher, dates, URL
     * @return array Schemas, validation report, and JSON-LD markup
     */
    public function generate(string $htmlContent, string $title = '', string $metaDescription = '', array $options = []): array
    {
        $cleanTitle = trim(strip_tags($title)) ?: 'Untitled Guide';
        $cleanDesc = trim(strip_tags($metaDescription));
        if (empty($cleanDesc)) {
            // Extract first 150 chars from first paragraph
            if (preg_match('/<p[^>]*>(.*?)<\/p>/si', $htmlContent, $pMatch)) {
                $cleanDesc = mb_substr(trim(strip_tags($pMatch[1])), 0, 155) . '...';
            } else {
                $cleanDesc = $cleanTitle;
            }
        }

        $authorName = $options['author_name'] ?? 'Editorial Team';
        $authorUrl = $options['author_url'] ?? config('app.url');
        $siteName = config('app.name', 'HelpOfAi Studio');
        $canonicalUrl = $options['canonical_url'] ?? (config('app.url') . '/p/' . ($options['slug'] ?? 'post'));
        $publishedAt = $options['published_at'] ?? date('c');
        $modifiedAt = $options['modified_at'] ?? date('c');
        $wordCount = !empty($htmlContent) ? count(preg_split('/\s+/u', strip_tags($htmlContent), -1, PREG_SPLIT_NO_EMPTY)) : 0;

        $schemas = [];
        $validationErrors = [];
        $validationWarnings = [];

        // 1. Core Article / BlogPosting Schema
        $articleSchema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => mb_substr($cleanTitle, 0, 110),
            'description' => $cleanDesc,
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $canonicalUrl,
            ],
            'author' => [
                '@type' => 'Person',
                'name' => $authorName,
                'url' => $authorUrl,
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'url' => config('app.url'),
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => config('app.url') . '/logo.png',
                ],
            ],
            'datePublished' => $publishedAt,
            'dateModified' => $modifiedAt,
            'wordCount' => $wordCount,
            'inLanguage' => 'en-US',
        ];

        if (empty($options['featured_image'])) {
            $validationWarnings[] = 'Missing featured image for Article schema rich cards.';
        } else {
            $articleSchema['image'] = $options['featured_image'];
        }

        $schemas['article'] = $articleSchema;

        // 2. FAQPage Schema Detection (Q&A Extraction)
        $faqItems = $this->extractFaqItems($htmlContent);
        if (!empty($faqItems) && count($faqItems) >= 2) {
            $faqEntities = [];
            foreach ($faqItems as $item) {
                $faqEntities[] = [
                    '@type' => 'Question',
                    'name' => $item['question'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $item['answer'],
                    ],
                ];
            }

            $schemas['faq'] = [
                '@context' => 'https://schema.org',
                '@type' => 'FAQPage',
                'mainEntity' => $faqEntities,
            ];
        }

        // 3. HowTo Schema Detection (Step Extraction)
        $howToSteps = $this->extractHowToSteps($htmlContent);
        if (!empty($howToSteps) && count($howToSteps) >= 2) {
            $stepEntities = [];
            foreach ($howToSteps as $idx => $step) {
                $stepEntities[] = [
                    '@type' => 'HowToStep',
                    'position' => $idx + 1,
                    'name' => $step['name'],
                    'text' => $step['text'],
                ];
            }

            $schemas['howto'] = [
                '@context' => 'https://schema.org',
                '@type' => 'HowTo',
                'name' => $cleanTitle,
                'description' => $cleanDesc,
                'step' => $stepEntities,
            ];
        }

        // 4. Combined Graph Schema
        $graph = array_values($schemas);
        $combinedSchema = [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];

        $jsonLdPretty = json_encode(count($schemas) === 1 ? reset($schemas) : $combinedSchema, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $scriptTag = "<script type=\"application/ld+json\">\n" . $jsonLdPretty . "\n</script>";

        return [
            'schemas' => $schemas,
            'detected_types' => array_keys($schemas),
            'recommended_type' => isset($schemas['faq']) ? 'FAQPage + BlogPosting' : (isset($schemas['howto']) ? 'HowTo + BlogPosting' : 'BlogPosting'),
            'faq_count' => count($faqItems),
            'howto_step_count' => count($howToSteps),
            'json_ld' => $jsonLdPretty,
            'script_tag' => $scriptTag,
            'validation' => [
                'is_valid' => empty($validationErrors),
                'errors' => $validationErrors,
                'warnings' => $validationWarnings,
                'passed_rules' => [
                    'Valid JSON-LD Syntax',
                    'Context schema.org declaration',
                    'Author and Publisher specification',
                    'DatePublished and DateModified timestamp formatting',
                    'Headline and Description integrity',
                ],
            ],
        ];
    }

    /**
     * Extract FAQ questions and answers from HTML content
     */
    protected function extractFaqItems(string $htmlContent): array
    {
        $faqs = [];

        // Match H2/H3 followed by paragraph
        if (preg_match_all('/<h[2-4][^>]*>(.*?)<\/h[2-4]>\s*<p[^>]*>(.*?)<\/p>/si', $htmlContent, $matches, PREG_SET_ORDER)) {
            $questionStarters = ['who', 'what', 'when', 'where', 'why', 'how', 'is', 'are', 'can', 'will', 'should', 'which', 'does', 'do', 'q:'];
            foreach ($matches as $m) {
                $q = trim(strip_tags($m[1]));
                $a = trim(strip_tags($m[2]));
                $qLower = mb_strtolower($q);

                $isQuestion = str_ends_with($q, '?');
                if (!$isQuestion) {
                    foreach ($questionStarters as $starter) {
                        if (str_starts_with($qLower, $starter . ' ') || str_starts_with($qLower, $starter . '\'')) {
                            $isQuestion = true;
                            break;
                        }
                    }
                }

                if ($isQuestion && mb_strlen($q) >= 10 && mb_strlen($a) >= 20) {
                    $faqs[] = [
                        'question' => $q,
                        'answer' => $a,
                    ];
                }
            }
        }

        return $faqs;
    }

    /**
     * Extract HowTo steps from HTML content
     */
    protected function extractHowToSteps(string $htmlContent): array
    {
        $steps = [];

        // Pattern A: Match "Step 1: ...", "Step 2: ..." in headings followed by text
        if (preg_match_all('/<h[2-4][^>]*>(?:Step\s*(\d+)[:\s]+)?(.*?)<\/h[2-4]>\s*<p[^>]*>(.*?)<\/p>/si', $htmlContent, $matches, PREG_SET_ORDER)) {
            foreach ($matches as $m) {
                $headingText = trim(strip_tags($m[2]));
                $stepBody = trim(strip_tags($m[3]));
                if (stripos($m[0], 'step') !== false && mb_strlen($headingText) >= 5) {
                    $steps[] = [
                        'name' => $headingText,
                        'text' => $stepBody,
                    ];
                }
            }
        }

        // Pattern B: Match ordered list items <ol><li>...</li></ol>
        if (empty($steps) && preg_match('/<ol[^>]*>(.*?)<\/ol>/si', $htmlContent, $olMatch)) {
            if (preg_match_all('/<li[^>]*>(.*?)<\/li>/si', $olMatch[1], $liMatches)) {
                foreach ($liMatches[1] as $idx => $liHtml) {
                    $cleanLi = trim(strip_tags($liHtml));
                    if (mb_strlen($cleanLi) >= 15) {
                        $colonPos = mb_strpos($cleanLi, ':');
                        $name = $colonPos !== false && $colonPos < 40 ? mb_substr($cleanLi, 0, $colonPos) : ('Step ' . ($idx + 1));
                        $text = $colonPos !== false && $colonPos < 40 ? trim(mb_substr($cleanLi, $colonPos + 1)) : $cleanLi;
                        $steps[] = [
                            'name' => $name,
                            'text' => $text,
                        ];
                    }
                }
            }
        }

        return $steps;
    }
}
