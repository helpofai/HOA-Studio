<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Livewire Concern: SEO Auditing & Metadata
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

namespace App\Features\Documents\Livewire\Concerns;

use App\Features\Documents\Models\Document;
use App\Features\SEO\Actions\GenerateSeoMetadata;
use App\Features\SEO\Models\SeoAnalysis;
use App\Features\SEO\Services\SeoAnalyzer;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\On;

trait HasSeoAuditing
{
    public function runSeoAudit(?string $liveHtml = null, ?SeoAnalyzer $analyzer = null): string
    {
        $analyzer = $analyzer ?? app(SeoAnalyzer::class);
        $this->isAnalyzingSeo = true;

        try {
            if ($liveHtml !== null && trim($liveHtml) !== '') {
                $this->contentHtml = $liveHtml;
            }

            $rawSeo = $analyzer->analyze(
                $this->contentHtml,
                $this->title,
                $this->targetKeyword ?: null,
                $this->secondaryKeywords,
                $this->metaDescription ?? ''
            );

            $markedHtml = $rawSeo['marked_html'] ?? '';
            unset($rawSeo['marked_html']);
            $this->seoData = $rawSeo;

            $savedMetrics = $this->seoData['metrics'] ?? [];
            if (! empty($this->metaDescription)) {
                $savedMetrics['meta_description'] = $this->metaDescription;
            }

            SeoAnalysis::updateOrCreate(
                ['document_id' => $this->documentId],
                [
                    'target_keyword' => $this->targetKeyword,
                    'secondary_keywords' => $this->secondaryKeywords,
                    'score' => $this->seoData['score'] ?? 0,
                    'readability_score' => $this->seoData['readability_score'] ?? 0,
                    'metrics' => $savedMetrics,
                    'recommendations' => $this->seoData['recommendations'] ?? [],
                ]
            );

            $this->generateQualityAudit();

            return $markedHtml;
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();

            return '';
        } finally {
            $this->isAnalyzingSeo = false;
        }
    }

    public function queueSeoAudit()
    {
        return $this->runSeoAudit();
    }

    public function toggleSeoDrawer(): void
    {
        $this->showSeoDrawer = ! $this->showSeoDrawer;
    }

    public function addSecondaryKeyword()
    {
        $newKw = trim($this->newSecondaryKeyword);
        if ($newKw && ! in_array($newKw, $this->secondaryKeywords)) {
            $this->secondaryKeywords[] = $newKw;
            $this->newSecondaryKeyword = '';
            $this->runSeoAudit();
        }
    }

    public function removeSecondaryKeyword(int $index)
    {
        if (isset($this->secondaryKeywords[$index])) {
            array_splice($this->secondaryKeywords, $index, 1);
            $this->runSeoAudit();
        }
    }

