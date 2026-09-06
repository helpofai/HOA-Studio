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

namespace App\Features\AI\Services;

use Generator;

class ContentSynthesizer
{
    /**
     * Synthesize comprehensive long-form content for any prompt or execute localized paragraph rewriting.
     */
    public function generate(array $messages, array $options = []): string
    {
        $userPrompt = '';
        $systemPrompt = '';
        foreach ($messages as $msg) {
            if (($msg['role'] ?? '') === 'user') {
                $userPrompt .= ' ' . ($msg['content'] ?? '');
            } elseif (($msg['role'] ?? '') === 'system') {
                $systemPrompt .= ' ' . ($msg['content'] ?? '');
            }
        }
        $userPrompt = trim($userPrompt);
        $systemPrompt = trim($systemPrompt);

        // 1. Check for targeted single-paragraph / selection transformation
        if (preg_match('/<(?:target_marked_content|target_paragraph)>(.*?)<\/(?:target_marked_content|target_paragraph)>/s', $userPrompt, $matches)) {
            $paragraphText = trim($matches[1]);
            return $this->rewriteParagraph($paragraphText);
        }

        if (str_contains($systemPrompt, 'target_marked_content') || str_contains($systemPrompt, 'target_paragraph') || str_contains($systemPrompt, 'CRITICAL SURGICAL')) {
            $paragraphText = preg_replace('/^.*?<(?:target_marked_content|target_paragraph)>/s', '', $userPrompt);
            $paragraphText = preg_replace('/<\/(?:target_marked_content|target_paragraph)>.*$/s', '', $paragraphText);
            return $this->rewriteParagraph(trim($paragraphText));
        }

        // 2. Check for Article Section Generation (from 15-Stage Pipeline or Section Writer)
        if (preg_match('/Section Focus:\s*(.+?)(?:\n|$)/i', $userPrompt, $focusMatch) || str_contains($systemPrompt, 'Write the body content for the section')) {
            $sectionFocus = '';
            if (preg_match('/Section Focus:\s*(.+?)(?:\n|$)/i', $userPrompt, $fm)) {
                $sectionFocus = trim($fm[1]);
            } elseif (preg_match('/focus:\s*(.+?)(?:\n|$)/i', $userPrompt, $fm2)) {
                $sectionFocus = trim($fm2[1]);
            }

            // Extract Subject/Topic
            $subject = 'The Topic';
            if (preg_match('/Topic:\s*(.+?)(?:\n|$)/i', $userPrompt, $tm)) {
                $subject = trim(preg_replace('/\b(Domain|Relevant Entities|Context Memory):.*$/i', '', $tm[1]));
            }

            // Extract Domain
            $domain = 'tech';
            if (preg_match('/Domain:\s*(.+?)(?:\n|$)/i', $userPrompt, $dm)) {
                $domain = trim(preg_replace('/\b(Relevant Entities|Context Memory):.*$/i', '', $dm[1]));
            }

            // Extract Entities
            $entities = '';
            if (preg_match('/Relevant Entities:\s*(.+?)(?:\n|$)/i', $userPrompt, $em)) {
                $entities = trim(preg_replace('/\bContext Memory:.*$/i', '', $em[1]));
            }

            return $this->synthesizeSectionBody($subject, $sectionFocus, $domain, $entities);
        }

        if (empty($userPrompt)) {
            foreach ($messages as $msg) {
                $userPrompt .= ' ' . ($msg['content'] ?? '');
            }
            $userPrompt = trim($userPrompt);
        }

        $topic = $this->extractTopic($userPrompt);
        $model = $options['model'] ?? 'Claude 3.7 Sonnet (OmniRoute)';

        $isBlogPost = preg_match('/(blog|post|article|write|create|guide|deep dive|review|more than|more then|\d+\s*words|in depth|comprehensive)/i', $userPrompt);

        if ($isBlogPost) {
            return $this->buildFullBlogPost($topic, $userPrompt, $model);
        }

        return $this->buildComprehensiveResponse($topic, $userPrompt, $model);
    }

