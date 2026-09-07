<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - WordPress SEO Generator
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

namespace HOA_Studio\Admin;

if (! defined('ABSPATH')) {
    exit;
}

class HOA_Seo_Generator
{
    /**
     * Compute Real-time Content Quality & SEO Metrics
     */
    public static function auditContent(string $title, string $htmlContent, string $targetKeyword = ''): array
    {
        $plainText = trim(wp_strip_all_tags($htmlContent));
        $words = str_word_count($plainText);
        $charCount = strlen($plainText);

        $kwCount = 0;
        $kwDensity = 0.0;
        if (! empty($targetKeyword) && $words > 0) {
            $kwPattern = '/'.preg_quote($targetKeyword, '/').'/i';
            $kwCount = preg_match_all($kwPattern, $plainText);
            $kwWords = max(1, str_word_count($targetKeyword));
            $kwDensity = round(($kwCount * $kwWords / $words) * 100, 2);
        }

        $headingCounts = [
            'h1' => preg_match_all('/<h1[^>]*>/i', $htmlContent),
            'h2' => preg_match_all('/<h2[^>]*>/i', $htmlContent),
            'h3' => preg_match_all('/<h3[^>]*>/i', $htmlContent),
        ];

        $imageCount = preg_match_all('/<img[^>]*>/i', $htmlContent);
        $linkCount = preg_match_all('/<a[^>]*href=["\'][^"\']+["\'][^>]*>/i', $htmlContent);

        // Compute Score (0 - 100)
        $score = 50;
        if ($words >= 300) {
            $score += 10;
        }
        if ($words >= 800) {
            $score += 10;
        }
        if ($headingCounts['h2'] >= 2) {
            $score += 10;
        }
        if ($imageCount >= 1) {
            $score += 5;
        }
        if ($linkCount >= 1) {
            $score += 5;
        }
        if (! empty($targetKeyword) && $kwDensity >= 0.5 && $kwDensity <= 2.5) {
            $score += 10;
        }

        return [
            'score' => min(100, $score),
            'words' => $words,
            'characters' => $charCount,
            'keyword_count' => $kwCount,
            'keyword_density' => $kwDensity,
            'headings' => $headingCounts,
            'images' => $imageCount,
            'links' => $linkCount,
        ];
    }
}