    public function generateSeoTitles(?GenerateSeoMetadata $generator = null)
    {
        $generator = $generator ?? app(GenerateSeoMetadata::class);
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'titles';
        $this->seoErrorMessage = '';

        try {
            $this->aiTitles = $generator->generateTitles(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
            $this->aiSeoResults = $this->aiTitles;
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateMetaDescriptions(?GenerateSeoMetadata $generator = null)
    {
        $generator = $generator ?? app(GenerateSeoMetadata::class);
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'descriptions';
        $this->seoErrorMessage = '';

        try {
            $this->aiMetaDescriptions = $generator->generateMetaDescriptions(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
            $this->aiSeoResults = $this->aiMetaDescriptions;
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function suggestLsiKeywords(?GenerateSeoMetadata $generator = null)
    {
        $generator = $generator ?? app(GenerateSeoMetadata::class);
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'lsi';
        $this->seoErrorMessage = '';

        try {
            $this->aiLsiKeywords = $generator->suggestLsiKeywords(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
            $this->aiSeoResults = $this->aiLsiKeywords;
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateFaqSuggestions(?GenerateSeoMetadata $generator = null)
    {
        $generator = $generator ?? app(GenerateSeoMetadata::class);
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'faq';
        $this->seoErrorMessage = '';

        try {
            $this->aiFaqs = $generator->generateFaqs(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
            $this->aiSeoResults = $this->aiFaqs;
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateQuickAnswer(?GenerateSeoMetadata $generator = null)
    {
        $generator = $generator ?? app(GenerateSeoMetadata::class);
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'quick_answer';
        $this->seoErrorMessage = '';

        try {
            $this->aiQuickAnswer = $generator->generateQuickAnswer(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
            $this->aiSeoResults = [$this->aiQuickAnswer];
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateContentGaps(?GenerateSeoMetadata $generator = null)
    {
        $generator = $generator ?? app(GenerateSeoMetadata::class);
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'content_gaps';
        $this->seoErrorMessage = '';

        try {
            $this->aiContentGaps = $generator->identifyContentGaps(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
            $this->aiSeoResults = $this->aiContentGaps;
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateQualityAudit()
    {
        $plain = strip_tags($this->contentHtml ?: '');
        $words = $this->wordCount ?: str_word_count($plain);
        $score = $this->seoData['score'] ?? 75;
        $read = $this->seoData['readability_score'] ?? 70;
        $metrics = $this->seoData['metrics'] ?? [];
        $geo = $this->seoData['geo_readiness'] ?? [];
        $schema = $this->seoData['schema_data'] ?? [];
        $kw = trim(mb_strtolower($this->targetKeyword ?? ''));
        $lowerPlain = mb_strtolower($plain);
        $lowerTitle = mb_strtolower($this->title ?? '');

        // 1. Search Intent Satisfaction (0-100)
        $searchIntentScore = 50;
        $searchIntentStatus = 'General search intent';
        if (! empty($kw)) {
            $inTitle = mb_strpos($lowerTitle, $kw) !== false;
            $tenPctWords = mb_strtolower(implode(' ', array_slice(preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY), 0, max(15, (int) round($words * 0.1)))));
            $inIntro = mb_strpos($tenPctWords, $kw) !== false;
            $inH2 = false;
            if (! empty($metrics['headings']['h2'])) {
                preg_match_all('/<h2[^>]*>(.*?)<\/h2>/si', $this->contentHtml, $h2m);
                $inH2 = mb_strpos(mb_strtolower(implode(' ', $h2m[1] ?? [])), $kw) !== false;
            }
            $inMeta = ! empty($this->metaDescription) && mb_strpos(mb_strtolower($this->metaDescription), $kw) !== false;

            $points = 20;
            if ($inTitle) {
                $points += 30;
            }
            if ($inIntro) {
                $points += 25;
            }
            if ($inH2) {
                $points += 15;
            }
            if ($inMeta) {
                $points += 10;
            }
            $searchIntentScore = min(100, $points);

            if ($inTitle && $inIntro && ($inH2 || $inMeta)) {
                $searchIntentStatus = 'Optimal query intent alignment';
            } elseif ($inTitle || $inIntro) {
                $searchIntentStatus = 'Partial query intent alignment';
            } else {
                $searchIntentStatus = 'Keyword missing from intro & headings';
            }
        } elseif ($words > 300 && ! empty($this->title)) {
            $searchIntentScore = 78;
            $searchIntentStatus = 'Informational guide structure';
        }

        // 2. Topical Depth & Comprehensiveness (0-100)
        if ($words >= 1500) {
            $topicCoverageScore = 98;
            $topicCoverageStatus = "{$words} words (Definitive authority)";
        } elseif ($words >= 1000) {
            $topicCoverageScore = 88;
            $topicCoverageStatus = "{$words} words (In-depth coverage)";
        } elseif ($words >= 600) {
            $topicCoverageScore = 74;
            $topicCoverageStatus = "{$words} words (Standard length)";
        } elseif ($words >= 300) {
            $topicCoverageScore = 55;
            $topicCoverageStatus = "{$words} words (Moderate length)";
        } else {
            $topicCoverageScore = 35;
            $topicCoverageStatus = "{$words} words (Thin content)";
        }

        // 3. Information Gain & Empirical Data Points (0-100)
        $dataPoints = $geo['data_points'] ?? 0;
        if ($dataPoints === 0) {
            preg_match_all('/\b\d+(\.\d+)?%|\$\d+(\.\d+)?|\b(19|20)\d{2}\b/i', $plain, $dpMatches);
            $dataPoints = count($dpMatches[0] ?? []);
        }
        if ($dataPoints >= 4) {
            $originalValueScore = 96;
            $originalValueStatus = "{$dataPoints} empirical data points detected";
        } elseif ($dataPoints >= 2) {
            $originalValueScore = 82;
            $originalValueStatus = "{$dataPoints} statistics & metrics found";
        } elseif ($dataPoints === 1) {
            $originalValueScore = 65;
            $originalValueStatus = '1 single data point found';
        } else {
            $originalValueScore = 38;
            $originalValueStatus = 'No verifiable data points detected';
        }

        // 4. Readability & Sentence Cadence (0-100)
        $flesch = (int) $read;
        $longPct = $metrics['long_sentences_pct'] ?? 0;
        $readingGrade = $this->seoData['reading_grade'] ?? 'Standard';
        $readabilityScore = $flesch;
        if ($flesch >= 60 && $flesch <= 80 && $longPct <= 20) {
            $readabilityScore = 94;
        } elseif ($flesch >= 50 && $longPct <= 25) {
            $readabilityScore = 82;
        } elseif ($longPct > 35) {
            $readabilityScore = max(40, $flesch - 15);
        }
        $readabilityStatus = "Flesch {$flesch} ({$readingGrade})";

        // 5. Heading Architecture & Scannability (0-100)
        $h1Count = $metrics['headings']['h1'] ?? 0;
        $h2Count = $metrics['headings']['h2'] ?? 0;
        $h3Count = $metrics['headings']['h3'] ?? 0;
        if ($h1Count === 1 && $h2Count >= 2 && $h3Count >= 1) {
            $seoStructureScore = 98;
            $seoStructureStatus = "1 H1, {$h2Count} H2s, {$h3Count} H3s (Ideal hierarchy)";
        } elseif ($h2Count >= 2) {
            $seoStructureScore = 85;
            $seoStructureStatus = "{$h2Count} H2 sections present";
        } elseif ($h2Count === 1) {
            $seoStructureScore = 68;
            $seoStructureStatus = 'Only 1 H2 subheading found';
        } else {
            $seoStructureScore = 38;
            $seoStructureStatus = 'No H2 subheadings detected';
        }

        // 6. Internal Topic Cluster Links (0-100)
        $internalLinks = $metrics['links']['internal'] ?? 0;
        if ($internalLinks >= 3) {
            $internalLinkingScore = 96;
            $internalLinkingStatus = "{$internalLinks} internal cluster links";
        } elseif ($internalLinks >= 1) {
            $internalLinkingScore = 78;
            $internalLinkingStatus = "{$internalLinks} internal link(s) found";
        } else {
            $internalLinkingScore = 35;
            $internalLinkingStatus = '0 internal cluster links';
        }

        // 7. Authoritative Outbound Citations (0-100)
        $externalLinks = $metrics['links']['external'] ?? 0;
        $hasQuotes = $geo['has_quotes'] ?? false;
        if ($externalLinks >= 2) {
            $outboundCitationsScore = $hasQuotes ? 98 : 92;
            $outboundCitationsStatus = "{$externalLinks} external citations".($hasQuotes ? ' + quotes' : '');
        } elseif ($externalLinks === 1) {
            $outboundCitationsScore = $hasQuotes ? 86 : 76;
            $outboundCitationsStatus = '1 citation'.($hasQuotes ? ' + expert quote' : '');
        } else {
            $outboundCitationsScore = $hasQuotes ? 62 : 36;
            $outboundCitationsStatus = $hasQuotes ? 'Quote found, no outbound links' : '0 external citations';
        }

        // 8. E-E-A-T First-Hand Experience & Trust (0-100)
        $hasExpPhrases = preg_match('/\b(in our (tests|testing|evaluation|lab|study|experience)|we tested|we evaluated|our findings|in my experience|case study|hands-on|benchmarks show|our team verified|in our trial|we observed)\b/i', $plain) === 1;
        $hasTable = $geo['has_table'] ?? false;
        if ($hasExpPhrases) {
            $eeatScore = 95;
            $eeatStatus = 'First-hand testing & empirical signals present';
        } elseif ($hasTable || $hasQuotes) {
            $eeatScore = 78;
            $eeatStatus = 'Structured data/quotes present';
        } else {
            $eeatScore = 46;
            $eeatStatus = 'Missing first-hand experience markers';
        }

        // 9. Google AI Overviews & GEO Readiness (0-100)
        $hasDirectAnswer = $geo['has_direct_answer'] ?? false;
        $hasStructuredTable = $geo['has_table'] ?? false;
        $paaCount = $geo['paa_count'] ?? 0;
        $geoPoints = 20;
        if ($hasDirectAnswer) {
            $geoPoints += 40;
        }
        if ($hasStructuredTable) {
            $geoPoints += 25;
        }
        if ($paaCount >= 2) {
            $geoPoints += 15;
        }
        $geoScore = min(100, $geoPoints);
        $geoStatus = ($hasDirectAnswer ? '✓ Direct snippet' : '✕ No snippet').' • '.($hasStructuredTable ? '✓ Table' : '✕ No table').' • '."{$paaCount} PAA";

        // 10. Technical Schema & Semantic Markup (0-100)
        $isValidSchema = $schema['validation']['is_valid'] ?? false;
        $schemaType = $schema['recommended_type'] ?? 'Article';
        $titleLen = mb_strlen($this->title ?? '');
        $metaLen = mb_strlen($this->metaDescription ?? '');
        $techPoints = 30;
        if ($isValidSchema) {
            $techPoints += 40;
        }
        if ($titleLen >= 40 && $titleLen <= 65) {
            $techPoints += 15;
        }
        if ($metaLen >= 120 && $metaLen <= 160) {
            $techPoints += 15;
        }
        $technicalScore = min(100, $techPoints);
        $technicalStatus = $isValidSchema ? "Valid {$schemaType} Schema.org JSON-LD" : 'Schema not validated';

        // Assemble 10 Factors
        $factors = [
            'search_intent' => [
                'number' => 1,
                'id' => 'search_intent',
                'title' => 'Search Intent Satisfaction',
                'category' => 'Relevance',
                'score' => $searchIntentScore,
                'status' => $searchIntentStatus,
                'desc' => 'Primary search intent alignment in title, opening hook, and subheadings.',
                'action_type' => 'search_intent',
                'button_label' => '⚡ Align Intent',
                'button_class' => 'bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white',
            ],
            'topic_coverage' => [
                'number' => 2,
                'id' => 'topic_coverage',
                'title' => 'Topical Depth & Comprehensiveness',
                'category' => 'Content Depth',
                'score' => $topicCoverageScore,
                'status' => $topicCoverageStatus,
                'desc' => 'Depth, word count volume, and exhaustive subtopic coverage.',
                'action_type' => 'expand',
                'button_label' => '⚡ AI Expand',
                'button_class' => 'bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white',
            ],
            'original_value' => [
                'number' => 3,
                'id' => 'original_value',
                'title' => 'Information Gain & Data Points',
                'category' => 'Research',
                'score' => $originalValueScore,
                'status' => $originalValueStatus,
                'desc' => 'Verifiable benchmarks, empirical metrics, and research figures.',
                'action_type' => 'geo_data_points',
                'button_label' => '⚡ Add Data',
                'button_class' => 'bg-amber-600/30 hover:bg-amber-600 text-amber-300 hover:text-white',
            ],
            'readability' => [
                'number' => 4,
                'id' => 'readability',
                'title' => 'Readability & Scannability',
                'category' => 'User Experience',
                'score' => $readabilityScore,
                'status' => $readabilityStatus,
                'desc' => 'Flesch ease, sentence cadence, and absence of wall-of-text paragraphs.',
                'action_type' => 'polish',
                'button_label' => '⚡ Simplify',
                'button_class' => 'bg-cyan-600/30 hover:bg-cyan-600 text-cyan-300 hover:text-white',
            ],
            'seo_structure' => [
                'number' => 5,
                'id' => 'seo_structure',
                'title' => 'Heading Hierarchy & Structure',
                'category' => 'Architecture',
                'score' => $seoStructureScore,
                'status' => $seoStructureStatus,
                'desc' => 'H1-H2-H3 logical hierarchy, bulleted lists, and scannable visual anchors.',
                'action_type' => 'generate_outline',
                'button_label' => '⚡ AI Structure',
                'button_class' => 'bg-indigo-600/30 hover:bg-indigo-600 text-indigo-300 hover:text-white',
            ],
            'internal_linking' => [
                'number' => 6,
                'id' => 'internal_linking',
                'title' => 'Internal Topic Cluster Links',
                'category' => 'Site Silo',
                'score' => $internalLinkingScore,
                'status' => $internalLinkingStatus,
                'desc' => 'Topical cluster connections passing crawl equity to parent/child pages.',
                'action_type' => 'custom',
                'custom_prompt' => 'Analyze this document and suggest 3 contextual internal topic cluster links with descriptive anchor text to build a topical silo.',
                'button_label' => '⚡ Auto-Cluster',
                'button_class' => 'bg-violet-600/30 hover:bg-violet-600 text-violet-300 hover:text-white',
            ],
            'outbound_citations' => [
                'number' => 7,
                'id' => 'outbound_citations',
                'title' => 'Authoritative Outbound Citations',
                'category' => 'Authority',
                'score' => $outboundCitationsScore,
                'status' => $outboundCitationsStatus,
                'desc' => 'External references and study attributions supporting key claims.',
                'action_type' => 'seo_fix_citations',
                'button_label' => '⚡ Add Citations',
                'button_class' => 'bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white',
            ],
            'eeat_signals' => [
                'number' => 8,
                'id' => 'eeat_signals',
                'title' => 'First-Hand Experience & Trust (E-E-A-T)',
                'category' => 'Trust & Experience',
                'score' => $eeatScore,
                'status' => $eeatStatus,
                'desc' => 'Tangible proof of personal testing, laboratory benchmarks, and author expertise.',
                'action_type' => 'eeat_trust',
                'button_label' => '⚡ Inject Trust',
                'button_class' => 'bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white',
            ],
            'geo_readiness' => [
                'number' => 9,
                'id' => 'geo_readiness',
                'title' => 'Google AI Overviews & GEO Readiness',
                'category' => 'AI Search',
                'score' => $geoScore,
                'status' => $geoStatus,
                'desc' => 'Direct 40-60w answer definitions, comparison matrices, and PAA queries.',
                'action_type' => 'geo_direct_answer',
                'button_label' => '⚡ AI Overview',
                'button_class' => 'bg-purple-600/30 hover:bg-purple-600 text-purple-300 hover:text-white',
            ],
            'technical_seo' => [
                'number' => 10,
                'id' => 'technical_seo',
                'title' => 'Technical Schema.org & Meta Markup',
                'category' => 'Technical',
                'score' => $technicalScore,
                'status' => $technicalStatus,
                'desc' => 'Validated JSON-LD schema (Article, FAQPage) and optimized metadata.',
                'action_type' => 'generate_faq',
                'button_label' => '⚡ Add Schema',
                'button_class' => 'bg-emerald-600/30 hover:bg-emerald-600 text-emerald-300 hover:text-white',
            ],
        ];

        $scores = array_column($factors, 'score');
        $overall = (int) round(array_sum($scores) / count($scores));
        $passedCount = count(array_filter($scores, fn ($s) => $s >= 75));

        $gradeLabel = match (true) {
            $overall >= 90 => '🏆 Enterprise Grade (A+)',
            $overall >= 80 => '✨ High Quality (A)',
            $overall >= 70 => '⚡ Publication Ready (B)',
            $overall >= 60 => '⚠️ Needs Polish (C)',
            default => '✕ High Risk / Low E-E-A-T (D)',
        };

        $this->aiQualityAudit = [
            'overall' => $overall,
            'grade' => $gradeLabel,
            'passed_count' => $passedCount,
            'total_count' => 10,
            'factors' => $factors,
            // Flat keys for backwards compatibility:
            'search_intent' => $searchIntentScore,
            'topic_coverage' => $topicCoverageScore,
            'original_value' => $originalValueScore,
            'readability' => $readabilityScore,
            'seo_structure' => $seoStructureScore,
            'internal_linking' => $internalLinkingScore,
            'outbound_citations' => $outboundCitationsScore,
            'eeat_signals' => $eeatScore,
            'geo_readiness' => $geoScore,
            'technical_seo' => $technicalScore,
        ];
    }

    #[On('updateTitle')]
    #[On('applyTitle')]
    public function applyTitle(?string $newTitle = null, ?string $title = null)
    {
        $titleToUse = $newTitle ?? $title;
        if ($titleToUse) {
            $safeTitle = mb_substr(trim(preg_replace('/\s+/u', ' ', strip_tags($titleToUse))), 0, 190);
            $this->title = $safeTitle;
            if ($this->documentId) {
                Document::where('id', $this->documentId)->update(['title' => $safeTitle]);
            }
            if ($this->document) {
                $this->document->title = $safeTitle;
            }
            $this->runSeoAudit();
        }
    }

    public function updatedTitle($value): void
    {
        if ($this->document) {
            $this->document->update(['title' => $value]);
        }
        $this->isDirty = true;
    }

    public function saveActiveTitle()
    {
        if ($this->document && $this->title) {
            $this->document->update(['title' => $this->title]);
            $this->dispatch('notify', message: 'Document title saved.', type: 'success');
        }
    }

    public function applyMetaDescription(?string $meta = null, ?string $metaDescription = null)
    {
        $descToUse = $meta ?? $metaDescription;
        if ($descToUse) {
            $this->metaDescription = $descToUse;
            if ($this->document) {
                $this->document->update(['meta_description' => $descToUse]);
            }
            $this->runSeoAudit();
        }
    }

    public function addSuggestedKeyword(string $kw)
    {
        $kw = trim($kw);
        if ($kw && ! in_array($kw, $this->secondaryKeywords)) {
            $this->secondaryKeywords[] = $kw;
            $this->runSeoAudit();
        }
    }
}