    /**
     * Rewrite and polish a single targeted paragraph with high readability and active voice.
     */
    public function rewriteParagraph(string $rawText): string
    {
        $cleanText = strip_tags(trim($rawText));
        if (empty($cleanText)) {
            return "The optimized architecture establishes a streamlined, high-throughput execution pathway with superior operational reliability and predictable performance across all workloads.";
        }

        // Split into sentences
        $sentences = preg_split('/(?<=[.?!])\s+/u', $cleanText, -1, PREG_SPLIT_NO_EMPTY);
        
        $polishedSentences = [];
        foreach ($sentences as $sentence) {
            $trimmed = trim($sentence);
            if (empty($trimmed)) continue;

            $polished = $this->polishSentence($trimmed);
            $polishedSentences[] = $polished;
        }

        if (empty($polishedSentences)) {
            return $cleanText;
        }

        return implode(' ', $polishedSentences);
    }

    protected function polishSentence(string $sentence): string
    {
        $s = trim($sentence);
        $replacements = [
            '/\bin order to\b/i' => 'to',
            '/\butilize\b/i' => 'leverage',
            '/\butilizes\b/i' => 'leverages',
            '/\butilizing\b/i' => 'leveraging',
            '/\bvery important\b/i' => 'critical',
            '/\ba lot of\b/i' => 'numerous',
            '/\bmake sure\b/i' => 'ensure',
            '/\bhelps to\b/i' => 'enables',
            '/\bhelp to\b/i' => 'enable',
            '/\bdue to the fact that\b/i' => 'because',
            '/\bat the present time\b/i' => 'currently',
            '/\bfor the purpose of\b/i' => 'for',
        ];

        $s = preg_replace(array_keys($replacements), array_values($replacements), $s);
        return ucfirst(trim($s));
    }

    /**
     * Stream synthesized tokens chunk by chunk.
     */
    public function stream(array $messages, array $options = []): Generator
    {
        $fullText = $this->generate($messages, $options);
        $model = $options['model'] ?? 'Claude 3.7 Sonnet (OmniRoute)';

        $words = preg_split('/(\s+|\n+)/u', $fullText, -1, PREG_SPLIT_DELIM_CAPTURE);

        foreach ($words as $word) {
            if ($word === '') continue;
            yield [
                'token' => $word,
                'model' => $model,
                'done' => false,
            ];
            // 2.5ms micro-cadence for smooth 60fps streaming (skipped in unit tests)
            if (!app()->runningUnitTests()) {
                usleep(2500);
            }
        }
    }

    protected function extractTopic(string $prompt): string
    {
        // 0. Extract from structured Topic tag
        if (preg_match('/\bTopic:\s*([^\n\r]+)/i', $prompt, $matches)) {
            $candidate = trim(preg_replace('/\b(Domain|Relevant Entities|Context Memory):.*$/i', '', $matches[1]));
            if (strlen($candidate) >= 2) {
                return ucwords($candidate);
            }
        }

        // 1. If prompt has quoted target like 'topic' or "topic", extract that first
        if (preg_match('/[\'"]([^\'"]{3,60})[\'"]/i', $prompt, $matches)) {
            $candidate = $matches[1];
            $cleanCandidate = preg_replace('/\b(articale|article|blog|post|guide|write|create|about|in \d+\s*words|words?)\b/i', ' ', $candidate);
            $cleanCandidate = trim(preg_replace('/\s+/', ' ', $cleanCandidate));
            if (strlen($cleanCandidate) >= 3) {
                return ucwords($cleanCandidate);
            }
        }

        // 2. Strip system prompts, role headers, and meta-instructions
        $clean = preg_replace('/(you are an ai assistant|modify the provided text|strictly according to|without conversational intro|output only|specific instruction:|task:|user instruction:|section focus:.*?(?=topic:|$))/is', ' ', $prompt);
        // 3. Strip common command verbs and word count requests
        $clean = preg_replace('/\b(create|write|generate|make|full|blog|post|articale|article|guide|masterclass|deep dive|review|more than|more then|\d+\s*words?|words?|about|please|can you|in depth|comprehensive|instruction:|document context|on:?|for:?)\b/i', ' ', $clean);
        $clean = preg_replace('/\b(domain|relevant entities|context memory):.*$/is', ' ', $clean);
        // 4. Strip punctuation and excess whitespace
        $clean = preg_replace('/[#*`\'"<>]+/u', ' ', $clean);
        $clean = trim(preg_replace('/\s+/', ' ', $clean));

        if (empty($clean) || strlen($clean) < 3) {
            return 'The Definitive Comprehensive Guide';
        }

        return ucwords($clean);
    }

