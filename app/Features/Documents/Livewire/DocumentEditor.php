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

namespace App\Features\Documents\Livewire;

use App\Features\AI\Models\AiProvider;
use App\Features\Documents\Actions\CreateDocumentShare;
use App\Features\Documents\Actions\RestoreDocumentVersion;
use App\Features\Documents\Actions\RevokeDocumentShare;
use App\Features\Documents\Actions\SaveDocumentVersion;
use App\Features\Documents\Contracts\EditorRegistry;
use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentShare;
use App\Features\Documents\Models\DocumentVersion;
use App\Features\Projects\Models\Project;
use App\Features\SEO\Actions\AnalyzeDocumentSeo;
use App\Features\SEO\Actions\GenerateSeoMetadata;
use App\Features\SEO\Models\SeoAnalysis;
use App\Features\SEO\Services\SeoAnalyzer;
use Exception;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.workspace')]
class DocumentEditor extends Component
{
    public int $documentId;
    public string $title = '';
    public string $contentHtml = '';
    public ?int $projectId = null;
    public string $status = 'draft';
    public string $editorType = 'tiptap';

    public int $wordCount = 0;
    public int $characterCount = 0;
    public int $readingTimeMinutes = 1;

    public bool $isSaving = false;
    public string $saveStatusText = 'All changes saved';
    public ?string $lastSavedAt = null;

    public bool $showVersionHistory = false;

    // Document Sharing State
    public bool $showShareModal = false;
    public ?string $shareToken = null;
    public string $sharePassword = '';
    public bool $shareAllowCopy = true;
    public bool $shareAllowDownload = true;
    public ?int $shareExpiryDays = null;
    public int $shareViewCount = 0;
    public bool $isShareActive = false;
    public string $shareUrl = '';

    // Real-Time SEO Intelligence State
    public bool $showSeoDrawer = false;
    public string $targetKeyword = '';
    public array $secondaryKeywords = [];
    public string $newSecondaryKeyword = '';
    public ?array $seoData = null;
    public bool $isAnalyzingSeo = false;

    // AI SEO Generator State
    public bool $isGeneratingSeo = false;
    public string $aiSeoType = '';
    public array $aiSeoResults = [];
    public string $seoErrorMessage = '';
    public string $metaDescription = '';
    public array $aiTitles = [];
    public array $aiMetaDescriptions = [];
    public array $aiFaqs = [];
    public string $aiQuickAnswer = '';
    public array $aiContentGaps = [];
    public ?array $aiQualityAudit = null;

    public function mount(int $id, SeoAnalyzer $analyzer)
    {
        $document = Document::with(['content', 'project'])
            ->where('user_id', Auth::id())
            ->findOrFail($id);

        $this->documentId = $document->id;
        $this->title = $document->title;
        $this->contentHtml = $document->content->content_html ?? '<p></p>';
        $this->projectId = $document->project_id;
        $this->status = $document->status;
        $this->editorType = $document->editor_type ?? 'tiptap';
        $this->wordCount = $document->word_count;
        $this->characterCount = $document->character_count;
        $this->readingTimeMinutes = $document->reading_time_minutes;
        $this->lastSavedAt = $document->updated_at->format('H:i:s');

        // Load existing SEO analysis target keyword if available
        $existingSeo = SeoAnalysis::where('document_id', $document->id)->first();
        if ($existingSeo) {
            $this->targetKeyword = $existingSeo->target_keyword ?? '';
            $this->secondaryKeywords = $existingSeo->secondary_keywords ?? [];
            $this->metaDescription = $existingSeo->metrics['meta_description'] ?? '';
        }

        // Always run comprehensive analysis to guarantee rank_math pillars, recommendations, and marked_html exist
        $this->seoData = $analyzer->analyze(
            $this->contentHtml, 
            $this->title, 
            $this->targetKeyword ?: null, 
            $this->secondaryKeywords ?? [],
            $this->metaDescription
        );

        $this->generateQualityAudit();
    }

