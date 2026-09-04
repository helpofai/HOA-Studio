<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Advanced SEO Analyzer v2.0
|--------------------------------------------------------------------------
|
| Enhanced with AI-driven semantic analysis, multi-algorithm readability scoring,
| entity recognition simulation, E-E-A-T scoring, and competitive gap analysis.
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

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class SeoAnalyzer
{
    /**
     * Power words dictionary for Title Readability
     */
    protected array $powerWords = [
        'ultimate', 'proven', 'essential', 'master', 'breakthrough', 'complete',
        'guide', 'review', 'best', 'top', 'fast', 'easy', 'secret', 'definitive',
        'expert', 'advanced', 'strategy', 'blueprint', 'guaranteed', 'epic',
        'unleashed', 'framework', 'formula', 'insider', 'supercharged', 'step-by-step',
        'revolutionary', 'groundbreaking', 'comprehensive', 'definitive', 'authoritative'
    ];

    /**
     * Stop words for keyword analysis
     */
    protected array $stopWords = [
        'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by',
        'is', 'are', 'was', 'were', 'been', 'be', 'have', 'has', 'had', 'do', 'does', 'did',
        'will', 'would', 'could', 'should', 'may', 'might', 'must', 'can', 'this', 'that',
        'these', 'those', 'i', 'you', 'he', 'she', 'it', 'we', 'they', 'me', 'him', 'her',
        'us', 'them', 'my', 'your', 'his', 'her', 'its', 'our', 'their', 'mine', 'yours'
    ];

    /**
     * Common question words for voice search optimization
     */
    protected array $questionWords = [
        'who', 'what', 'when', 'where', 'why', 'how', 'is', 'are', 'can', 'will',
        'would', 'should', 'could', 'does', 'did', 'has', 'have', 'was', 'were'
    ];

    /**
     * Analyze document content and calculate comprehensive SEO & Readability metrics
     * 
     * @param string $htmlContent HTML content to analyze
     * @param string $title Document title
     * @param ?string $targetKeyword Primary focus keyword
     * @param array $secondaryKeywords Secondary/LSI keywords
     * @param string $metaDescription Meta description
     * @return array Analysis results with scores and recommendations
     */
    public function analyze(string $htmlContent, string $title = '', ?string $targetKeyword = null, array $secondaryKeywords = [], string $metaDescription = ''): array
    {
        // Spaced HTML for accurate word and sentence segmentation
        $spacedHtml = preg_replace('/<\/(h[1-6]|p|div|li|blockquote|section|article|td|th|tr)>/i', "$0. ", $htmlContent);
        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags($spacedHtml)));
        $words = !empty($plainText) ? preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY) : [];
        $totalWords = count($words);

        // Sentences
        $sentences = !empty($plainText) ? preg_split('/(?<=[.?!])\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY) : [];
        $totalSentences = max(1, count($sentences));

        // Paragraphs extraction
        preg_match_all('/<p[^>]*>(.*?)<\/p>/si', $htmlContent, $pMatches);
        $rawParagraphs = array_map('strip_tags', $pMatches[1] ?? []);
        $totalParagraphs = max(1, count($rawParagraphs));

        // Syllables estimation for Flesch Readability
        $totalSyllables = 0;
        foreach ($words as $w) {
            $totalSyllables += $this->countSyllables($w);
        }

        // Flesch Reading Ease Score
        $fleschScore = 0;
        $readingGrade = 'N/A';
        if ($totalWords > 0) {
            $wordsPerSentence = $totalWords / $totalSentences;
            $syllablesPerWord = $totalSyllables / $totalWords;
            $fleschScore = 206.835 - (1.015 * $wordsPerSentence) - (84.6 * $syllablesPerWord);
            $fleschScore = (int) round(max(0, min(100, $fleschScore)));

            if ($fleschScore >= 90) $readingGrade = 'Very Easy (5th grade)';
            elseif ($fleschScore >= 80) $readingGrade = 'Easy (6th grade)';
            elseif ($fleschScore >= 70) $readingGrade = 'Fairly Easy (7th grade)';
            elseif ($fleschScore >= 60) $readingGrade = 'Standard (8th-9th grade)';
            elseif ($fleschScore >= 50) $readingGrade = 'Fairly Difficult (High school)';
            elseif ($fleschScore >= 30) $readingGrade = 'Difficult (College level)';
            else $readingGrade = 'Very Difficult (Academic/Graduate)';
        }

        // Advanced Readability Scores
        $gunningFog = $this->calculateGunningFog($totalWords, $totalSentences, $rawParagraphs);
        $smogIndex = $this->calculateSMOG($totalSentences, $rawParagraphs);
        $ariScore = $this->calculateARI($totalWords, $totalSentences, $totalSyllables);
        $colemanLiau = $this->calculateColemanLiau($totalWords, $totalSentences, $htmlContent);

        // Extract Headings
        preg_match_all('/<h1[^>]*>(.*?)<\/h1>/si', $htmlContent, $h1Matches);
        preg_match_all('/<h2[^>]*>(.*?)<\/h2>/si', $htmlContent, $h2Matches);
        preg_match_all('/<h3[^>]*>(.*?)<\/h3>/si', $htmlContent, $h3Matches);
        preg_match_all('/<h[4-6][^>]*>.*?<\/h[4-6]>/si', $htmlContent, $h456Matches);

        $h1List = array_map('strip_tags', $h1Matches[1] ?? []);
        $h2List = array_map('strip_tags', $h2Matches[1] ?? []);
        $h3List = array_map('strip_tags', $h3Matches[1] ?? []);
        $h456List = array_map('strip_tags', $h456Matches[1] ?? []);

        // Extract Links & Images
        preg_match_all('/<a\s+[^>]*href=["\']([^"\']*)["\']/si', $htmlContent, $linkMatches);
        preg_match_all('/<img\s+[^>]*alt=["\']([^"\']*)["\']/si', $htmlContent, $imgMatches);
        
        $links = $linkMatches[1] ?? [];
        $imgAlts = $imgMatches[1] ?? [];
        $totalLinks = count($links);
        $totalImages = count($imgAlts);

        // Internal vs External Links analysis
        $internalLinksCount = 0;
        $externalLinksCount = 0;
        foreach ($links as $link) {
            if (Str::startsWith($link, ['/', '#', 'http://localhost', 'http://127.0.0.1', 'https://helpofai.com'])) {
                $internalLinksCount++;
            } else {
                $externalLinksCount++;
            }
        }

        // Long sentences (> 20 words)
        $longSentencesCount = 0;
        foreach ($sentences as $s) {
            $sWords = preg_split('/\s+/u', trim($s), -1, PREG_SPLIT_NO_EMPTY);
            if (count($sWords) > 20) {
                $longSentencesCount++;
            }
        }
        $longSentencesPct = $totalSentences > 0 ? round(($longSentencesCount / $totalSentences) * 100) : 0;

        // Long Paragraphs (> 120 words)
        $longParagraphsCount = 0;
        foreach ($rawParagraphs as $p) {
            $pWords = count(preg_split('/\s+/u', trim($p), -1, PREG_SPLIT_NO_EMPTY));
            if ($pWords > 120) {
                $longParagraphsCount++;
            }
        }

        // Target Keyword Data
        $kw = $targetKeyword ? trim(mb_strtolower($targetKeyword)) : null;
        $kwWordsCount = $kw ? count(preg_split('/\s+/u', $kw, -1, PREG_SPLIT_NO_EMPTY)) : 1;
        $slug = Str::slug($title ?: 'untitled');

        $kwData = [
            'target_keyword' => $targetKeyword,
            'count' => 0,
            'density' => 0.0,
            'in_title' => false,
            'in_first_10_pct' => false,
            'in_first_100_words' => false,
            'in_meta' => false,
            'in_url' => false,
            'in_h1' => false,
            'in_h2' => false,
            'in_subheadings' => false,
            'in_img_alt' => false,
            'semantic_variations_found' => 0,
            'keyword_stuffing_risk' => false,
        ];

        if ($kw && $totalWords > 0) {
            $lowerText = mb_strtolower($plainText);
            $lowerTitle = mb_strtolower($title);
            $lowerMeta = mb_strtolower($metaDescription);
            $lowerSlug = mb_strtolower(str_replace('-', ' ', $slug));
            $lowerH1 = mb_strtolower(implode(' ', $h1List));
            $lowerSubheadings = mb_strtolower(implode(' ', array_merge($h2List, $h3List)));
            $lowerImgAlts = mb_strtolower(implode(' ', $imgAlts));

            // First 10% of content
            $tenPercentWordsCount = max(10, (int) round($totalWords * 0.1));
            $first10PctWords = mb_strtolower(implode(' ', array_slice($words, 0, $tenPercentWordsCount)));

            $kwOccurrences = mb_substr_count($lowerText, $kw);
            $kwData['count'] = $kwOccurrences;
            
            // Rank Math Keyword Density Formula: (Keyword Count * Words in Keyword / Total Words) * 100
            $kwData['density'] = round((($kwOccurrences * $kwWordsCount) / max(1, $totalWords)) * 100, 2);
            
            // Keyword stuffing detection (> 3% density is risky)
            $kwData['keyword_stuffing_risk'] = $kwData['density'] > 3.0;
            
            // Semantic variations detection (simple stem matching)
            $kwData['semantic_variations_found'] = $this->countSemanticVariations($lowerText, $kw);

            $kwData['in_title'] = mb_strpos($lowerTitle, $kw) !== false;
            $kwData['in_first_10_pct'] = mb_strpos($first10PctWords, $kw) !== false;
            $kwData['in_first_100_words'] = $kwData['in_first_10_pct'] || (mb_strpos(mb_strtolower(implode(' ', array_slice($words, 0, 100))), $kw) !== false);
            $kwData['in_meta'] = !empty($metaDescription) && mb_strpos($lowerMeta, $kw) !== false;
            $kwData['in_url'] = mb_strpos($lowerSlug, str_replace(' ', ' ', $kw)) !== false || mb_strpos(Str::slug($title), Str::slug($kw)) !== false;
            $kwData['in_h1'] = mb_strpos($lowerH1, $kw) !== false;
            $kwData['in_h2'] = mb_strpos(mb_strtolower(implode(' ', $h2List)), $kw) !== false;
            $kwData['in_subheadings'] = mb_strpos($lowerSubheadings, $kw) !== false;
            $kwData['in_img_alt'] = mb_strpos($lowerImgAlts, $kw) !== false;
        }

        // Title Readability Extra Analysis
        $titleStartsWithKw = false;
        $titleHasNumber = preg_match('/\d+/', $title) === 1;
        $titleHasPowerWord = false;
        $matchedPowerWord = '';
        $titleSentiment = $this->analyzeTitleSentiment($title);

        if (!empty($title)) {
            $lowerTitle = mb_strtolower($title);
            if ($kw && (mb_strpos($lowerTitle, $kw) === 0 || mb_strpos($lowerTitle, $kw) < 15)) {
                $titleStartsWithKw = true;
            }
            foreach ($this->powerWords as $pw) {
                if (mb_strpos($lowerTitle, $pw) !== false) {
                    $titleHasPowerWord = true;
                    $matchedPowerWord = $pw;
                    break;
                }
            }
        }

        // E-E-A-T Signals (Experience, Expertise, Authoritativeness, Trustworthiness)
        $eEatScore = $this->calculateEEATScore($htmlContent, $title, $targetKeyword);

        // Voice Search Optimization Score
        $voiceSearchScore = $this->calculateVoiceSearchScore($htmlContent, $title, $targetKeyword);

        // Featured Snippet Potential
        $featuredSnippetScore = $this->calculateFeaturedSnippetPotential($htmlContent, $targetKeyword);

        // Content Freshness Indicators
        $contentFreshness = $this->calculateContentFreshness($htmlContent);

        // Competitive Gap Analysis Simulation
        $competitiveGap = $this->simulateCompetitiveGapAnalysis($targetKeyword, $htmlContent);

        // ══════════════════════════════════════════════════════════════════════════
        // ADVANCED 6-PILLAR CHECKLIST COMPUTATION
        // ══════════════════════════════════════════════════════════════════════════

        // PILLAR 1: Basic SEO (25 Points Max)
        $basicChecks = [
            [
                'id' => 'kw_in_title',
                'title' => 'Focus Keyword in SEO Title',
                'desc' => 'Primary keyword appears in the document title tag.',
                'pass' => $kw ? $kwData['in_title'] : false,
                'weight' => 6,
                'severity' => 'critical',
                'current_val' => $kw ? ($kwData['in_title'] ? 'Found in title' : 'Missing from title') : 'No keyword set',
                'goal_val' => "Include '" . ($targetKeyword ?: 'keyword') . "'",
                'actionable_tip' => "Include your primary keyword '" . ($targetKeyword ?: 'keyword') . "' in the document title for core search indexing.",
                'target_canvas_id' => 'seo-loc-title',
                'ai_prompt' => $kw ? "Rewrite the document title to naturally include the focus keyword '{$targetKeyword}'." : null,
                'manual_prompt' => "Manually edit the title to include your target keyword."
            ],
            [
                'id' => 'kw_in_meta',
                'title' => 'Focus Keyword in Meta Description',
                'desc' => 'Primary keyword appears in the meta description.',
                'pass' => $kw ? $kwData['in_meta'] : (!empty($metaDescription)),
                'weight' => 5,
                'severity' => 'warning',
                'current_val' => !empty($metaDescription) ? ($kw && mb_strpos(mb_strtolower($metaDescription), $kw) !== false ? 'Found in meta' : 'Missing keyword') : 'Empty meta',
                'goal_val' => '140-160 chars with keyword',
                'actionable_tip' => "Write a 140-160 character meta description featuring '" . ($targetKeyword ?: 'keyword') . "' to improve click-through rates.",
                'target_canvas_id' => 'seo-loc-meta',
                'ai_prompt' => $kw ? "Write a compelling meta description that includes the focus keyword '{$targetKeyword}' and encourages clicks." : null,
                'manual_prompt' => "Manually write a meta description including your target keyword."
            ],
            [
                'id' => 'kw_in_url',
                'title' => 'Focus Keyword in URL Slug',
                'desc' => 'Primary keyword is present in the permalink slug.',
                'pass' => $kw ? $kwData['in_url'] : true,
                'weight' => 4,
                'severity' => 'warning',
                'current_val' => $slug ?: 'untitled',
                'goal_val' => "Clean slug with '" . ($targetKeyword ?: 'keyword') . "'",
                'actionable_tip' => "Include your focus keyword in the URL permalink slug for clean hierarchy.",
                'target_canvas_id' => 'seo-loc-meta',
                'ai_prompt' => null, // URL changes require manual intervention
                'manual_prompt' => "Edit the URL slug to include your target keyword."
            ],
            [
                'id' => 'kw_in_intro',
                'title' => 'Focus Keyword in First 10% (Intro)',
                'desc' => 'Primary keyword appears in the opening introduction sentences.',
                'pass' => $kw ? $kwData['in_first_10_pct'] : false,
                'weight' => 4,
                'severity' => 'critical',
                'current_val' => $kwData['in_first_10_pct'] ? 'Present in hook' : 'Missing in first 10%',
                'goal_val' => 'Within opening 100 words',
                'actionable_tip' => "Mention '" . ($targetKeyword ?: 'keyword') . "' in your opening paragraph so search engines and readers instantly confirm topic relevance.",
                'target_canvas_id' => 'seo-loc-kw_in_intro',
                'ai_prompt' => $kw ? "Rewrite the introduction to naturally include the focus keyword '{$targetKeyword}' in the first 10% of content." : null,
                'manual_prompt' => "Edit the opening paragraph to include your target keyword naturally."
            ],
            [
                'id' => 'kw_in_body',
                'title' => 'Focus Keyword in Content Body',
                'desc' => 'Primary keyword is referenced across paragraphs naturally.',
                'pass' => $kw ? ($kwData['count'] >= 2 && !$kwData['keyword_stuffing_risk']) : false,
                'weight' => 4,
                'severity' => 'critical',
                'current_val' => $kwData['count'] . ' mentions',
                'goal_val' => '2+ natural mentions',
                'actionable_tip' => "Refer to '" . ($targetKeyword ?: 'keyword') . "' at least twice throughout the body paragraphs to reinforce topic depth.",
                'target_canvas_id' => 'seo-loc-kw_in_intro',
                'ai_prompt' => $kw && $kwData['count'] < 2 ? "Increase natural usage of the focus keyword '{$targetKeyword}' throughout the content." : null,
                'manual_prompt' => "Add more natural mentions of your target keyword throughout the content."
            ],
            [
                'id' => 'content_length',
                'title' => 'Content Length Check (600+ words)',
                'desc' => "Current length is {$totalWords} words (Optimal: 1,200+ words for comprehensive coverage).",
                'pass' => $totalWords >= 1200,
                'weight' => 2,
                'severity' => 'warning',
                'current_val' => $totalWords . ' words',
                'goal_val' => '1,200+ words',
                'actionable_tip' => $totalWords < 1200 ? "Add approximately " . (1200 - $totalWords) . " more words with sub-guides, examples, or tables to outrank competing articles." : "Comprehensive content length achieved.",
                'target_canvas_id' => 'seo-loc-kw_in_intro',
            ],
        ];

        // PILLAR 2: Additional SEO (20 Points Max)
        $densityValid = $kw ? ($kwData['density'] >= 0.8 && $kwData['density'] <= 2.5) : true;
        $urlLengthValid = strlen($slug) <= 75;
        
        $additionalChecks = [
            [
                'id' => 'kw_in_subheadings',
                'title' => 'Focus Keyword in Subheadings (H2, H3)',
                'desc' => 'Primary keyword found in H2 or H3 heading tags.',
                'pass' => $kw ? $kwData['in_subheadings'] : (count($h2List) >= 2),
                'weight' => 4,
                'severity' => 'warning',
                'current_val' => $kwData['in_subheadings'] ? 'Included in H2/H3' : 'Missing in headings',
                'goal_val' => '1+ H2/H3 headings',
                'actionable_tip' => "Add your focus keyword '" . ($targetKeyword ?: 'keyword') . "' to at least one prominent H2 subheading.",
                'target_canvas_id' => 'seo-loc-kw_in_subheadings',
                'ai_prompt' => $kw ? "Add H2 or H3 subheadings that include the focus keyword '{$targetKeyword}'." : null,
                'manual_prompt' => "Create subheadings that include your target keyword."
            ],
            [
                'id' => 'kw_in_img_alt',
                'title' => 'Focus Keyword in Image Alt Attributes',
                'desc' => 'Images contain descriptive alt text with the focus keyword.',
                'pass' => $kw ? $kwData['in_img_alt'] : ($totalImages > 0),
                'weight' => 3,
                'severity' => 'optimization',
                'current_val' => $totalImages . ' images (' . ($kwData['in_img_alt'] ? 'Alt match' : 'No keyword in alt') . ')',
                'goal_val' => 'Descriptive alt with keyword',
                'actionable_tip' => "Add an image with descriptive alt text containing '" . ($targetKeyword ?: 'keyword') . "'.",
                'target_canvas_id' => 'seo-loc-kw_in_subheadings',
                'ai_prompt' => $kw ? "Add descriptive alt text to images that includes the focus keyword '{$targetKeyword}'." : null,
                'manual_prompt' => "Add alt text to images describing the content and including your target keyword."
            ],
            [
                'id' => 'keyword_density',
                'title' => 'Keyword Density (0.8% - 2.5%)',
                'desc' => 'Keyword density is balanced without keyword stuffing.',
                'pass' => $densityValid && ($kw ? $kwData['count'] > 0 : true),
                'weight' => 4,
                'severity' => $kwData['keyword_stuffing_risk'] ? 'critical' : 'warning',
                'current_val' => $kwData['density'] . '%',
                'goal_val' => '0.8% - 2.5%',
                'actionable_tip' => $kwData['density'] < 0.8 ? "Keyword density is low ({$kwData['density']}%). Weave '" . ($targetKeyword ?: 'keyword') . "' into 1-2 more sentences." : ($kwData['density'] > 2.5 ? "Density is high ({$kwData['density']}%). Reduce repetitive mentions to avoid over-optimization." : "Keyword density is balanced."),
                'target_canvas_id' => 'seo-loc-kw_in_intro',
                'ai_prompt' => $kw && $kwData['density'] < 0.8 ? "Increase usage of the focus keyword '{$targetKeyword}' to reach optimal density." : 
                            ($kw && $kwData['density'] > 2.5 ? "Reduce keyword usage to avoid stuffing while maintaining natural flow." : null),
                'manual_prompt' => "Adjust keyword usage to maintain 0.8%-2.5% density range."
            ],
            [
                'id' => 'url_length',
                'title' => 'URL Permalinks Length',
                'desc' => 'URL slug length is concise (under 75 characters).',
                'pass' => $urlLengthValid,
                'weight' => 2,
                'severity' => 'optimization',
                'current_val' => strlen($slug) . ' chars',
                'goal_val' => '< 75 chars',
                'actionable_tip' => "Keep permalink slugs concise and focused under 75 characters for cleaner URL sharing.",
                'target_canvas_id' => 'seo-loc-meta',
            ],
            [
                'id' => 'external_links',
                'title' => 'External Outbound Citations',
                'desc' => 'Authoritative external citations found in content.',
                'pass' => $externalLinksCount >= 2,
                'weight' => 3,
                'severity' => 'warning',
                'current_val' => $externalLinksCount . ' external links',
                'goal_val' => '2+ authoritative links',
                'actionable_tip' => "Add 2+ authoritative outbound links to reputable research, papers, or documentation to prove authenticity.",
                'target_canvas_id' => 'seo-loc-external_links',
                'ai_prompt' => $externalLinksCount < 2 ? "Add 2 authoritative external links to reputable sources in your niche." : null,
                'manual_prompt' => "Add links to authoritative sources that support your content."
            ],
            [
                'id' => 'internal_links',
                'title' => 'Internal Cluster Links',
                'desc' => 'Internal links linking to related topics and resources.',
                'pass' => $internalLinksCount >= 3,
                'weight' => 2,
                'severity' => 'warning',
                'current_val' => $internalLinksCount . ' internal links',
                'goal_val' => '3+ internal links',
                'actionable_tip' => "Add 3+ internal links to related guides or categories on your site to build topical clusters.",
                'target_canvas_id' => 'seo-loc-external_links',
                'ai_prompt' => $internalLinksCount < 3 ? "Add internal links to related content on your website." : null,
                'manual_prompt' => "Link to other relevant pages on your website."
            ],
        ];

        // PILLAR 3: Title Readability & Click-Worthiness (15 Points Max)
        $titleChecks = [
            [
                'id' => 'kw_at_beginning_of_title',
                'title' => 'Focus Keyword at Start of Title',
                'desc' => 'Primary keyword is front-loaded in the first half of the title.',
                'pass' => $kw ? $titleStartsWithKw : (!empty($title)),
                'weight' => 5,
                'severity' => 'warning',
                'current_val' => $titleStartsWithKw ? 'Front-loaded' : 'Not at beginning',
                'goal_val' => 'In first 3 words',
                'actionable_tip' => "Place '" . ($targetKeyword ?: 'keyword') . "' near the beginning of your title so it is immediately visible on search result pages.",
                'target_canvas_id' => 'seo-loc-title',
                'ai_prompt' => $kw ? "Rewrite title to start with the focus keyword '{$targetKeyword}' for better SEO and CTR." : null,
                'manual_prompt' => "Move your target keyword to the beginning of the title."
            ],
            [
                'id' => 'title_has_number',
                'title' => 'Title Contains a Number',
                'desc' => 'Numbers in titles increase click-through rates (CTR) and set clear expectations.',
                'pass' => $titleHasNumber,
                'weight' => 3,
                'severity' => 'optimization',
                'current_val' => $titleHasNumber ? 'Number present' : 'No number',
                'goal_val' => 'Include number/year (e.g. 2026, 7, 10)',
                'actionable_tip' => "Include a number or year (e.g. '2026', '5 Steps') to increase Google click-through rates by up to 36%.",
                'target_canvas_id' => 'seo-loc-title',
                'ai_prompt' => null,
                'manual_prompt' => "Add a relevant number to your title (e.g., '5 Ways', '10 Tips', '2024 Guide')."
            ],
            [
                'id' => 'title_has_power_word',
                'title' => 'Title Contains a Power Word',
                'desc' => $titleHasPowerWord ? "Power word detected ('{$matchedPowerWord}')." : 'Add a persuasive power word to boost CTR and engagement.',
                'pass' => $titleHasPowerWord,
                'weight' => 3,
                'severity' => 'optimization',
                'current_val' => $titleHasPowerWord ? "Power word: '{$matchedPowerWord}'" : 'No power word',
                'goal_val' => "Include 'Ultimate', 'Proven', or 'Complete'",
                'actionable_tip' => "Add an engaging power word like 'Ultimate', 'Proven', 'Complete', or 'Essential' to stimulate click motivation.",
                'target_canvas_id' => 'seo-loc-title',
                'ai_prompt' => $titleHasPowerWord ? null : "Add a power word like 'Ultimate', 'Proven', 'Essential', or 'Complete' to your title.",
                'manual_prompt' => "Include an emotional/power word in your title."
            ],
            [
                'id' => 'title_length_optimal',
                'title' => 'Title Length Optimal (50-60 characters)',
                'desc' => 'Title length is ideal for search engine display (50-60 characters).',
                'pass' => strlen($title) >= 50 && strlen($title) <= 60,
                'weight' => 2,
                'severity' => 'optimization',
                'current_val' => strlen($title) . ' characters',
                'goal_val' => '50 - 60 characters',
                'actionable_tip' => strlen($title) < 50 ? "Title is short (" . strlen($title) . " chars). Aim for 50-60 chars to maximize pixel width in SERP." : "Title exceeds 60 chars (" . strlen($title) . "). Shorten to avoid SERP ellipsis truncation.",
                'target_canvas_id' => 'seo-loc-title',
                'ai_prompt' => strlen($title) < 50 ? "Extend title to 50-60 characters for better search visibility." : 
                            (strlen($title) > 60 ? "Shorten title to 50-60 characters to avoid truncation in search results." : null),
                'manual_prompt' => "Adjust title length to 50-60 characters."
            ],
            [
                'id' => 'title_sentiment_positive',
                'title' => 'Title Has Positive Sentiment',
                'desc' => $titleSentiment['label'] === 'positive' ? 'Title conveys positive emotion and value.' : 'Consider more positive, benefit-driven language.',
                'pass' => $titleSentiment['label'] === 'positive',
                'weight' => 2,
                'severity' => 'optimization',
                'current_val' => ucfirst($titleSentiment['label'] ?? 'neutral'),
                'goal_val' => 'Positive & benefit-focused',
                'actionable_tip' => "Use positive, value-driven language to emphasize the user benefit of reading.",
                'target_canvas_id' => 'seo-loc-title',
                'ai_prompt' => $titleSentiment['label'] !== 'positive' ? "Rewrite title with more positive, benefit-oriented language." : null,
                'manual_prompt' => "Make your title more positive and benefit-focused."
            ],
        ];

        // PILLAR 4: Content Readability (15 Points Max)
        $contentReadabilityChecks = [
            [
                'id' => 'headings_toc',
                'title' => 'Use Table of Contents / Headings Structure',
                'desc' => 'Content utilizes H2 and H3 tags for scannability and structure.',
                'pass' => count($h2List) >= 2,
                'weight' => 4,
                'severity' => 'warning',
                'current_val' => count($h2List) . ' H2 headings',
                'goal_val' => '2+ H2 headings',
                'actionable_tip' => "Break content into scannable subtopics using at least two H2 headings.",
                'target_canvas_id' => 'seo-loc-kw_in_subheadings',
                'ai_prompt' => count($h2List) < 2 ? "Add more H2 and H3 subheadings to improve content structure." : null,
                'manual_prompt' => "Create a clear hierarchical structure with H2 and H3 headings."
            ],
            [
                'id' => 'short_paragraphs',
                'title' => 'Short & Scannable Paragraphs',
                'desc' => 'Paragraphs are bite-sized (under 100 words each) for better readability.',
                'pass' => $longParagraphsCount === 0 && $totalParagraphs >= 3,
                'weight' => 4,
                'severity' => 'warning',
                'current_val' => $longParagraphsCount . ' bulky paragraphs (>100 words)',
                'goal_val' => '0 bulky paragraphs',
                'actionable_tip' => "Split long paragraphs into 2-3 sentence chunks to keep mobile readers engaged.",
                'target_canvas_id' => 'seo-loc-kw_in_intro',
                'ai_prompt' => $longParagraphsCount > 0 ? "Break long paragraphs into shorter, more digestible chunks." : null,
                'manual_prompt' => "Split long paragraphs into shorter ones (aim for 60-100 words each)."
            ],
            [
                'id' => 'sentence_length',
                'title' => 'Sentence Length Check',
                'desc' => "Only {$longSentencesPct}% of sentences exceed 20 words (target: < 15%).",
                'pass' => $longSentencesPct <= 15,
                'weight' => 4,
                'severity' => 'warning',
                'current_val' => $longSentencesPct . '% sentences >20 words',
                'goal_val' => '< 15% long sentences',
                'actionable_tip' => "Split run-on sentences into shorter, punchy statements for higher readability scores.",
                'target_canvas_id' => 'seo-loc-kw_in_intro',
                'ai_prompt' => $longSentencesPct > 15 ? "Break long sentences into shorter, clearer statements." : null,
                'manual_prompt' => "Split long sentences into shorter ones for better readability."
            ],
            [
                'id' => 'rich_media',
                'title' => 'Rich Media Usage',
                'desc' => 'Content contains images, videos, tables, or other engaging media.',
                'pass' => $totalImages >= 2 || (strpos($htmlContent, '<table') !== false) || (strpos($htmlContent, '<video') !== false),
                'weight' => 3,
                'severity' => 'optimization',
                'current_val' => $totalImages . ' images, ' . (strpos($htmlContent, '<table') !== false ? 'Table present' : 'No table'),
                'goal_val' => 'Images, table, or comparison card',
                'actionable_tip' => "Add an informative comparison table, chart, or image to break up text and increase time-on-page.",
                'target_canvas_id' => 'seo-loc-kw_in_subheadings',
                'ai_prompt' => $totalImages < 2 && !(strpos($htmlContent, '<table') !== false) && !(strpos($htmlContent, '<video') !== false) ? 
                            "Add relevant images, tables, or videos to enhance engagement." : null,
                'manual_prompt' => "Include images, videos, tables, or other media to break up text."
            ],
        ];

        // PILLAR 5: E-E-A-T & Authority Signals (15 Points Max)
        $eatChecks = [
            [
                'id' => 'eeat_score',
                'title' => 'E-E-A-T Signals (Experience, Expertise, Authority, Trust)',
                'desc' => "E-E-A-T score: {$eEatScore}/100 - Demonstrates expertise and trustworthiness.",
                'pass' => $eEatScore >= 70,
                'weight' => 5,
                'severity' => 'warning',
                'current_val' => $eEatScore . '/100',
                'goal_val' => '70+ E-E-A-T score',
                'actionable_tip' => "Add first-hand experience observations, author bio, research data, and case study evidence.",
                'target_canvas_id' => 'seo-loc-external_links',
                'ai_prompt' => $eEatScore < 70 ? 
                            "Add author credentials, cite authoritative sources, include case studies, and show transparency." : null,
                'manual_prompt' => "Enprove E-E-A-T by adding author bios, credentials, citations, and transparent information."
            ],
            [
                'id' => 'voice_search_optimized',
                'title' => 'Voice Search Optimization',
                'desc' => "Voice search readiness: {$voiceSearchScore}% - Optimized for conversational queries.",
                'pass' => $voiceSearchScore >= 60,
                'weight' => 4,
                'severity' => 'optimization',
                'current_val' => $voiceSearchScore . '%',
                'goal_val' => '60%+ voice readiness',
                'actionable_tip' => "Add an FAQ section with direct conversational answers to match voice assistant queries.",
                'target_canvas_id' => 'seo-loc-external_links',
                'ai_prompt' => $voiceSearchScore < 60 ? 
                            "Add FAQ sections, conversational language, and direct answers to common questions." : null,
                'manual_prompt' => "Optimize for voice search by adding Q&A sections and natural language patterns."
            ],
            [
                'id' => 'featured_snippet_potential',
                'title' => 'Featured Snippet Potential',
                'desc' => "Featured snippet readiness: {$featuredSnippetScore}% - Likely to appear in position zero.",
                'pass' => $featuredSnippetScore >= 50,
                'weight' => 3,
                'severity' => 'optimization',
                'current_val' => $featuredSnippetScore . '%',
                'goal_val' => '50%+ position-zero readiness',
                'actionable_tip' => "Add a concise 40-50 word direct definition box or numbered list targeting Google's Position Zero.",
                'target_canvas_id' => 'seo-loc-kw_in_intro',
                'ai_prompt' => $featuredSnippetScore < 50 ? 
                            "Add direct answers, lists, tables, or definitions that target featured snippet opportunities." : null,
                'manual_prompt' => "Structure content to target featured snippets with clear, concise answers."
            ],
            [
                'id' => 'content_freshness',
                'title' => 'Content Freshness Indicators',
                'desc' => "Content freshness: {$contentFreshness}% - Shows current, up-to-date information.",
                'pass' => $contentFreshness >= 60,
                'weight' => 3,
                'severity' => 'optimization',
                'current_val' => $contentFreshness . '%',
                'goal_val' => '60%+ freshness signals',
                'actionable_tip' => "Include 2026 data points, recent case studies, or contemporary statistics.",
                'target_canvas_id' => 'seo-loc-external_links',
                'ai_prompt' => $contentFreshness < 60 ? 
                            "Update statistics, add recent examples, and refresh outdated information." : null,
                'manual_prompt' => "Update content with recent data, examples, and current information."
            ],
        ];

        // PILLAR 6: Google AI Overviews & GEO (Generative Engine Optimization) (15 Points Max)
        $geoMetrics = $this->calculateGeoMetrics($htmlContent, $h2List, $plainText, $words);
        
        $geoChecks = [
            [
                'id' => 'geo_direct_answer',
                'title' => 'AI Overview Direct Answer Snippet',
                'desc' => $geoMetrics['has_direct_answer'] ? 'Direct 40-60 word definition answer detected beneath subheadings.' : 'No direct 40-60 word definition found directly under H2 subheadings.',
                'pass' => $geoMetrics['has_direct_answer'],
                'weight' => 4,
                'severity' => 'critical',
                'current_val' => $geoMetrics['has_direct_answer'] ? 'Direct snippet present' : 'Missing direct definition',
                'goal_val' => '40-60 word direct definition',
                'actionable_tip' => "Place a concise 40-60 word direct answer or definition immediately below your first H2 question heading. Google AI Overviews (SGE) extract this exact structure.",
                'target_canvas_id' => 'seo-loc-geo_direct_answer',
                'ai_prompt' => "Draft a concise 40-50 word direct definition answer box satisfying Google AI Overview snippet guidelines for the primary question.",
                'manual_prompt' => "Add a 40-60 word direct definition or answer right below your first major H2 heading."
            ],
            [
                'id' => 'geo_data_points',
                'title' => 'Information Gain & Data Point Density',
                'desc' => "Found {$geoMetrics['data_point_count']} verifiable statistics, metrics, or data points (target: 3+).",
                'pass' => $geoMetrics['data_point_count'] >= 3,
                'weight' => 3,
                'severity' => 'warning',
                'current_val' => $geoMetrics['data_point_count'] . ' data points/metrics',
                'goal_val' => '3+ statistics or metrics',
                'actionable_tip' => "Include at least 3 verifiable metrics, statistics, percentages, or research benchmarks to prove unique information gain to AI crawlers.",
                'target_canvas_id' => 'seo-loc-kw_in_intro',
                'ai_prompt' => "Add 2-3 credible statistics, benchmark percentages, or research data points into the content body.",
                'manual_prompt' => "Incorporate specific statistics, percentages, and metrics to demonstrate original research."
            ],
            [
                'id' => 'geo_structured_synthesis',
                'title' => 'Structured Comparison Table / Matrix',
                'desc' => $geoMetrics['has_structured_table'] ? 'HTML comparison table or feature matrix detected.' : 'No comparison table detected. Structured tables dominate AI search citations.',
                'pass' => $geoMetrics['has_structured_table'],
                'weight' => 3,
                'severity' => 'warning',
                'current_val' => $geoMetrics['has_structured_table'] ? 'Table present' : 'No comparison table',
                'goal_val' => '1+ comparison table',
                'actionable_tip' => "Insert a comparison table or structured feature matrix. AI search engines (Gemini & Perplexity) heavily prioritize tables for answer snapshots.",
                'target_canvas_id' => 'seo-loc-geo_structured_synthesis',
                'ai_prompt' => "Generate a clean HTML comparison table summarizing the core features, options, or pros/cons discussed.",
                'manual_prompt' => "Add an HTML table comparing features, options, or metrics."
            ],
            [
                'id' => 'geo_paa_questions',
                'title' => 'People Also Ask (PAA) Question Headings',
                'desc' => "Found {$geoMetrics['paa_question_count']} conversational question headings (target: 2+).",
                'pass' => $geoMetrics['paa_question_count'] >= 2,
                'weight' => 3,
                'severity' => 'optimization',
                'current_val' => $geoMetrics['paa_question_count'] . ' question headings',
                'goal_val' => '2+ question headings',
                'actionable_tip' => "Include at least 2 conversational question subheadings (e.g. 'How does...', 'What is the best...') to match Google People Also Ask queries.",
                'target_canvas_id' => 'seo-loc-kw_in_subheadings',
                'ai_prompt' => "Add 2 People Also Ask (PAA) question headings with direct answers.",
                'manual_prompt' => "Structure subheadings as common search questions (e.g., 'How do I...', 'What is...')."
            ],
            [
                'id' => 'geo_authoritative_quotes',
                'title' => 'Expert Quotation & Source Attribution',
                'desc' => $geoMetrics['has_expert_quote'] ? 'Authoritative quotes or source attributions detected.' : 'No expert quotes or source attributions found.',
                'pass' => $geoMetrics['has_expert_quote'],
                'weight' => 2,
                'severity' => 'optimization',
                'current_val' => $geoMetrics['has_expert_quote'] ? 'Attribution found' : '0 expert quotes',
                'goal_val' => '1+ expert quote or study citation',
                'actionable_tip' => "Include at least one authoritative quote, expert statement, or study citation in blockquote format to establish thought leadership.",
                'target_canvas_id' => 'seo-loc-external_links',
                'ai_prompt' => "Add an authoritative quote or expert statement citing research or industry leaders.",
                'manual_prompt' => "Add a blockquote containing an expert quote or industry authority insight."
            ],
        ];

        // Auto-Generate Verified Schema.org JSON-LD (Articles, FAQPage, HowTo)
        $schemaData = app(SchemaGenerator::class)->generate($htmlContent, $title, $metaDescription);

        // PILLAR 7: Technical & Competitive Analysis (10 Points Max)
        $technicalChecks = [
            [
                'id' => 'competitive_gap_analysis',
                'title' => 'Competitive Gap Analysis',
                'desc' => "Content depth score: {$competitiveGap['score']}% - Addresses key topics competitors cover.",
                'pass' => $competitiveGap['score'] >= 70,
                'weight' => 4,
                'severity' => 'warning',
                'current_val' => $competitiveGap['score'] . '% coverage',
                'goal_val' => '70%+ depth score',
                'actionable_tip' => !empty($competitiveGap['missing_topics']) ? "Cover competitor topic gaps: " . implode(', ', array_slice($competitiveGap['missing_topics'], 0, 3)) : "Cover key subtopics in your industry.",
                'target_canvas_id' => 'seo-loc-kw_in_subheadings',
                'ai_prompt' => $competitiveGap['score'] < 70 ? 
                            "Cover missing subtopics: " . implode(', ', $competitiveGap['missing_topics']) : null,
                'manual_prompt' => "Research top-ranking pages and cover subtopics they address that you missed."
            ],
            [
                'id' => 'semantic_depth',
                'title' => 'Semantic Keyword Depth',
                'desc' => "Found {$kwData['semantic_variations_found']} semantic variations of your target keyword.",
                'pass' => $kw ? $kwData['semantic_variations_found'] >= 5 : true,
                'weight' => 3,
                'severity' => 'optimization',
                'current_val' => $kwData['semantic_variations_found'] . ' LSI variations',
                'goal_val' => '5+ semantic variations',
                'actionable_tip' => "Include natural LSI synonyms and related semantic phrases throughout the document.",
                'target_canvas_id' => 'seo-loc-kw_in_intro',
                'ai_prompt' => $kw && $kwData['semantic_variations_found'] < 5 ? 
                            "Use related terms and LSI keywords to enhance topical relevance." : null,
                'manual_prompt' => "Include related terms, synonyms, and LSI keywords throughout your content."
            ],
            [
                'id' => 'schema_markup',
                'title' => 'Schema Markup Readiness',
                'desc' => $schemaData['validation']['is_valid'] ? 'Verified Schema.org JSON-LD generated (' . $schemaData['recommended_type'] . ').' : 'Content includes structured data (Schema.org) for rich snippets.',
                'pass' => $schemaData['validation']['is_valid'] || strpos($htmlContent, 'application/ld+json') !== false || strpos($htmlContent, 'schema.org') !== false,
                'weight' => 3,
                'severity' => 'optimization',
                'current_val' => $schemaData['recommended_type'] . ' Ready',
                'goal_val' => 'Valid JSON-LD schema',
                'actionable_tip' => "Open the Schema Studio to view, copy, or inject your auto-generated Schema.org JSON-LD markup.",
                'target_canvas_id' => 'seo-loc-meta',
                'ai_prompt' => null,
                'manual_prompt' => "Add or verify Schema.org JSON-LD markup for rich snippets."
            ],
        ];

        // Extract Semantic NLP Entities & LSI Keyword Density Matrix
        $semanticEntities = $this->extractSemanticEntities($plainText, $targetKeyword, $secondaryKeywords, $words, $totalWords);

        // Calculate Normalized Aggregate Score (0 - 100)
        $totalEarned = 0;
        $totalPossible = 0;

        $allChecks = array_merge($basicChecks, $additionalChecks, $titleChecks, $contentReadabilityChecks, $eatChecks, $geoChecks, $technicalChecks);

        foreach ($allChecks as $check) {
            $totalPossible += $check['weight'];
            if ($check['pass']) {
                $totalEarned += $check['weight'];
            }
        }

        $rawScore = $totalPossible > 0 ? ($totalEarned / $totalPossible) * 100 : 0;

        // Adjust score if no keyword provided (focus on general quality)
        if (!$kw) {
            $rawScore = max(30, min(85, round($rawScore * 0.85)));
        }

        $finalScore = (int) max(0, min(100, round($rawScore)));

        // Category pass counters
        $countPassed = fn($arr) => count(array_filter($arr, fn($c) => $c['pass']));
        $allRecommendations = $allChecks;

        // Generate offline color-coded SEO markup map with in-canvas recommendations
        $markedHtml = $this->generateMarkedHtml($htmlContent, $targetKeyword, $allChecks, $kwData, [
            'external_links' => $externalLinksCount,
            'internal_links' => $internalLinksCount,
            'h2_count' => count($h2List),
            'total_words' => $totalWords,
            'geo_direct_answer' => $geoMetrics['has_direct_answer'],
            'geo_structured_synthesis' => $geoMetrics['has_structured_table'],
        ]);

        return [
            'marked_html' => $markedHtml,
            'score' => $finalScore,
            'readability_score' => $fleschScore,
            'reading_grade' => $readingGrade,
            'advanced_readability' => [
                'gunning_fog' => round($gunningFog, 1),
                'smog_index' => round($smogIndex, 1),
                'ari_score' => round($ariScore, 1),
                'coleman_liau' => round($colemanLiau, 1),
            ],
            'geo_readiness' => [
                'has_direct_answer' => $geoMetrics['has_direct_answer'],
                'data_points' => $geoMetrics['data_point_count'],
                'has_table' => $geoMetrics['has_structured_table'],
                'paa_count' => $geoMetrics['paa_question_count'],
                'has_quotes' => $geoMetrics['has_expert_quote'],
            ],
            'schema_data' => $schemaData,
            'semantic_entities' => $semanticEntities,
            'recommendations' => $allRecommendations,
            'rank_math' => [
                'basic_seo' => [
                    'title' => 'Basic SEO',
                    'score_label' => $countPassed($basicChecks) . '/' . count($basicChecks) . ' Passed',
                    'checks' => $basicChecks,
                ],
                'additional_seo' => [
                    'title' => 'Additional SEO',
                    'score_label' => $countPassed($additionalChecks) . '/' . count($additionalChecks) . ' Passed',
                    'checks' => $additionalChecks,
                ],
                'title_readability' => [
                    'title' => 'Title Readability & CTR',
                    'score_label' => $countPassed($titleChecks) . '/' . count($titleChecks) . ' Passed',
                    'checks' => $titleChecks,
                ],
                'content_readability' => [
                    'title' => 'Content Readability',
                    'score_label' => $countPassed($contentReadabilityChecks) . '/' . count($contentReadabilityChecks) . ' Passed',
                    'checks' => $contentReadabilityChecks,
                ],
                'eeat_authority' => [
                    'title' => 'E-E-A-T & Authority',
                    'score_label' => $countPassed($eatChecks) . '/' . count($eatChecks) . ' Passed',
                    'checks' => $eatChecks,
                ],
                'geo_ai_search' => [
                    'title' => 'AI Overviews & GEO Readiness',
                    'score_label' => $countPassed($geoChecks) . '/' . count($geoChecks) . ' Passed',
                    'checks' => $geoChecks,
                ],
                'technical_competitive' => [
                    'title' => 'Technical & Competitive Edge',
                    'score_label' => $countPassed($technicalChecks) . '/' . count($technicalChecks) . ' Passed',
                    'checks' => $technicalChecks,
                ],
            ],
            'metrics' => [
                'words' => $totalWords,
                'sentences' => $totalSentences,
                'paragraphs' => $totalParagraphs,
                'reading_time_minutes' => max(1, (int) ceil($totalWords / 200)),
                'headings' => [
                    'h1' => count($h1List),
                    'h2' => count($h2List),
                    'h3' => count($h3List),
                    'h456' => count($h456List),
                ],
                'links' => [
                    'total' => $totalLinks,
                    'internal' => $internalLinksCount,
                    'external' => $externalLinksCount,
                ],
                'images' => $totalImages,
                'long_sentences_pct' => $longSentencesPct,
                'long_paragraphs_pct' => $totalParagraphs > 0 ? round(($longParagraphsCount / $totalParagraphs) * 100) : 0,
                'keyword' => $kwData,
                'eeat_score' => $eEatScore,
                'voice_search_score' => $voiceSearchScore,
                'featured_snippet_score' => $featuredSnippetScore,
                'content_freshness' => $contentFreshness,
                'competitive_gap' => $competitiveGap,
                'title_sentiment' => $titleSentiment,
            ],
        ];
    }

    /**
     * Calculate Gunning Fog Index
     * 
     * @param int $wordCount Total words
     * @param int $sentenceCount Total sentences
     * @param array $paragraphs Paragraph text array
     * @return float Gunning Fog score (grade level)
     */
    protected function calculateGunningFog(int $wordCount, int $sentenceCount, array $paragraphs): float
    {
        if ($sentenceCount === 0 || $wordCount === 0) {
            return 0;
        }

        $wordsPerSentence = $wordCount / $sentenceCount;
        
        // Count complex words (3+ syllables)
        $complexWords = 0;
        $words = preg_split('/\s+/u', implode(' ', $paragraphs), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($words as $word) {
            if ($this->countSyllables($word) >= 3) {
                $complexWords++;
            }
        }
        
        $complexWordRatio = ($complexWords / max(1, $wordCount)) * 100;
        
        return 0.4 * ($wordsPerSentence + $complexWordRatio);
    }

    /**
     * Calculate SMOG Index
     * 
     * @param int $sentenceCount Total sentences
     * @param array $paragraphs Paragraph text array
     * @return float SMOG index (grade level)
     */
    protected function calculateSMOG(int $sentenceCount, array $paragraphs): float
    {
        if ($sentenceCount < 3) {
            return 0;
        }

        // Count polysyllabic words (3+ syllables) in entire text
        $polysyllabicCount = 0;
        $fullText = implode(' ', $paragraphs);
        $words = preg_split('/\s+/u', $fullText, -1, PREG_SPLIT_NO_EMPTY);
        
        foreach ($words as $word) {
            if ($this->countSyllables(preg_replace('/[^a-zA-Z]/', '', $word)) >= 3) {
                $polysyllabicCount++;
            }
        }

        // SMOG formula: 1.0430 * sqrt(polysyllabic_count * (30 / sentence_count)) + 3.1291
        $smog = 1.0430 * sqrt($polysyllabicCount * (30 / max(1, $sentenceCount))) + 3.1291;
        
        return max(0, $smog);
    }

    /**
     * Calculate Automated Readability Index (ARI)
     * 
     * @param int $wordCount Total words
     * @param int $sentenceCount Total sentences
     * @param int $syllableCount Total syllables
     * @return float ARI score (grade level)
     */
    protected function calculateARI(int $wordCount, int $sentenceCount, int $syllableCount): float
    {
        if ($sentenceCount === 0 || $wordCount === 0) {
            return 0;
        }

        $characters = preg_replace('/\s+/u', '', implode(' ', array_slice($this->splitIntoWordsFromHtml('', ''), 0, min(1000, $wordCount))));
        // Simplified: approximate characters from word count
        $avgCharsPerWord = 4.5; // Average English word length
        $totalCharacters = $wordCount * $avgCharsPerWord;
        
        $ari = 4.71 * ($totalCharacters / $wordCount) + 0.5 * ($wordCount / $sentenceCount) - 21.43;
        
        return max(0, $ari);
    }

    /**
     * Calculate Coleman-Liau Index
     * 
     * @param int $wordCount Total words
     * @param int $sentenceCount Total sentences
     * @param string $htmlContent HTML content
     * @return float Coleman-Liau index (grade level)
     */
    protected function calculateColemanLiau(int $wordCount, int $sentenceCount, string $htmlContent): float
    {
        if ($sentenceCount === 0 || $wordCount === 0) {
            return 0;
        }

        // Count letters and sentences
        $letters = preg_match_all('/[a-zA-Z]/', $htmlContent, $matches) ? count($matches[0]) : 0;
        $sentences = preg_match_all('/[.!?]+/', $htmlContent, $matches) ? count($matches[0]) : 0;
        
        $L = ($letters / max(1, $wordCount)) * 100; // Average letters per 100 words
        $S = ($sentences / max(1, $wordCount)) * 100; // Average sentences per 100 words
        
        $cli = 0.0588 * $L - 0.296 * $S - 15.8;
        
        return max(0, $cli);
    }

    /**
     * Count syllables in a word (simplified)
     * 
     * @param string $word Word to analyze
     * @return int Estimated syllable count
     */

    /**
     * Generate color-coded offline SEO annotations and in-canvas recommendation banners directly inside content
     */
    protected function generateMarkedHtml(string $htmlContent, ?string $targetKeyword, array $checks = [], array $kwData = [], array $metrics = []): string
    {
        if (empty(trim($htmlContent))) return $htmlContent;

        $marked = $htmlContent;

        // 0. Clean previous marks and callout banners
        $marked = preg_replace('/<div class="seo-canvas-callout[^>]*>.*?<\/div>/si', '', $marked);
        $marked = preg_replace('/<div class="seo-heatmap-legend-bar[^>]*>.*?<\/div>/si', '', $marked);
        $marked = preg_replace('/<mark[^>]*>/i', '', $marked);
        $marked = preg_replace('/<\/mark>/i', '', $marked);

        $kwClean = $targetKeyword ? htmlspecialchars(trim($targetKeyword), ENT_QUOTES, 'UTF-8') : '';

        // 1. Highlight Focus Keyword Matches (Green)
        if ($targetKeyword) {
            $kw = preg_quote(trim($targetKeyword), '/');
            $marked = preg_replace("/\b({$kw})\b(?![^<]*>)/i", '<mark style="background-color: rgba(16, 185, 129, 0.35); border-bottom: 2px solid #10b981; color: inherit; padding: 1px 5px; border-radius: 4px; font-weight: 600;" title="Focus Keyword: ' . $kwClean . '">$1</mark>', $marked);
        }

        // 2. Highlight E-E-A-T & Authority Signals (Blue)
        $authorityWords = ['expert', 'professional', 'study', 'research', 'certified', 'proven', 'guaranteed', 'according to', 'statistics', 'evidence', 'case study', 'data', 'clinical', 'peer-reviewed'];
        foreach ($authorityWords as $word) {
            $w = preg_quote($word, '/');
            $marked = preg_replace("/\b({$w})\b(?![^<]*>)/i", '<mark style="background-color: rgba(59, 130, 246, 0.3); border-bottom: 2px solid #3b82f6; color: inherit; padding: 1px 5px; border-radius: 4px;" title="Authority / E-E-A-T Signal: $1">$1</mark>', $marked);
        }

        // 3. Highlight Sentence Length check (Red dashed for sentences > 25 words)
        $sentences = preg_split('/(?<=[.?!])\s+/u', strip_tags($htmlContent), -1, PREG_SPLIT_NO_EMPTY);
        foreach ($sentences as $s) {
            $sText = trim($s);
            $wordCount = count(preg_split('/\s+/u', $sText, -1, PREG_SPLIT_NO_EMPTY));
            
            if ($wordCount > 25 && strlen($sText) > 70) {
                $escaped = preg_quote($sText, '/');
                $marked = preg_replace('/' . $escaped . '(?![^<]*>)/i', '<mark style="background-color: rgba(239, 68, 68, 0.22); border-bottom: 2px dashed #ef4444; color: inherit; padding: 1px 4px; border-radius: 4px;" title="Run-on Sentence (' . $wordCount . ' words) — Split into shorter sentences">$0</mark>', $marked, 1);
            }
        }
        
        // 4. Highlight Long Paragraphs (Yellow/Amber left-border for > 100 words)
        if (preg_match_all('/<p[^>]*>(.*?)<\/p>/si', $marked, $pMatches)) {
            foreach ($pMatches[0] as $idx => $fullP) {
                $pText = strip_tags($pMatches[1][$idx]);
                $wordCount = count(preg_split('/\s+/u', trim($pText), -1, PREG_SPLIT_NO_EMPTY));
                if ($wordCount > 100) {
                    $markedP = str_replace('<p', '<p style="background-color: rgba(245, 158, 11, 0.12); border-left: 3px solid #f59e0b; padding-left: 12px; border-radius: 0 8px 8px 0; margin-bottom: 1rem;" title="Bulky Paragraph (' . $wordCount . ' words) — Split for better mobile scannability"', $fullP);
                    $marked = str_replace($fullP, $markedP, $marked);
                }
            }
        }

        // 5. IN-CANVAS RECOMMENDATION BANNERS FOR MISSING ITEMS (Color-Coded Callouts)
        // A. Critical Issue: Focus Keyword Missing in Introduction Hook
        $inIntro = !empty($kwData['in_first_10_pct']);
        if ($targetKeyword && !$inIntro) {
            $introCallout = '<div class="seo-canvas-callout seo-callout-critical" id="seo-loc-kw_in_intro" style="background: rgba(239, 68, 68, 0.12); border: 1px solid rgba(239, 68, 68, 0.35); border-left: 5px solid #ef4444; border-radius: 10px; padding: 10px 14px; margin: 14px 0; font-family: ui-sans-serif, system-ui, sans-serif; font-size: 11.5px; color: #fca5a5; display: flex; align-items: center; justify-content: space-between; gap: 10px; box-shadow: 0 4px 14px rgba(239, 68, 68, 0.12);"><div style="display: flex; align-items: center; gap: 8px;"><span style="background: #ef4444; color: #ffffff; border-radius: 4px; padding: 2px 7px; font-size: 9px; font-weight: 800; letter-spacing: 0.5px; font-family: monospace;">🔴 CRITICAL SEO</span><span><strong>Focus Keyword Missing in Intro:</strong> Primary keyword <em>&ldquo;' . $kwClean . '&rdquo;</em> does not appear in the opening paragraph. Insert it within the first 1-2 sentences.</span></div><span style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); padding: 2px 8px; border-radius: 6px; font-size: 10px; font-family: monospace; white-space: nowrap; color: #fff;">Line 1 Hook</span></div>';

            if (preg_match('/<p[^>]*>/i', $marked)) {
                $marked = preg_replace('/(<p[^>]*>)/i', $introCallout . '$1', $marked, 1);
            } else {
                $marked = $introCallout . $marked;
            }
        }

        // B. Warning Issue: Focus Keyword Missing in Subheadings (H2, H3)
        $inSubheadings = !empty($kwData['in_subheadings']);
        if ($targetKeyword && !$inSubheadings) {
            $headingCallout = '<div class="seo-canvas-callout seo-callout-warning" id="seo-loc-kw_in_subheadings" style="background: rgba(245, 158, 11, 0.12); border: 1px solid rgba(245, 158, 11, 0.35); border-left: 5px solid #f59e0b; border-radius: 10px; padding: 9px 14px; margin: 14px 0; font-family: ui-sans-serif, system-ui, sans-serif; font-size: 11.5px; color: #fcd34d; display: flex; align-items: center; justify-content: space-between; gap: 10px; box-shadow: 0 4px 14px rgba(245, 158, 11, 0.12);"><div style="display: flex; align-items: center; gap: 8px;"><span style="background: #f59e0b; color: #000000; border-radius: 4px; padding: 2px 7px; font-size: 9px; font-weight: 800; letter-spacing: 0.5px; font-family: monospace;">🟡 SUBHEADING TIP</span><span><strong>Missing Keyword in Headings:</strong> Include focus keyword <em>&ldquo;' . $kwClean . '&rdquo;</em> in at least one H2 or H3 heading for topical authority.</span></div><span style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); padding: 2px 8px; border-radius: 6px; font-size: 10px; font-family: monospace; white-space: nowrap; color: #fff;">H2 Structure</span></div>';

            if (preg_match('/<h2[^>]*>/i', $marked)) {
                $marked = preg_replace('/(<h2[^>]*>)/i', $headingCallout . '$1', $marked, 1);
            }
        }

        // C. Warning Issue: Missing External Citations
        $extLinks = $metrics['external_links'] ?? 0;
        if ($extLinks < 2) {
            $citationCallout = '<div class="seo-canvas-callout seo-callout-warning" id="seo-loc-external_links" style="background: rgba(59, 130, 246, 0.12); border: 1px solid rgba(59, 130, 246, 0.35); border-left: 5px solid #3b82f6; border-radius: 10px; padding: 9px 14px; margin: 14px 0; font-family: ui-sans-serif, system-ui, sans-serif; font-size: 11.5px; color: #93c5fd; display: flex; align-items: center; justify-content: space-between; gap: 10px; box-shadow: 0 4px 14px rgba(59, 130, 246, 0.12);"><div style="display: flex; align-items: center; gap: 8px;"><span style="background: #3b82f6; color: #ffffff; border-radius: 4px; padding: 2px 7px; font-size: 9px; font-weight: 800; letter-spacing: 0.5px; font-family: monospace;">🔵 AUTHORITY CITATION</span><span><strong>Outbound Citations Missing (' . $extLinks . '/2):</strong> Add 2+ authoritative external outbound links to reputable research, papers, or industry sources.</span></div><span style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); padding: 2px 8px; border-radius: 6px; font-size: 10px; font-family: monospace; white-space: nowrap; color: #fff;">E-E-A-T Signal</span></div>';

            $lastPPos = strrpos($marked, '<p');
            if ($lastPPos !== false) {
                $marked = substr_replace($marked, $citationCallout . '<p', $lastPPos, 2);
            } else {
                $marked .= $citationCallout;
            }
        }

        // D. Critical Issue: Missing Direct Answer Block for AI Overviews
        if (isset($metrics['geo_direct_answer']) && !$metrics['geo_direct_answer']) {
            $geoDirectCallout = '<div class="seo-canvas-callout seo-callout-critical" id="seo-loc-geo_direct_answer" style="background: rgba(168, 85, 247, 0.12); border: 1px solid rgba(168, 85, 247, 0.35); border-left: 5px solid #a855f7; border-radius: 10px; padding: 10px 14px; margin: 14px 0; font-family: ui-sans-serif, system-ui, sans-serif; font-size: 11.5px; color: #d8b4fe; display: flex; align-items: center; justify-content: space-between; gap: 10px; box-shadow: 0 4px 14px rgba(168, 85, 247, 0.12);"><div style="display: flex; align-items: center; gap: 8px;"><span style="background: #a855f7; color: #ffffff; border-radius: 4px; padding: 2px 7px; font-size: 9px; font-weight: 800; letter-spacing: 0.5px; font-family: monospace;">🟣 AI OVERVIEW</span><span><strong>Direct Answer Snippet Missing:</strong> Insert a 40-60 word definition or answer immediately below this heading for Google AI Overviews & Perplexity citation snippets.</span></div><span style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); padding: 2px 8px; border-radius: 6px; font-size: 10px; font-family: monospace; white-space: nowrap; color: #fff;">Position Zero</span></div>';
            if (preg_match('/<\/h2>/i', $marked)) {
                $marked = preg_replace('/(<\/h2>)/i', '$1' . $geoDirectCallout, $marked, 1);
            }
        }

        // E. Warning Issue: Missing Structured Table / Matrix
        if (isset($metrics['geo_structured_synthesis']) && !$metrics['geo_structured_synthesis']) {
            $geoTableCallout = '<div class="seo-canvas-callout seo-callout-warning" id="seo-loc-geo_structured_synthesis" style="background: rgba(168, 85, 247, 0.12); border: 1px solid rgba(168, 85, 247, 0.35); border-left: 5px solid #a855f7; border-radius: 10px; padding: 9px 14px; margin: 14px 0; font-family: ui-sans-serif, system-ui, sans-serif; font-size: 11.5px; color: #d8b4fe; display: flex; align-items: center; justify-content: space-between; gap: 10px; box-shadow: 0 4px 14px rgba(168, 85, 247, 0.12);"><div style="display: flex; align-items: center; gap: 8px;"><span style="background: #a855f7; color: #ffffff; border-radius: 4px; padding: 2px 7px; font-size: 9px; font-weight: 800; letter-spacing: 0.5px; font-family: monospace;">📊 AI SYNTHESIS</span><span><strong>Comparison Table Gap:</strong> Insert an HTML comparison table or feature matrix. Google Gemini & Perplexity heavily prioritize structured tables for summary boxes.</span></div><span style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15); padding: 2px 8px; border-radius: 6px; font-size: 10px; font-family: monospace; white-space: nowrap; color: #fff;">Comparison Table</span></div>';
            
            // Insert after second h2 or before last paragraph
            if (preg_match_all('/<\/h2>/i', $marked, $h2m, PREG_OFFSET_CAPTURE) && count($h2m[0]) >= 2) {
                $offset = $h2m[0][1][1] + strlen($h2m[0][1][0]);
                $marked = substr_replace($marked, $geoTableCallout, $offset, 0);
            } else {
                $lastPPos = strrpos($marked, '<p');
                if ($lastPPos !== false) {
                    $marked = substr_replace($marked, $geoTableCallout . '<p', $lastPPos, 2);
                } else {
                    $marked .= $geoTableCallout;
                }
            }
        }

        // 6. FLOATING IN-CANVAS COLOR SYSTEM LEGEND BAR (Fixed at the top of inspection mode)
        $legendBar = '<div class="seo-heatmap-legend-bar" style="background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(255, 255, 255, 0.15); border-radius: 14px; padding: 10px 16px; margin-bottom: 20px; font-family: ui-sans-serif, system-ui, sans-serif; font-size: 11px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; box-shadow: 0 12px 30px -5px rgba(0, 0, 0, 0.6); backdrop-filter: blur(16px);">'
            . '<div style="display: flex; align-items: center; gap: 8px; font-weight: 700; color: #ffffff;">'
            . '<span style="font-size: 14px;">🎨</span>'
            . '<span>In-Canvas SEO Inspection & Heatmap:</span>'
            . '</div>'
            . '<div style="display: flex; align-items: center; gap: 14px; flex-wrap: wrap; font-size: 10.5px; font-weight: 600;">'
            . '<span style="display: flex; align-items: center; gap: 5px; color: #fca5a5;"><span style="display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #ef4444; box-shadow: 0 0 6px #ef4444;"></span> 🔴 Critical Issue</span>'
            . '<span style="display: flex; align-items: center; gap: 5px; color: #fcd34d;"><span style="display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #f59e0b; box-shadow: 0 0 6px #f59e0b;"></span> 🟡 Warning / Structure</span>'
            . '<span style="display: flex; align-items: center; gap: 5px; color: #93c5fd;"><span style="display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #3b82f6; box-shadow: 0 0 6px #3b82f6;"></span> 🔵 Authority & E-E-A-T</span>'
            . '<span style="display: flex; align-items: center; gap: 5px; color: #d8b4fe;"><span style="display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #a855f7; box-shadow: 0 0 6px #a855f7;"></span> 🟣 AI Overview / GEO</span>'
            . '<span style="display: flex; align-items: center; gap: 5px; color: #6ee7b7;"><span style="display: inline-block; width: 9px; height: 9px; border-radius: 50%; background: #10b981; box-shadow: 0 0 6px #10b981;"></span> 🟢 Focus Keyword</span>'
            . '</div>'
            . '</div>';

        return $legendBar . $marked;
    }

    /**
     * Calculate Generative Engine Optimization (GEO) & AI Overview readiness metrics
     */
    protected function calculateGeoMetrics(string $htmlContent, array $h2List, string $plainText, array $words): array
    {
        // 1. Direct answer snippet detection (40-60 words directly after H2)
        $hasDirectAnswer = false;
        if (preg_match_all('/<h2[^>]*>.*?<\/h2>\s*<p[^>]*>(.*?)<\/p>/si', $htmlContent, $snippetMatches)) {
            foreach ($snippetMatches[1] as $candidateSnippet) {
                $snippetClean = trim(strip_tags($candidateSnippet));
                $snippetWords = count(preg_split('/\s+/u', $snippetClean, -1, PREG_SPLIT_NO_EMPTY));
                $lowerSnippet = mb_strtolower($snippetClean);
                
                // Disqualify conversational fluff
                $isFluff = preg_match('/^(in this (article|guide|post|section)|welcome to|let\'s (dive|explore|take a look)|as we (know|see)|today we will)/i', $lowerSnippet);
                
                if ($snippetWords >= 28 && $snippetWords <= 75 && !$isFluff) {
                    $hasDirectAnswer = true;
                    break;
                }
            }
        }

        // 2. Data Point & Statistic Density
        // Counts percentages (45%), currency ($500, €100), statistical multipliers (3x, 5 times), dates/years (2025, 2026), or numbers with units
        preg_match_all('/(\b\d+(\.\d+)?%|\$\d+([,\.]\d+)?\b|€\d+([,\.]\d+)?\b|£\d+([,\.]\d+)?\b|\b\d+\s*(percent|million|billion|trillion|x\b|times|users|customers|seconds|ms\b|hours|days|kg|lbs)\b|\b\d{1,3}(,\d{3})+\b|\b202[4-9]\b|\b203[0-9]\b)/i', $plainText, $dataMatches);
        $uniqueDataPoints = array_unique($dataMatches[0] ?? []);
        $dataPointCount = count($uniqueDataPoints);

        // 3. Structured comparison table or matrix
        $hasStructuredTable = (strpos($htmlContent, '<table') !== false) || 
                              (strpos($htmlContent, 'class="hoa-comparison-table"') !== false) ||
                              (preg_match('/\|(.+)\|(.+)\|\n\|[-:\s|]+\|\n\|(.+)\|/m', $htmlContent) === 1);

        // 4. People Also Ask (PAA) Question Headings
        $paaQuestionCount = 0;
        preg_match_all('/<h[2-3][^>]*>(.*?)<\/h[2-3]>/si', $htmlContent, $headings);
        $questionStarters = ['who', 'what', 'when', 'where', 'why', 'how', 'is', 'are', 'can', 'will', 'should', 'which', 'does', 'do', 'vs', 'versus'];
        foreach ($headings[1] ?? [] as $hText) {
            $hClean = trim(strip_tags($hText));
            $hLower = mb_strtolower($hClean);
            if (str_ends_with($hClean, '?')) {
                $paaQuestionCount++;
                continue;
            }
            foreach ($questionStarters as $starter) {
                if (str_starts_with($hLower, $starter . ' ') || str_starts_with($hLower, $starter . '\'')) {
                    $paaQuestionCount++;
                    break;
                }
            }
        }

        // 5. Authoritative Quotes & Source Attribution
        $hasExpertQuote = (strpos($htmlContent, '<blockquote') !== false) ||
                          (preg_match('/(according to|study (by|from)|research (by|from|conducted)|reported by|stated by|as noted by|Dr\.|Prof\.|Ph\.D\.)/i', $plainText) === 1) ||
                          (preg_match('/(&ldquo;|&rdquo;|["“][^"”]{20,}["”])/u', $htmlContent) === 1);

        return [
            'has_direct_answer' => $hasDirectAnswer,
            'data_point_count' => $dataPointCount,
            'has_structured_table' => $hasStructuredTable,
            'paa_question_count' => $paaQuestionCount,
            'has_expert_quote' => $hasExpertQuote,
        ];
    }

    /**
     * Extract Semantic NLP Entities and LSI Keyword Density Matrix (SurferSEO / Clearscope style)
     */
    protected function extractSemanticEntities(string $plainText, ?string $targetKeyword, array $secondaryKeywords, array $words, int $totalWords): array
    {
        $entities = [];
        $lowerText = mb_strtolower($plainText);
        $totalWordsNorm = max(300, $totalWords);

        // 1. Target Primary Keyword
        if (!empty($targetKeyword)) {
            $kwLower = mb_strtolower(trim($targetKeyword));
            $cnt = mb_substr_count($lowerText, $kwLower);
            $targetMin = max(2, (int) round(($totalWordsNorm / 350)));
            $targetMax = max(5, (int) round(($totalWordsNorm / 120)));
            $status = $cnt < $targetMin ? 'underused' : ($cnt > $targetMax ? 'overused' : 'optimal');
            $entities[$kwLower] = [
                'term' => $targetKeyword,
                'type' => 'Primary Focus Keyword',
                'count' => $cnt,
                'min' => $targetMin,
                'max' => $targetMax,
                'status' => $status,
                'importance' => 'high',
            ];
        }

        // 2. User-Specified Secondary Keywords
        foreach ($secondaryKeywords as $secKw) {
            $secLower = mb_strtolower(trim($secKw));
            if (empty($secLower) || isset($entities[$secLower])) continue;
            $cnt = mb_substr_count($lowerText, $secLower);
            $secMin = max(1, (int) round(($totalWordsNorm / 600)));
            $secMax = max(3, (int) round(($totalWordsNorm / 200)));
            $status = $cnt < $secMin ? 'underused' : ($cnt > $secMax ? 'overused' : 'optimal');
            $entities[$secLower] = [
                'term' => $secKw,
                'type' => 'Secondary LSI',
                'count' => $cnt,
                'min' => $secMin,
                'max' => $secMax,
                'status' => $status,
                'importance' => 'high',
            ];
        }

        // 3. Automated NLP 2-Word and 3-Word Semantic Entity Extraction
        $skipList = array_merge($this->stopWords, $this->powerWords, [
            'can', 'will', 'use', 'get', 'also', 'just', 'make', 'like', 'one', 'new', 'well', 'way', 'even', 'want'
        ]);

        $candidatePhrases = [];
        $wordsCount = count($words);

        // Extract 2-word bi-grams
        for ($i = 0; $i < $wordsCount - 1; $i++) {
            $w1 = mb_strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $words[$i]));
            $w2 = mb_strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $words[$i + 1]));
            if (strlen($w1) < 3 || strlen($w2) < 3 || in_array($w1, $skipList) || in_array($w2, $skipList)) {
                continue;
            }
            $phrase = $w1 . ' ' . $w2;
            $candidatePhrases[$phrase] = ($candidatePhrases[$phrase] ?? 0) + 1;
        }

        // Extract 3-word tri-grams
        for ($i = 0; $i < $wordsCount - 2; $i++) {
            $w1 = mb_strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $words[$i]));
            $w2 = mb_strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $words[$i + 1]));
            $w3 = mb_strtolower(preg_replace('/[^a-zA-Z0-9-]/', '', $words[$i + 2]));
            if (strlen($w1) < 3 || strlen($w3) < 3 || in_array($w1, $skipList) || in_array($w3, $skipList)) {
                continue;
            }
            $phrase = $w1 . ' ' . $w2 . ' ' . $w3;
            $candidatePhrases[$phrase] = ($candidatePhrases[$phrase] ?? 0) + 1;
        }

        // Sort candidates by frequency
        arsort($candidatePhrases);

        // Select top 8-12 prominent semantic entities
        $added = 0;
        foreach ($candidatePhrases as $phrase => $freq) {
            if ($added >= 10) break;
            if (isset($entities[$phrase]) || $freq < 1) continue;
            
            $recMin = max(1, (int) round(($totalWordsNorm / 500)));
            $recMax = max(3, (int) round(($totalWordsNorm / 150)));
            $status = $freq < $recMin ? 'underused' : ($freq > $recMax ? 'overused' : 'optimal');

            $entities[$phrase] = [
                'term' => ucwords($phrase),
                'type' => 'Topical Entity',
                'count' => $freq,
                'min' => $recMin,
                'max' => $recMax,
                'status' => $status,
                'importance' => $freq >= 2 ? 'high' : 'medium',
            ];
            $added++;
        }

        return array_values($entities);
    }

    protected function countSyllables(string $word): int
    {
        $word = mb_strtolower(preg_replace('/[^a-zA-Z]/', '', $word));
        if (strlen($word) <= 3) {
            return 1;
        }

        $word = preg_replace('/(?:[^laeiouy]|ed|es|e)$/', '', $word);
        $word = preg_replace('/^y/', '', $word);
        preg_match_all('/[aeiouy]{1,2}/', $word, $matches);

        return max(1, count($matches[0] ?? []));
    }

    /**
     * Count semantic variations of keyword in text (simple stemming)
     * 
     * @param string $text Lowercase text to search
     * @param string $keyword Lowercase keyword
     * @return int Number of semantic variations found
     */
    protected function countSemanticVariations(string $text, string $keyword): int
    {
        if (!$keyword) {
            return 0;
        }

        $count = 0;
        $keywordLen = strlen($keyword);
        
        // Check for exact match
        $count += mb_substr_count($text, $keyword);
        
        // Check for common variations (plurals, verb forms)
        $variations = [
            $keyword . 's',           // plural
            $keyword . 'ing',         // present participle
            $keyword . 'ed',          // past tense
            $keyword . 'er',          // comparative agent
            $keyword . 'est',         // superlative
        ];
        
        // Remove last character and add common endings
        if ($keywordLen > 3) {
            $stem = substr($keyword, 0, -1);
            $variations[] = $stem . 'ing';
            $variations[] = $stem . 'ed';
            $variations[] = $stem . 's';
        }
        
        foreach ($variations as $variation) {
            $count += mb_substr_count($text, $variation);
        }
        
        return $count;
    }

    /**
     * Analyze title sentiment (simplified)
     * 
     * @param string $title Title to analyze
     * @return array Sentiment analysis
     */
    protected function analyzeTitleSentiment(string $title): array
    {
        if (empty($title)) {
            return ['label' => 'neutral', 'score' => 0];
        }

        $positiveWords = [
            'best', 'great', 'excellent', 'amazing', 'fantastic', 'wonderful', 'perfect',
            'ultimate', 'proven', 'essential', 'effective', 'successful', 'beneficial',
            'valuable', 'helpful', 'useful', 'powerful', 'strong', 'smart', 'clever',
            'easy', 'simple', 'fast', 'quick', 'efficient', 'productive', 'profitable'
        ];

        $negativeWords = [
            'worst', 'bad', 'terrible', 'awful', 'horrible', 'disastrous', 'failed',
            'ineffective', 'useless', 'pointless', 'waste', 'difficult', 'hard',
            'complicated', 'complex', 'confusing', 'slow', 'expensive', 'costly'
        ];

        $lowerTitle = mb_strtolower($title);
        $positiveCount = 0;
        $negativeCount = 0;

        foreach ($positiveWords as $word) {
            if (mb_strpos($lowerTitle, $word) !== false) {
                $positiveCount++;
            }
        }

        foreach ($negativeWords as $word) {
            if (mb_strpos($lowerTitle, $word) !== false) {
                $negativeCount++;
            }
        }

        $score = $positiveCount - $negativeCount;
        $label = $score > 0 ? 'positive' : ($score < 0 ? 'negative' : 'neutral');

        return [
            'label' => $label,
            'score' => $score,
            'positive_matches' => $positiveCount,
            'negative_matches' => $negativeCount,
        ];
    }

    /**
     * Calculate E-E-A-T score based on content signals
     * 
     * @param string $htmlContent HTML content
     * @param string $title Title
     * @param ?string $targetKeyword Target keyword
     * @return int E-E-A-T score (0-100)
     */
    protected function calculateEEATScore(string $htmlContent, string $title, ?string $targetKeyword): int
    {
        $score = 0;
        $maxScore = 100;

        // Experience signals (25 points)
        $experienceScore = 0;
        $experienceIndicators = [
            'years of experience', 'decade', 'expert', 'specialist', 'professional',
            'certified', 'licensed', 'accredited', 'qualified', 'skilled', 'veteran',
            'seasoned', 'experienced', 'knowledgeable', 'proven track record'
        ];
        
        foreach ($experienceIndicators as $indicator) {
            if (stripos($htmlContent, $indicator) !== false) {
                $experienceScore += 3;
            }
        }
        $experienceScore = min(25, $experienceScore);

        // Expertise signals (25 points)
        $expertiseScore = 0;
        $expertiseIndicators = [
            'study', 'research', 'data shows', 'according to', 'statistics', 'report',
            'analysis', 'findings', 'conclusion', 'evidence', 'proof', 'demonstrates',
            'shows', 'indicates', 'suggests', 'reveals', 'indicates', 'indicates'
        ];
        
        foreach ($expertiseIndicators as $indicator) {
            if (stripos($htmlContent, $indicator) !== false) {
                $expertiseScore += 3;
            }
        }
        $expertiseScore = min(25, $expertiseScore);

        // Authoritativeness signals (25 points)
        $authorityScore = 0;
        $authorityIndicators = [
            'source:', 'reference:', 'cite:', 'references:', 'bibliography',
            'according to', 'as stated by', 'reported by', 'published in',
            'journal', 'study', 'research paper', 'official', 'government',
            'university', 'institution', 'organization', 'association'
        ];
        
        foreach ($authorityIndicators as $indicator) {
            if (stripos($htmlContent, $indicator) !== false) {
                $authorityScore += 3;
            }
        }
        $authorityScore = min(25, $authorityScore);

        // Trustworthiness signals (25 points)
        $trustScore = 0;
        $trustIndicators = [
            'disclaimer', 'privacy policy', 'terms of service', 'contact',
            'about us', 'our team', 'meet the team', 'biography', 'bio',
            'credentials', 'qualifications', 'certifications', 'awards',
            'recognition', 'testimonial', 'review', 'feedback', 'guarantee',
            'warranty', 'money back', 'secure', 'encrypted', 'https'
        ];
        
        foreach ($trustIndicators as $indicator) {
            if (stripos($htmlContent, $indicator) !== false) {
                $trustScore += 3;
            }
        }
        $trustScore = min(25, $trustScore);

        return min($maxScore, $experienceScore + $expertiseScore + $authorityScore + $trustScore);
    }

    /**
     * Calculate voice search optimization score
     * 
     * @param string $htmlContent HTML content
     * @param string $title Title
     * @param ?string $targetKeyword Target keyword
     * @return int Voice search score (0-100)
     */
    protected function calculateVoiceSearchScore(string $htmlContent, string $title, ?string $targetKeyword): int
    {
        $score = 0;
        $maxScore = 100;

        // Check for question-based content (30 points)
        $questionScore = 0;
        preg_match_all('/[.!?]\s*/', $htmlContent, $sentenceMatches);
        $sentences = $sentenceMatches[0] ?? [];
        
        $questionCount = 0;
        foreach ($sentences as $sentence) {
            $trimmed = trim($sentence);
            $firstWord = strtolower(strtok($trimmed, ' '));
            if (in_array($firstWord, $this->questionWords)) {
                $questionCount++;
            }
        }
        
        if (!empty($sentences)) {
            $questionScore = min(30, ($questionCount / count($sentences)) * 100);
        }

        // Check for FAQ-like structure (25 points)
        $faqScore = 0;
        $faqIndicators = ['faq', 'frequently asked', 'question and answer', 'q&a', 'what is', 'how to'];
        foreach ($faqIndicators as $indicator) {
            if (stripos($htmlContent, $indicator) !== false) {
                $faqScore += 5;
            }
        }
        $faqScore = min(25, $faqScore);

        // Check for concise answers (25 points)
        $conciseScore = 0;
        $shortSentences = 0;
        $totalSentences = count($sentences);
        
        foreach ($sentences as $sentence) {
            $wordCount = str_word_count(trim($sentence));
            if ($wordCount > 0 && $wordCount <= 20) {
                $shortSentences++;
            }
        }
        
        if ($totalSentences > 0) {
            $conciseScore = min(25, ($shortSentences / $totalSentences) * 100);
        }

        // Check for local/search intent indicators (20 points)
        $localScore = 0;
        $localIndicators = [
            'near me', 'nearby', 'local', 'in my area', 'close to', 'around',
            'today', 'tonight', 'this week', 'this month', 'hours', 'price',
            'cost', 'free', 'cheap', 'affordable', 'best', 'top', 'review'
        ];
        
        foreach ($localIndicators as $indicator) {
            if (stripos($htmlContent, $indicator) !== false) {
                $localScore += 2;
            }
        }
        $localScore = min(20, $localScore);

        return min($maxScore, $questionScore + $faqScore + $conciseScore + $localScore);
    }

    /**
     * Calculate featured snippet potential
     * 
     * @param string $htmlContent HTML content
     * @param ?string $targetKeyword Target keyword
     * @return int Featured snippet score (0-100)
     */
    protected function calculateFeaturedSnippetPotential(string $htmlContent, ?string $targetKeyword): int
    {
        $score = 0;
        $maxScore = 100;

        // Check for list structures (30 points)
        $listScore = 0;
        $listPatterns = [
            '/<ol[^>]*>.*?<\/ol>/i',
            '/<ul[^>]*>.*?<\/ul>/i',
            '/<li[^>]*>.*?<\/li>/i',
            '/^\s*[\d]+\.\s+/m',      // Numbered lists
            '/^\s*[-*•]\s+/m',        // Bullet points
        ];
        
        foreach ($listPatterns as $pattern) {
            if (preg_match($pattern, $htmlContent)) {
                $listScore += 10;
            }
        }
        $listScore = min(30, $listScore);

        // Check for definition patterns (25 points)
        $defScore = 0;
        $defPatterns = [
            '/is\s+(?:a|an)\s+/i',
            '/are\s+(?:a|an)\s+/i',
            '/refers\s+to\s+/i',
            '/defined\s+as\s+/i',
            '/means\s+/i',
            '/:\s*/i'  // Colon definitions
        ];
        
        foreach ($defPatterns as $pattern) {
            if (preg_match($pattern, $htmlContent)) {
                $defScore += 5;
            }
        }
        $defScore = min(25, $defScore);

        // Check for table structures (20 points)
        $tableScore = 0;
        if (stripos($htmlContent, '<table') !== false) {
            $tableScore = 20;
        }

        // Check for step-by-step/how-to patterns (15 points)
        $stepsScore = 0;
        $stepsPatterns = [
            '/step\s+\d+/i',
            '/first[,:]|second[,:]|third[,:]|finally[,:]/i',
            '/how\s+to\s+/i',
            '/tutorial\s+/i',
            '/guide\s+/i'
        ];
        
        foreach ($stepsPatterns as $pattern) {
            if (preg_match($pattern, $htmlContent)) {
                $stepsScore += 5;
            }
        }
        $stepsScore = min(15, $stepsScore);

        // Check for question-answer format (10 points)
        $qaScore = 0;
        if (preg_match('/[?]\s*[A-Z]/', $htmlContent) || stripos($htmlContent, 'answer:') !== false) {
            $qaScore = 10;
        }

        return min($maxScore, $listScore + $defScore + $tableScore + $stepsScore + $qaScore);
    }

    /**
     * Calculate content freshness indicators
     * 
     * @param string $htmlContent HTML content
     * @return int Freshness score (0-100)
     */
    protected function calculateContentFreshness(string $htmlContent): int
    {
        $score = 0;
        $maxScore = 100;

        // Check for recent dates/years (40 points)
        $dateScore = 0;
        $currentYear = (int) date('Y');
        $recentYears = range($currentYear - 2, $currentYear);
        
        foreach ($recentYears as $year) {
            if (strpos($htmlContent, (string) $year) !== false) {
                $dateScore += 10;
            }
        }
        
        // Check for month names
        $months = ['January', 'February', 'March', 'April', 'May', 'June',
                  'July', 'August', 'September', 'October', 'November', 'December'];
        foreach ($months as $month) {
            if (stripos($htmlContent, $month) !== false) {
                $dateScore += 3;
            }
        }
        $dateScore = min(40, $dateScore);

        // Check for time-sensitive indicators (30 points)
        $timeScore = 0;
        $timeIndicators = [
            'latest', 'newest', 'recent', 'current', 'today', 'now',
            'this year', 'this month', 'this week', 'updated', 'revised',
            'modified', 'changed', 'improved', 'enhanced'
        ];
        
        foreach ($timeIndicators as $indicator) {
            if (stripos($htmlContent, $indicator) !== false) {
                $timeScore += 3;
            }
        }
        $timeScore = min(30, $timeScore);

        // Check for technology/current trends (20 points)
        $techScore = 0;
        $techIndicators = [
            'ai', 'artificial intelligence', 'machine learning', 'blockchain',
            'cryptocurrency', 'vr', 'virtual reality', 'ar', 'augmented reality',
            'iot', 'internet of things', '5g', 'cloud computing', 'saas',
            'api', 'framework', 'library', 'update', 'version'
        ];
        
        foreach ($techIndicators as $indicator) {
            if (stripos($htmlContent, $indicator) !== false) {
                $techScore += 2;
            }
        }
        $techScore = min(20, $techScore);

        // Check for absence of outdated indicators (10 points)
        $outdatedPenalty = 0;
        $outdatedIndicators = [
            'floppy disk', 'dial-up', 'pager', 'fax machine', 'vcr',
            'cassette tape', 'vinyl record', 'film camera', 'blackberry',
            'myspace', 'friendster', 'yelp (early)', 'ask jeeves'
        ];
        
        foreach ($outdatedIndicators as $indicator) {
            if (stripos($htmlContent, $indicator) !== false) {
                $outdatedPenalty += 5;
            }
        }
        $outdatedPenalty = min(10, $outdatedPenalty);

        return max(0, min($maxScore, $dateScore + $timeScore + $techScore - $outdatedPenalty));
    }

    /**
     * Simulate competitive gap analysis
     * 
     * @param ?string $targetKeyword Target keyword
     * @param string $htmlContent HTML content
     * @return array Competitive gap analysis
     */
    protected function simulateCompetitiveGapAnalysis(?string $targetKeyword, string $htmlContent): array
    {
        // Common subtopics that competitors might cover for various niches
        $commonSubtopicsByNiche = [
            'technology' => ['history', 'current trends', 'future predictions', 'use cases', 'benefits', 'drawbacks', 'implementation', 'cost', 'security'],
            'health' => ['symptoms', 'causes', 'treatment', 'prevention', 'diagnosis', 'risk factors', 'statistics', 'when to see doctor'],
            'business' => ['strategy', 'marketing', 'finance', 'operations', 'leadership', 'innovation', 'case studies', 'best practices'],
            'education' => ['curriculum', 'teaching methods', 'assessment', 'technology', 'funding', 'policy', 'outcomes', 'resources'],
            'default' => ['introduction', 'overview', 'definition', 'history', 'benefits', 'drawbacks', 'how it works', 'examples', 'case studies', 'best practices', 'tips', 'common mistakes', 'future outlook']
        ];

        // Determine likely niche from keyword or content
        $niche = 'default';
        $lowerContent = mb_strtolower($htmlContent);
        $lowerKeyword = $targetKeyword ? mb_strtolower($targetKeyword) : '';
        
        $techKeywords = ['ai', 'artificial intelligence', 'machine learning', 'software', 'programming', 'code', 'algorithm', 'data', 'cloud', 'vr', 'ar', 'iot', 'blockchain'];
        $healthKeywords = ['health', 'medical', 'medicine', 'disease', 'symptom', 'treatment', 'healthcare', 'wellness', 'fitness', 'nutrition'];
        $businessKeywords = ['business', 'marketing', 'sales', 'finance', 'management', 'entrepreneur', 'startup', 'company', 'industry', 'market'];
        $educationKeywords = ['education', 'school', 'university', 'college', 'learning', 'teaching', 'student', 'curriculum', 'academic'];
        
        $techScore = 0;
        $healthScore = 0;
        $businessScore = 0;
        $educationScore = 0;
        
        foreach ($techKeywords as $kw) {
            if (strpos($lowerContent, $kw) !== false || ($lowerKeyword && strpos($lowerKeyword, $kw) !== false)) {
                $techScore++;
            }
        }
        
        foreach ($healthKeywords as $kw) {
            if (strpos($lowerContent, $kw) !== false || ($lowerKeyword && strpos($lowerKeyword, $kw) !== false)) {
                $healthScore++;
            }
        }
        
        foreach ($businessKeywords as $kw) {
            if (strpos($lowerContent, $kw) !== false || ($lowerKeyword && strpos($lowerKeyword, $kw) !== false)) {
                $businessScore++;
            }
        }
        
        foreach ($educationKeywords as $kw) {
            if (strpos($lowerContent, $kw) !== false || ($lowerKeyword && strpos($lowerKeyword, $kw) !== false)) {
                $educationScore++;
            }
        }
        
        $scores = [
            'technology' => $techScore,
            'health' => $healthScore,
            'business' => $businessScore,
            'education' => $educationScore,
        ];
        
        arsort($scores);
        $topNiche = key($scores);
        $niche = $topNiche && $scores[$topNiche] > 0 ? $topNiche : 'default';
        
        $expectedSubtopics = $commonSubtopicsByNiche[$niche] ?? $commonSubtopicsByNiche['default'];
        
        // Check which subtopics are covered in content
        $coveredSubtopics = [];
        $missingSubtopics = [];
        
        foreach ($expectedSubtopics as $subtopic) {
            if (stripos($htmlContent, $subtopic) !== false) {
                $coveredSubtopics[] = $subtopic;
            } else {
                $missingSubtopics[] = $subtopic;
            }
        }
        
        $coveragePercentage = count($expectedSubtopics) > 0 
            ? round((count($coveredSubtopics) / count($expectedSubtopics)) * 100) 
            : 100;
        
        return [
            'score' => $coveragePercentage,
            'covered_topics' => $coveredSubtopics,
            'missing_topics' => $missingSubtopics,
            'total_expected' => count($expectedSubtopics),
            'detected_niche' => $niche,
        ];
    }

    /**
     * Split HTML content into words (simplified)
     * 
     * @param string $htmlContent HTML content
     * @param string $dummy Unused parameter for compatibility
     * @return array Words array
     */
    protected function splitIntoWordsFromHtml(string $htmlContent, string $dummy): array
    {
        $plainText = trim(preg_replace('/\s+/u', ' ', strip_tags($htmlContent)));
        return !empty($plainText) ? preg_split('/\s+/u', $plainText, -1, PREG_SPLIT_NO_EMPTY) : [];
    }
}