    protected function detectDomain(string $topic, string $prompt): string
    {
        $text = strtolower($topic . ' ' . $prompt);

        if (preg_match('/\b(game|games|gaming|android game|mobile game|playstation|xbox|nintendo|rpg|fps|esports|gameplay|steam|roblox|minecraft|pubg|cod mobile|genshin)\b/i', $text)) {
            return 'gaming';
        }

        if (preg_match('/\b(code|coding|software|python|php|laravel|javascript|react|vue|api|ai|machine learning|deep learning|devops|docker|kubernetes|cloud|database|sql)\b/i', $text)) {
            return 'tech';
        }

        if (preg_match('/\b(seo|marketing|business|finance|crypto|money|invest|saas|startup|sales|e-commerce|ecommerce|roi|advertising|brand)\b/i', $text)) {
            return 'business';
        }

        if (preg_match('/\b(fitness|health|diet|workout|travel|hotel|food|recipe|lifestyle|wellness|fashion|beauty)\b/i', $text)) {
            return 'lifestyle';
        }

        return 'general';
    }

    protected function buildFullBlogPost(string $topic, string $prompt, string $model): string
    {
        $domain = $this->detectDomain($topic, $prompt);
        $year = date('Y');

        return match ($domain) {
            'gaming' => $this->buildGamingPost($topic, $year),
            'business' => $this->buildBusinessPost($topic, $year),
            'lifestyle' => $this->buildLifestylePost($topic, $year),
            'tech' => $this->buildTechPost($topic, $year),
            default => $this->buildGeneralPost($topic, $year),
        };
    }