    #[On('autosave')]
    public function autosave(string $html, ?array $json = null, ?SaveDocumentVersion $action = null)
    {
        $this->isSaving = true;
        $this->saveStatusText = 'Saving...';
        $this->contentHtml = $html;

        $user = Auth::user();
        $document = Document::where('user_id', $user->id)->findOrFail($this->documentId);

        $plain = strip_tags($html);
        $this->wordCount = str_word_count($plain);
        $this->characterCount = mb_strlen($plain);
        $this->readingTimeMinutes = max(1, (int) ceil($this->wordCount / 200));

        if ($document->content) {
            $document->content->update([
                'content_html' => $html,
                'content_json' => $json,
                'content_plain' => $plain,
            ]);
        }

        $document->update([
            'title' => $this->title,
            'project_id' => $this->projectId,
            'status' => $this->status,
            'word_count' => $this->wordCount,
            'character_count' => $this->characterCount,
            'reading_time_minutes' => $this->readingTimeMinutes,
        ]);

        $this->isSaving = false;
        $this->lastSavedAt = now()->format('H:i:s');
        $this->saveStatusText = 'Saved at ' . $this->lastSavedAt;
    }

    public function runSeoAudit(?SeoAnalyzer $analyzer = null)
    {
        $analyzer = $analyzer ?? app(SeoAnalyzer::class);
        $this->isAnalyzingSeo = true;
        
        try {
            $this->seoData = $analyzer->analyze(
                $this->contentHtml,
                $this->title,
                $this->targetKeyword ?: null,
                $this->secondaryKeywords,
                $this->metaDescription ?? ''
            );
            
            $savedMetrics = $this->seoData['metrics'] ?? [];
            if (!empty($this->metaDescription)) {
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
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isAnalyzingSeo = false;
        }
    }

    public function queueSeoAudit()
    {
        $this->runSeoAudit();
    }

    public function addSecondaryKeyword()
    {
        $kw = trim($this->newSecondaryKeyword);
        if (!empty($kw) && !in_array($kw, $this->secondaryKeywords)) {
            $this->secondaryKeywords[] = $kw;
            $this->newSecondaryKeyword = '';
            $this->queueSeoAudit();
        }
    }

    public function removeSecondaryKeyword(int $index)
    {
        if (isset($this->secondaryKeywords[$index])) {
            unset($this->secondaryKeywords[$index]);
            $this->secondaryKeywords = array_values($this->secondaryKeywords);
            $this->queueSeoAudit();
        }
    }

    public function generateSeoTitles(GenerateSeoMetadata $generator)
    {
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

    public function generateMetaDescriptions(GenerateSeoMetadata $generator)
    {
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'metas';
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

    public function suggestLsiKeywords(GenerateSeoMetadata $generator)
    {
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'lsi';
        $this->seoErrorMessage = '';

        try {
            $this->aiSeoResults = $generator->suggestKeywords(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateFaqSuggestions(GenerateSeoMetadata $generator)
    {
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'faqs';
        $this->seoErrorMessage = '';

        try {
            $this->aiFaqs = $generator->generateFaqs(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateQuickAnswer(GenerateSeoMetadata $generator)
    {
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'quick_answer';
        $this->seoErrorMessage = '';

        try {
            $this->aiQuickAnswer = $generator->generateQuickAnswer(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
        } catch (Exception $e) {
            $this->seoErrorMessage = $e->getMessage();
        } finally {
            $this->isGeneratingSeo = false;
        }
    }

    public function generateContentGaps(GenerateSeoMetadata $generator)
    {
        $this->isGeneratingSeo = true;
        $this->aiSeoType = 'gaps';
        $this->seoErrorMessage = '';

        try {
            $this->aiContentGaps = $generator->generateContentGaps(Auth::user(), strip_tags($this->contentHtml), $this->targetKeyword);
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
        if (!empty($kw)) {
            $inTitle = mb_strpos($lowerTitle, $kw) !== false;
            $tenPctWords = mb_strtolower(implode(' ', array_slice(preg_split('/\s+/u', $plain, -1, PREG_SPLIT_NO_EMPTY), 0, max(15, (int) round($words * 0.1)))));
            $inIntro = mb_strpos($tenPctWords, $kw) !== false;
            $inH2 = false;
            if (!empty($metrics['headings']['h2'])) {
                preg_match_all('/<h2[^>]*>(.*?)<\/h2>/si', $this->contentHtml, $h2m);
                $inH2 = mb_strpos(mb_strtolower(implode(' ', $h2m[1] ?? [])), $kw) !== false;
            }
            $inMeta = !empty($this->metaDescription) && mb_strpos(mb_strtolower($this->metaDescription), $kw) !== false;

            $points = 20;
            if ($inTitle) $points += 30;
            if ($inIntro) $points += 25;
            if ($inH2) $points += 15;
            if ($inMeta) $points += 10;
            $searchIntentScore = min(100, $points);

            if ($inTitle && $inIntro && ($inH2 || $inMeta)) {
                $searchIntentStatus = 'Optimal query intent alignment';
            } elseif ($inTitle || $inIntro) {
                $searchIntentStatus = 'Partial query intent alignment';
            } else {
                $searchIntentStatus = 'Keyword missing from intro & headings';
            }
        } elseif ($words > 300 && !empty($this->title)) {
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
            $originalValueStatus = "1 single data point found";
        } else {
            $originalValueScore = 38;
            $originalValueStatus = "No verifiable data points detected";
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
            $seoStructureStatus = "Only 1 H2 subheading found";
        } else {
            $seoStructureScore = 38;
            $seoStructureStatus = "No H2 subheadings detected";
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
            $internalLinkingStatus = "0 internal cluster links";
        }

        // 7. Authoritative Outbound Citations (0-100)
        $externalLinks = $metrics['links']['external'] ?? 0;
        $hasQuotes = $geo['has_quotes'] ?? false;
        if ($externalLinks >= 2) {
            $outboundCitationsScore = $hasQuotes ? 98 : 92;
            $outboundCitationsStatus = "{$externalLinks} external citations" . ($hasQuotes ? " + quotes" : "");
        } elseif ($externalLinks === 1) {
            $outboundCitationsScore = $hasQuotes ? 86 : 76;
            $outboundCitationsStatus = "1 citation" . ($hasQuotes ? " + expert quote" : "");
        } else {
            $outboundCitationsScore = $hasQuotes ? 62 : 36;
            $outboundCitationsStatus = $hasQuotes ? "Quote found, no outbound links" : "0 external citations";
        }

        // 8. E-E-A-T First-Hand Experience & Trust (0-100)
        $hasExpPhrases = preg_match('/\b(in our (tests|testing|evaluation|lab|study|experience)|we tested|we evaluated|our findings|in my experience|case study|hands-on|benchmarks show|our team verified|in our trial|we observed)\b/i', $plain) === 1;
        $hasTable = $geo['has_table'] ?? false;
        if ($hasExpPhrases) {
            $eeatScore = 95;
            $eeatStatus = "First-hand testing & empirical signals present";
        } elseif ($hasTable || $hasQuotes) {
            $eeatScore = 78;
            $eeatStatus = "Structured data/quotes present";
        } else {
            $eeatScore = 46;
            $eeatStatus = "Missing first-hand experience markers";
        }

        // 9. Google AI Overviews & GEO Readiness (0-100)
        $hasDirectAnswer = $geo['has_direct_answer'] ?? false;
        $hasStructuredTable = $geo['has_table'] ?? false;
        $paaCount = $geo['paa_count'] ?? 0;
        $geoPoints = 20;
        if ($hasDirectAnswer) $geoPoints += 40;
        if ($hasStructuredTable) $geoPoints += 25;
        if ($paaCount >= 2) $geoPoints += 15;
        $geoScore = min(100, $geoPoints);
        $geoStatus = ($hasDirectAnswer ? "✓ Direct snippet" : "✕ No snippet") . ' • ' . ($hasStructuredTable ? "✓ Table" : "✕ No table") . ' • ' . "{$paaCount} PAA";

        // 10. Technical Schema & Semantic Markup (0-100)
        $isValidSchema = $schema['validation']['is_valid'] ?? false;
        $schemaType = $schema['recommended_type'] ?? 'Article';
        $titleLen = mb_strlen($this->title ?? '');
        $metaLen = mb_strlen($this->metaDescription ?? '');
        $techPoints = 30;
        if ($isValidSchema) $techPoints += 40;
        if ($titleLen >= 40 && $titleLen <= 65) $techPoints += 15;
        if ($metaLen >= 120 && $metaLen <= 160) $techPoints += 15;
        $technicalScore = min(100, $techPoints);
        $technicalStatus = $isValidSchema ? "Valid {$schemaType} Schema.org JSON-LD" : "Schema not validated";

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
        $passedCount = count(array_filter($scores, fn($s) => $s >= 75));

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
    public function applyTitle(?string $newTitle = null)
    {
        if ($newTitle !== null && trim($newTitle) !== '') {
            $this->title = trim($newTitle);
        }
        Document::where('id', $this->documentId)->update(['title' => $this->title]);
        $this->queueSeoAudit();
        session()->flash('status', 'Document title updated!');
    }

    public function saveActiveTitle()
    {
        $this->applyTitle($this->title);
    }

    public function applyMetaDescription(?string $meta = null)
    {
        if ($meta !== null) {
            $this->metaDescription = trim($meta);
        }
        
        $seo = SeoAnalysis::where('document_id', $this->documentId)->first();
        if ($seo) {
            $metrics = $seo->metrics ?? [];
            $metrics['meta_description'] = $this->metaDescription;
            $seo->update(['metrics' => $metrics]);
        }
        
        $this->queueSeoAudit();
        session()->flash('status', 'Meta description saved & audited!');
    }

    public function addSuggestedKeyword(string $kw)
    {
        if (!in_array($kw, $this->secondaryKeywords)) {
            $this->secondaryKeywords[] = $kw;
            $this->queueSeoAudit();
        }
    }

    public function saveExplicitSnapshot(SaveDocumentVersion $action)
    {
        $user = Auth::user();
        $document = Document::with('content')->where('user_id', $user->id)->findOrFail($this->documentId);

        $action->execute($document, $user, [
            'title' => $this->title,
            'content_html' => $this->contentHtml,
            'operation_type' => 'manual_save',
            'summary' => 'Manual snapshot created',
        ]);

        $this->saveStatusText = 'New snapshot created';
    }

    public function restoreVersion(int $versionId, RestoreDocumentVersion $action)
    {
        $user = Auth::user();
        $document = Document::where('user_id', $user->id)->findOrFail($this->documentId);
        $version = DocumentVersion::where('document_id', $document->id)->findOrFail($versionId);

        $restored = $action->execute($document, $version, $user);

        $this->title = $restored->title;
        $this->contentHtml = $restored->content->content_html;
        $this->wordCount = $restored->word_count;
        $this->characterCount = $restored->character_count;
        $this->readingTimeMinutes = $restored->reading_time_minutes;
        $this->showVersionHistory = false;

        $this->dispatch('editor:setContent', content: $this->contentHtml);
        session()->flash('status', 'Restored to Version #' . $version->version_number);
    }

    #[On('switchEditorType')]
    public function switchEditorType(string $type = '', string $newType = '')
    {
        $target = !empty($type) ? $type : $newType;
        if (EditorRegistry::isValidEditor($target)) {
            $this->editorType = $target;
            Document::where('id', $this->documentId)->update(['editor_type' => $target]);
            $this->dispatch('editor:reload', editorType: $target);
        }
    }

    public function openShareModal()
    {
        $this->loadShareState();
        $this->showShareModal = true;
    }

    public function loadShareState()
    {
        $share = DocumentShare::where('document_id', $this->documentId)
            ->where('is_active', true)
            ->first();

        if ($share) {
            $this->shareToken = $share->share_token;
            $this->isShareActive = true;
            $this->shareAllowCopy = $share->allow_copy;
            $this->shareAllowDownload = $share->allow_download;
            $this->shareViewCount = $share->view_count;
            $this->shareUrl = route('public.share', ['token' => $share->share_token]);
        } else {
            $this->shareToken = null;
            $this->isShareActive = false;
            $this->shareUrl = '';
        }
    }

    public function createOrUpdateShare(CreateDocumentShare $action)
    {
        $document = Document::where('user_id', Auth::id())->findOrFail($this->documentId);

        $share = $action->execute($document, [
            'password' => !empty($this->sharePassword) ? $this->sharePassword : null,
            'allow_copy' => $this->shareAllowCopy,
            'allow_download' => $this->shareAllowDownload,
            'expires_in_days' => $this->shareExpiryDays,
        ]);

        $this->loadShareState();
        session()->flash('share_status', 'Share link generated successfully!');
    }

    public function revokeShare(RevokeDocumentShare $action)
    {
        $share = DocumentShare::where('document_id', $this->documentId)
            ->where('is_active', true)
            ->first();

        if ($share) {
            $action->execute($share);
        }

        $this->loadShareState();
        session()->flash('share_status', 'Share link has been revoked.');
    }

public function render()
    {
        $document = Document::with(['versions.creator', 'project'])->findOrFail($this->documentId);
        $projects = Project::where('user_id', Auth::id())->get();
        $availableEditors = EditorRegistry::getAvailableEditors();

        $availableAiModels = \App\Features\AI\Models\AiModel::where('is_active', true)
            ->orderBy('is_combo', 'desc')
            ->orderBy('id', 'asc')
            ->get();

        $availableProviders = AiProvider::where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'slug', 'is_local']);

        return view('documents.editor', [
            'document'           => $document,
            'projects'           => $projects,
            'availableEditors'   => $availableEditors,
            'availableAiModels'  => $availableAiModels,
            'availableProviders' => $availableProviders,
        ]);
    }
}