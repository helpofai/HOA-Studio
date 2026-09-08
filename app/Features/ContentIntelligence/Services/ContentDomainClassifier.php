<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Domain Classifier
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

class ContentDomainClassifier
{
    public const DOMAIN_GAMING = 'gaming';
    public const DOMAIN_AI_TECH = 'ai_tech';
    public const DOMAIN_SOFTWARE = 'software';
    public const DOMAIN_BUSINESS = 'business';
    public const DOMAIN_HEALTH = 'health';
    public const DOMAIN_GENERAL = 'general';

    /**
     * Classify topic and thesis into a high-level content domain.
     */
    public static function classify(string $topic, string $thesis = ''): string
    {
        $text = strtolower($topic . ' ' . $thesis);

        // Gaming keywords
        if (preg_match('/\b(game|games|gaming|battle royale|free fire|pubg|call of duty|cod|fortnite|apex legends|omega legends|roblox|minecraft|valorant|deadlock|fps|rpg|mmo|esports|gameplay|playstation|xbox|nintendo|steam|multiplayer|sandbox|shooter)\b/i', $text)) {
            return self::DOMAIN_GAMING;
        }

        // AI & Machine Learning keywords
        if (preg_match('/\b(ai|llm|gpt|gemini|claude|chatgpt|openai|machine learning|deep learning|neural|transformer|multimodal|diffusion|token|embeddings|rag|prompt engineering|langchain)\b/i', $text)) {
            return self::DOMAIN_AI_TECH;
        }

        // Software Engineering & Cloud
        if (preg_match('/\b(laravel|php|javascript|python|react|vue|angular|docker|kubernetes|aws|api|rest|database|sql|nosql|backend|frontend|microservice|devops|git|ci\/cd|linux)\b/i', $text)) {
            return self::DOMAIN_SOFTWARE;
        }

        // Business, Marketing & Finance
        if (preg_match('/\b(marketing|seo|ecommerce|crypto|bitcoin|stock|finance|revenue|roi|startup|saas|investment|banking|affiliate|sales funnel|b2b)\b/i', $text)) {
            return self::DOMAIN_BUSINESS;
        }

        // Health, Fitness & Lifestyle
        if (preg_match('/\b(health|fitness|workout|diet|nutrition|wellness|weight loss|gym|mental health|skincare|travel|recipe|cooking)\b/i', $text)) {
            return self::DOMAIN_HEALTH;
        }

        return self::DOMAIN_GENERAL;
    }

    /**
     * Extract specific named entities, products, tools, or games explicitly mentioned in the user's input.
     *
     * @return array<string>
     */
    public static function extractEntitiesFromThesis(string $text, string $topic = ''): array
    {
        $entities = [];
        $clean = self::cleanRawText($text);

        // 1. Look for patterns like "include A, B, C, and D" or "such as A, B, C"
        if (preg_match('/(?:include|including|such as|like|compare|between|among|features?)\s+([^.\n]{5,200})/i', $clean, $matches)) {
            $rawList = $matches[1];
            // Split by commas, "and", "or", "&"
            $parts = preg_split('/,\s*|\s+(?:and|or|&)\s+/i', $rawList);
            foreach ($parts as $part) {
                $item = trim(preg_replace('/\b(offering|providing|with|mix of|popular|competitive|multiplayer|experiences|games?|titles?)\b.*/i', '', $part));
                $item = trim($item, " \t\n\r\0\x0B,.-:;");
                if (strlen($item) >= 2 && strlen($item) <= 40 && ! in_array(strtolower($item), ['a', 'an', 'the', 'top', 'best', 'more', 'all'])) {
                    $entities[] = ucwords(strtolower($item));
                }
            }
        }

        // 2. Look for capitalized terms that are followed by description paragraphs (e.g. "Minecraft A sandbox...", "Roblox A platform...")
        if (preg_match_all('/(?:^|\n|\.\s+)([A-Z][a-zA-Z0-9\s\+]{2,25})\s+(?:is a|is an|a |an |developed by|featuring|offers)/i', $clean, $pm)) {
            foreach ($pm[1] as $candidate) {
                $candidate = trim($candidate);
                if (strlen($candidate) >= 3 && strlen($candidate) <= 30 && ! in_array(strtolower($candidate), ['popular', 'competitive', 'multiplayer', 'enterprise', 'intermediate', 'target', 'primary', 'objective'])) {
                    $entities[] = ucwords(strtolower($candidate));
                }
            }
        }

        // 3. Known domain entities lookup (longer variants listed first for preferential matching)
        $knownEntities = [
            'PUBG Mobile', 'Call of Duty: Mobile', 'Omega Legends', 'Free Fire MAX', 'Apex Legends',
            'Counter-Strike 2', 'Overwatch 2', 'League of Legends', 'World of Warcraft', 'Genshin Impact',
            'Minecraft', 'Roblox', 'Fortnite', 'Valorant', 'Deadlock', 'PUBG', 'Call of Duty',
            'Dota 2', 'Rocket League',
            'GPT-4o', 'Claude 3.5 Sonnet', 'DeepSeek V3', 'DeepSeek R1', 'Gemini 1.5 Pro', 'Llama 3.3', 'Mistral Large',
            'Laravel', 'Vue.js', 'React', 'Tailwind CSS', 'Docker', 'Kubernetes', 'PostgreSQL', 'Redis',
        ];

        foreach ($knownEntities as $known) {
            if (stripos($clean, $known) !== false || stripos($topic, $known) !== false) {
                $entities[] = $known;
            }
        }

        // Deduplicate: remove entities that are substrings of longer collected entities
        $entities = array_values(array_filter($entities, function ($e) use ($entities) {
            foreach ($entities as $other) {
                if ($e !== $other && stripos($other, $e) !== false) {
                    return false;
                }
            }
            return true;
        }));

        $unique = array_values(array_unique(array_filter($entities, fn($e) => strlen(trim($e)) >= 2)));

        if (empty($unique) && ! empty($topic)) {
            $unique[] = ucwords(trim($topic));
        }

        return $unique;
    }

    /**
     * Clean messy scraped text, remove raw URLs, citations, and deduplicate words.
     */
    public static function cleanRawText(string $text): string
    {
        // 1. Remove URLs (http://, https://, domain.com)
        $text = preg_replace('/\bhttps?:\/\/[^\s]+/i', '', $text);
        $text = preg_replace('/\b[a-zA-Z0-9\-\.]+\.(com|org|net|io|co|in|apk|apkpure\.com|moregameslike\.com|50bestgames\.com)\b/i', '', $text);

        // 2. Remove citation brackets [1], [APKPure.com], etc.
        $text = preg_replace('/\[[^\]]*\]/', '', $text);
        $text = preg_replace('/\([^\)]*(?:source|http|\.com)[^\)]*\)/i', '', $text);

        // 3. Remove repeated identical words back-to-back (e.g. "50bestgames.com 50bestgames.com")
        $text = preg_replace('/\b(\w+)\s+\1\b/i', '$1', $text);

        // 4. Remove leading dashes or metadata markers
        $text = preg_replace('/^[-–—\s]+/u', '', $text);

        // 5. Normalize spaces
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