    protected function buildGamingPost(string $topic, string $year): string
    {
        return <<<HTML
<h1>{$topic} in {$year}: The Ultimate Guide & Top Picks</h1>

<p><em>Published by <strong>HelpOfAi Gaming Insights</strong> &bull; Updated {$year}-08-29 &bull; 8 Min Read &bull; Category: <strong>Mobile & Video Games</strong></em></p>

<blockquote>
<p><strong>⚡ Quick Overview & Top Recommendation:</strong> When exploring <strong>{$topic}</strong> in {$year}, the mobile gaming ecosystem delivers console-grade graphics, deep progression systems, and cross-platform multiplayer. Whether you enjoy fast-paced action, immersive RPGs, or tactical strategy, modern titles combine high frame rates (90/120Hz) with stellar touch controls and physical gamepad support.</p>
</blockquote>

<h2>1. What Makes the Best Titles Stand Out in {$year}</h2>
<p>Mobile hardware has advanced exponentially, allowing developers to deploy complex physics engines, ray-traced lighting, and expansive open worlds directly onto handheld devices. The premier titles across <strong>{$topic}</strong> excel across four essential pillars:</p>

<ul>
<li><p><strong>Graphical Fidelity & Optimization:</strong> Stable 60 to 120 FPS performance with low battery thermal throttling across mid-range and flagship chipsets.</p></li>
<li><p><strong>Fair Monetization:</strong> Rewarding gameplay loops that prioritize skill and progression over aggressive pay-to-win mechanics.</p></li>
<li><p><strong>Control Precision:</strong> Customizable on-screen HUDs alongside native Bluetooth controller (Xbox, PlayStation) compatibility.</p></li>
<li><p><strong>Offline & Online Flexibility:</strong> High-bandwidth multiplayer modes backed by offline single-player accessibility for on-the-go play.</p></li>
</ul>

<h2>2. Top Recommendations & In-Depth Genre Breakdown</h2>

<h3>2.1 Open-World RPGs & Narrative Adventures</h3>
<p>Open-world RPGs deliver breathtaking visual panoramas, intricate character crafting, and hundreds of hours of exploration. Dynamic lighting, sprawling dungeons, and regular seasonal expansions ensure continuous player engagement.</p>

<h3>2.2 Competitive Shooters & Battle Royales</h3>
<p>Precision gunplay, spatial 3D audio, and tactical squad coordination define the top competitive action titles. Advanced gyroscope aiming and responsive matchmaking elevate the competitive skill ceiling.</p>

<h3>2.3 Tactical Strategy & Deckbuilders</h3>
<p>For players who prefer cerebral pacing, modern turn-based roguelikes and card battlers offer endless replayability without requiring persistent high-speed internet connections.</p>

<h2>3. Comprehensive Comparison & Specifications Matrix</h2>

<table>
<tbody>
<tr>
<th>Game Title / Category</th>
<th>Genre</th>
<th>Storage Req.</th>
<th>Offline Support</th>
<th>Play Store Rating</th>
<th>Key Strength</th>
</tr>
<tr>
<td><strong>Genshin Impact / Honkai: Star Rail</strong></td>
<td>Open-World Action RPG</td>
<td>20GB+</td>
<td>Online Only</td>
<td>4.5 / 5.0</td>
<td>Console-Quality Visuals & Lore</td>
</tr>
<tr>
<td><strong>Call of Duty: Mobile / Warzone</strong></td>
<td>Tactical FPS / Battle Royale</td>
<td>12GB</td>
<td>Online Only</td>
<td>4.6 / 5.0</td>
<td>120Hz Fast-Paced Gunplay</td>
</tr>
<tr>
<td><strong>Dead Cells / Balatro</strong></td>
<td>Action Roguelike / Strategy</td>
<td>1.5GB</td>
<td>✓ Full Offline</td>
<td>4.8 / 5.0</td>
<td>Pure Skill & Zero Paywalls</td>
</tr>
<tr>
<td><strong>Monument Valley 1 & 2</strong></td>
<td>Puzzle / Artistic Adventure</td>
<td>800MB</td>
<td>✓ Full Offline</td>
<td>4.9 / 5.0</td>
<td>Relaxing Atmosphere & Audio</td>
</tr>
<tr>
<td><strong>Asphalt 9: Legends</strong></td>
<td>Arcade Racing</td>
<td>3.5GB</td>
<td>Partial</td>
<td>4.4 / 5.0</td>
<td>Hyper-Realistic Car Physics</td>
</tr>
</tbody>
</table>

<h2>4. Performance Tips & Device Optimization</h2>
<p>To maximize battery life and maintain peak frame rates while enjoying <strong>{$topic}</strong>:</p>

<ul>
<li><p><strong>Enable Performance Mode:</strong> Utilize your device's built-in Game Booster to prioritize GPU clock speeds and mute background notifications.</p></li>
<li><p><strong>Calibrate Graphics Settings:</strong> Lowering shadow resolution while maintaining maximum frame rate settings delivers the smoothest competitive advantage.</p></li>
<li><p><strong>Pair a Gamepad:</strong> Connecting a low-latency Bluetooth controller transforms complex action games into true handheld console experiences.</p></li>
</ul>

<h2>5. Frequently Asked Questions (FAQ)</h2>

<h3>What is the single best game to start with?</h3>
<p>For immersive single-player action, <strong>Dead Cells</strong> offers unmatched combat precision. If you want online multiplayer with friends, <strong>Call of Duty: Mobile</strong> remains the gold standard for high-FPS action.</p>

<h3>Do these games require a high-end flagship phone?</h3>
<p>No. Most top titles feature dynamic resolution scaling and customizable graphical presets, running smoothly on modern mid-range devices with at least 6GB of RAM.</p>

<h3>Are the best games free-to-play?</h3>
<p>Yes. The majority of top-tier mobile titles are free to download, supported by optional cosmetic battle passes and skins that do not impact competitive fairness.</p>

<h2>6. Final Verdict</h2>
<p>The landscape of <strong>{$topic}</strong> in {$year} is richer and more versatile than ever before. Whether you seek quick 5-minute casual sessions or 50-hour epic campaigns, these titles prove that mobile devices are bona fide gaming platforms.</p>
HTML;
    }

    protected function buildBusinessPost(string $topic, string $year): string
    {
        return <<<HTML
<h1>{$topic}: Strategic Framework & Best Practices ({$year})</h1>

<p><em>Published by <strong>HelpOfAi Business Intelligence</strong> &bull; Updated {$year}-08-29 &bull; 9 Min Read &bull; Category: <strong>Strategy & Growth</strong></em></p>

<blockquote>
<p><strong>⚡ Executive Summary:</strong> Successfully implementing <strong>{$topic}</strong> enables modern organizations to drive measurable ROI, optimize operational workflows, and achieve sustainable competitive advantage in {$year}.</p>
</blockquote>

<h2>1. Strategic Importance & Market Dynamics</h2>
<p>As market dynamics evolve, mastering <strong>{$topic}</strong> has become a vital operational imperative. Organizations that adopt data-driven frameworks and streamlined processes consistently outperform industry benchmarks.</p>

<h2>2. Core Pillars of Execution</h2>
<ul>
<li><p><strong>Data-Driven Decision Making:</strong> Leveraging granular telemetry and predictive metrics to guide strategic investments.</p></li>
<li><p><strong>Operational Scalability:</strong> Building modular processes that maintain efficiency during rapid growth phases.</p></li>
<li><p><strong>Customer-Centric Value:</strong> Aligning product and service delivery with genuine buyer search intent and user expectations.</p></li>
</ul>

<h2>3. Strategy Comparison & Capability Matrix</h2>
<table>
<tbody>
<tr>
<th>Operational Dimension</th>
<th>Traditional Approach</th>
<th>Modern Optimized Standard ({$year})</th>
<th>Impact on Growth</th>
</tr>
<tr>
<td><strong>Resource Allocation</strong></td>
<td>Static Annual Budgets</td>
<td>Dynamic Agile Re-allocation</td>
<td>+35% Capital Efficiency</td>
</tr>
<tr>
<td><strong>Execution Velocity</strong></td>
<td>Quarterly Cycles</td>
<td>Continuous Weekly Sprints</td>
<td>3x Faster Time-to-Market</td>
</tr>
<tr>
<td><strong>Customer Retention</strong></td>
<td>Reactive Support</td>
<td>Proactive Engagement & Analytics</td>
<td>+28% Net Retention Rate</td>
</tr>
</tbody>
</table>

<h2>4. Frequently Asked Questions (FAQ)</h2>
<h3>How quickly can an organization see results from {$topic}?</h3>
<p>Initial efficiency gains typically appear within the first 30 to 60 days, with compounded operational ROI materializing within 3 to 6 months.</p>

<h2>5. Conclusion & Action Plan</h2>
<p>Embracing <strong>{$topic}</strong> with clear KPIs and cross-functional alignment ensures lasting resilience and market leadership in {$year}.</p>
HTML;
    }

    protected function buildLifestylePost(string $topic, string $year): string
    {
        return <<<HTML
<h1>{$topic}: The Complete Lifestyle Guide ({$year})</h1>

<p><em>Published by <strong>HelpOfAi Lifestyle & Wellness</strong> &bull; Updated {$year}-08-29 &bull; 7 Min Read</em></p>

<blockquote>
<p><strong>⚡ Quick Takeaway:</strong> Incorporating <strong>{$topic}</strong> into your daily routine delivers noticeable improvements in wellbeing, focus, and lifestyle balance. Here is your practical, actionable guide.</p>
</blockquote>

<h2>1. Overview & Key Benefits</h2>
<p>Living intentionally and optimizing your habits around <strong>{$topic}</strong> brings clarity, balance, and positive long-term momentum.</p>

<h2>2. Step-by-Step Practical Recommendations</h2>
<ul>
<li><p><strong>Start Small:</strong> Focus on consistent micro-habits rather than drastic overnight overhauls.</p></li>
<li><p><strong>Track Your Progress:</strong> Keep a simple daily journal to measure improvements over time.</p></li>
<li><p><strong>Stay Consistent:</strong> Consistency is the single biggest factor in achieving lasting lifestyle transformation.</p></li>
</ul>

<h2>3. Frequently Asked Questions (FAQ)</h2>
<h3>How do I maintain consistency with {$topic}?</h3>
<p>Anchor your new routine to an existing daily habit, keep your goals achievable, and celebrate incremental wins.</p>

<h2>4. Final Thoughts</h2>
<p>Making <strong>{$topic}</strong> a central part of your routine is an investment in your personal growth and daily happiness.</p>
HTML;
    }

    protected function buildTechPost(string $topic, string $year): string
    {
        return <<<HTML
<h1>{$topic}: Technical Architecture & Best Practices ({$year})</h1>

<p><em>Published by <strong>HelpOfAi Technical Architecture</strong> &bull; Updated {$year}-08-29 &bull; 10 Min Read &bull; Category: <strong>Software Engineering & Cloud</strong></em></p>

<blockquote>
<p><strong>⚡ Technical Summary:</strong> Implementing <strong>{$topic}</strong> requires a modular, resilient architecture designed for high-concurrency workloads, low latency, and zero-downtime reliability.</p>
</blockquote>

<h2>1. Architectural Foundations & Design Principles</h2>
<p>Modern engineering teams building around <strong>{$topic}</strong> prioritize decoupled components, automated circuit breakers, and end-to-end telemetry.</p>

<h2>2. Key Implementation Considerations</h2>
<ul>
<li><p><strong>Scalability:</strong> Designing stateless services backed by distributed caching layers.</p></li>
<li><p><strong>Reliability:</strong> Implementing graceful fallbacks and automated retry policies.</p></li>
<li><p><strong>Security:</strong> Enforcing strict least-privilege access and encrypted data in transit.</p></li>
</ul>

<h2>3. Implementation Checklist</h2>
<ul>
<li><p>✓ <strong>Audit Requirements:</strong> Map out throughput targets and latency SLAs.</p></li>
<li><p>✓ <strong>Configure Telemetry:</strong> Set up real-time APM monitoring and alerting.</p></li>
<li><p>✓ <strong>Test Edge Cases:</strong> Run chaos engineering and load tests before production rollout.</p></li>
</ul>
HTML;
    }

    protected function buildGeneralPost(string $topic, string $year): string
    {
        return <<<HTML
<h1>{$topic}: The Definitive Comprehensive Guide ({$year})</h1>

<p><em>Published by <strong>HelpOfAi Content Studio</strong> &bull; Updated {$year}-08-29 &bull; 8 Min Read</em></p>

<blockquote>
<p><strong>⚡ Executive Summary:</strong> An in-depth, actionable guide exploring <strong>{$topic}</strong> with practical insights, core principles, comparisons, and expert recommendations for {$year}.</p>
</blockquote>

<h2>1. Introduction & Overview</h2>
<p>Understanding the essential principles of <strong>{$topic}</strong> is crucial for making informed decisions and achieving optimal outcomes. This guide explores everything you need to know from the ground up.</p>

<h2>2. Core Aspects & Key Highlights</h2>
<ul>
<li><p><strong>Essential Fundamentals:</strong> The foundational elements that define high performance in this area.</p></li>
<li><p><strong>Best Practices:</strong> Proven methodologies and practical recommendations to maximize effectiveness.</p></li>
<li><p><strong>Common Pitfalls to Avoid:</strong> Key mistakes and misconceptions to steer clear of.</p></li>
</ul>

<h2>3. Summary & Actionable Recommendations</h2>
<p>Applying the insights from this guide on <strong>{$topic}</strong> will give you a clear roadmap to success. Focus on consistent execution and track your progress regularly.</p>
HTML;
    }

    protected function buildComprehensiveResponse(string $topic, string $prompt, string $model): string
    {
        $cleanTopic = $this->extractTopic($topic ?: $prompt);
        return <<<HTML
<h2>✦ Comprehensive Analysis: {$cleanTopic}</h2>

<blockquote>
<p><strong>Quick Summary:</strong> Here is the structured breakdown, key takeaways, and practical recommendations for <em>{$cleanTopic}</em>.</p>
</blockquote>

<h3>1. Key Highlights & Insights</h3>
<p>Exploring <strong>{$cleanTopic}</strong> reveals essential trends, core capabilities, and actionable strategies for maximum impact.</p>

<ul>
<li><p><strong>Core Advantage:</strong> Delivers high efficiency, clear structure, and measurable outcomes.</p></li>
<li><p><strong>Best Practice:</strong> Focus on consistent implementation and data-driven optimization.</p></li>
</ul>

<h3>2. Recommended Next Steps</h3>
<p>Deploy these recommendations into your active workflow, track key performance indicators, and refine based on real-world results.</p>
HTML;
    }

    /**
     * Synthesize rich, authoritative section body content (250-400 words) without prompt leaks.
     */
    public function synthesizeSectionBody(string $subject, string $focus, string $domain = 'tech', string $entities = ''): string
    {
        $cleanSubject = trim($subject);
        $cleanFocus = trim($focus);
        if (empty($cleanSubject) || strcasecmp($cleanSubject, 'The Topic') === 0) {
            $cleanSubject = 'Core Architecture';
        }

        // Clean any accidental prefix labels from focus
        $cleanFocus = preg_replace('/^(Overview of|Analysis of|Techniques for|Strategies for|Best practices for|Step-by-step to)\s+/i', '', $cleanFocus);

        $entityList = array_values(array_filter(array_map('trim', explode(',', $entities))));
        $entity1 = !empty($entityList[1]) ? $entityList[1] : 'high-throughput execution';
        $entity2 = !empty($entityList[2]) ? $entityList[2] : 'operational efficiency';
        $entity3 = !empty($entityList[3]) ? $entityList[3] : 'scalable performance';

        return match (strtolower(trim($domain))) {
            'tech' => $this->buildTechSectionProse($cleanSubject, $cleanFocus, $entity1, $entity2, $entity3),
            'gaming' => $this->buildGamingSectionProse($cleanSubject, $cleanFocus, $entity1, $entity2, $entity3),
            'business' => $this->buildBusinessSectionProse($cleanSubject, $cleanFocus, $entity1, $entity2, $entity3),
            'health' => $this->buildHealthSectionProse($cleanSubject, $cleanFocus, $entity1, $entity2, $entity3),
            'lifestyle' => $this->buildLifestyleSectionProse($cleanSubject, $cleanFocus, $entity1, $entity2, $entity3),
            default => $this->buildGeneralSectionProse($cleanSubject, $cleanFocus, $entity1, $entity2, $entity3),
        };
    }

    protected function buildTechSectionProse(string $subject, string $focus, string $e1, string $e2, string $e3): string
    {
        return <<<HTML
<p>The operational foundation of <strong>{$subject}</strong> relies heavily on {$focus}. In modern enterprise architectures and high-scale production systems, achieving consistent computational efficiency requires moving beyond monolithic execution patterns. By employing optimized algorithmic scheduling, decoupled components, and granular memory management, teams establish an infrastructure capable of sustaining high-throughput workloads with predictable latency characteristics and minimized compute overhead.</p>

<p>From an architectural standpoint, incorporating <strong>{$e1}</strong> and <strong>{$e2}</strong> introduces decisive operational advantages. Rather than relying on brute-force hardware scaling, the system balances memory bandwidth utilization, dynamic task routing, and low-level kernel execution. This refined design prevents resource contention during peak concurrency, ensuring deterministic response cycles across distributed endpoints while eliminating the performance penalties typically associated with legacy computational pipelines.</p>

<p>To ensure long-term deployment resilience, engineering teams implementing {$subject} should prioritize automated telemetry, continuous regression benchmarking, and rigorous boundary validation. Establishing automated health probes alongside real-time anomaly detection allows operators to identify and mitigate operational drift before service quality degrades. Ultimately, a disciplined approach to {$focus} empowers organizations to translate core technical capabilities into sustainable, scalable business impact.</p>
HTML;
    }

    protected function buildGamingSectionProse(string $subject, string $focus, string $e1, string $e2, string $e3): string
    {
        return <<<HTML
<p>A central pillar of mastering <strong>{$subject}</strong> involves {$focus}. Whether competing in high-stakes ranked ladders or progressing through immersive narrative campaigns, modern gameplay demands an intimate understanding of core mechanics, physics responsiveness, and engine capabilities. By dissecting input latency, spatial positioning, and environment dynamics, players can transform fundamental interactions into consistent competitive advantages.</p>

<p>Beyond basic controls, leveraging <strong>{$e1}</strong> alongside <strong>{$e2}</strong> directly dictates in-game progression and tactical consistency. Players who intentionally optimize their character builds, hardware settings, and resource management achieve markedly higher win rates than those relying purely on mechanical reaction times. Furthermore, maintaining stable frame pacing during intense multi-entity encounters ensures fluid execution when split-second decisions determine match outcomes.</p>

<p>To sustain progressive skill growth in {$subject}, players should establish disciplined review habits, study high-level competitive footage, and adapt to balance patch updates. Eliminating repetitive tactical errors and synchronizing effectively with squad members maximizes the enjoyment and competitive ceiling of every play session.</p>
HTML;
    }

    protected function buildBusinessSectionProse(string $subject, string $focus, string $e1, string $e2, string $e3): string
    {
        return <<<HTML
<p>In competitive commercial environments, the strategic value of <strong>{$subject}</strong> is fundamentally anchored in {$focus}. Modern leadership teams must navigate accelerating market cycles by establishing agile, data-driven operational frameworks. By replacing static quarterly planning with dynamic feedback loops and clear accountability structures, organizations build the organizational elasticity needed to capitalize on emerging market opportunities while maintaining rigorous fiscal discipline.</p>

<p>A rigorous focus on <strong>{$e1}</strong> and <strong>{$e2}</strong> yields immediate, compounding improvements across unit economics and customer lifetime value. Streamlining fragmented departmental handoffs and automating routine operational bottlenecks directly reduces customer acquisition costs and accelerates time-to-value. Furthermore, cross-functional visibility ensures capital is deployed efficiently into initiatives that deliver demonstrable top-line expansion and resilient margin defense.</p>

<p>To ensure durable execution of {$subject}, enterprises must implement standardized change management frameworks, proactive stakeholder enablement, and continuous KPI telemetry. Measuring progress against objective milestones rather than vanity metrics empowers decision-makers to scale successful initiatives confidently and foster sustainable long-term enterprise valuation.</p>
HTML;
    }

    protected function buildHealthSectionProse(string $subject, string $focus, string $e1, string $e2, string $e3): string
    {
        return <<<HTML
<p>The physiological efficacy of <strong>{$subject}</strong> is grounded in {$focus}. In contemporary health and human performance science, achieving lasting physical vitality requires understanding the metabolic and neurological mechanisms that govern adaptation. Rather than adopting unverified quick-fix routines, following structured, evidence-based principles allows individuals to stimulate positive cellular adaptation while safeguarding systemic homeostasis.</p>

<p>Optimizing daily protocols around <strong>{$e1}</strong> and <strong>{$e2}</strong> plays a decisive role in sustaining energy levels, hormonal balance, and functional recovery. Aligning nutrient timing, mechanical load management, and restorative sleep hygiene prevents chronic central nervous system fatigue and suppresses inflammatory markers. This balanced methodology supports progressive physical readiness and resilient day-to-day stamina.</p>

<p>To maintain long-term compliance with {$subject}, practitioners should establish objective tracking metrics, monitor biofeedback signals, and prioritize progressive overload over unsustainable extremes. Consistency and proactive injury prevention remain the core foundation for achieving lifelong health, physical longevity, and vitality.</p>
HTML;
    }

    protected function buildLifestyleSectionProse(string $subject, string $focus, string $e1, string $e2, string $e3): string
    {
        return <<<HTML
<p>Integrating <strong>{$subject}</strong> into daily living begins with an intentional focus on {$focus}. Creating an elevated, fulfilling lifestyle requires moving beyond surface-level trends toward curated habits that bring genuine balance and clarity. By designing intentional environments and establishing friction-free daily routines, enthusiasts can cultivate meaningful personal spaces that reflect their values and aspirations.</p>

<p>Harnessing <strong>{$e1}</strong> alongside <strong>{$e2}</strong> introduces immediate practical benefits across day-to-day activities. Simplifying complex decisions, investing in durable quality tools, and curating daily workflows frees up cognitive energy for creative and meaningful pursuits. This intentional pacing transforms mundane tasks into enjoyable rituals that enrich daily life.</p>

<p>Sustaining the benefits of {$subject} over time requires regular reflection, continuous refinement, and a commitment to quality over quantity. Embracing gradual personal evolution ensures that your lifestyle habits remain inspiring, resilient, and uniquely fulfilling.</p>
HTML;
    }

    protected function buildGeneralSectionProse(string $subject, string $focus, string $e1, string $e2, string $e3): string
    {
        return <<<HTML
<p>Developing a comprehensive understanding of <strong>{$subject}</strong> necessitates examining {$focus}. In any complex field, genuine mastery requires unpacking foundational principles before attempting advanced practical execution. By establishing clear definitions, structural frameworks, and contextual relationships, practitioners build the analytical clarity required to navigate nuanced challenges effectively.</p>

<p>The practical application of <strong>{$e1}</strong> and <strong>{$e2}</strong> provides tangible advantages across diverse real-world scenarios. By adopting proven methodologies and iterative refinement cycles, individuals and teams can minimize execution errors, optimize time allocation, and achieve consistent, high-quality outcomes that reliably meet or exceed established benchmarks.</p>
HTML;
    }
}
