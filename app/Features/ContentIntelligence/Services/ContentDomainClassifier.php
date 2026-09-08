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
        if (preg_match('/\b(game|games|gaming|battle royale|free fire|pubg|call of duty|cod|fortnite|apex legends|omega legends|roblox|minecraft|fps|rpg|mmo|esports|gameplay|playstation|xbox|nintendo|steam|apkpure|multiplayer|garena|respawn|clash royale|genshin)\b/i', $text)) {
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
     * Clean messy scraped text, remove raw URLs, citations, and deduplicate words.
     */
    public static function cleanRawText(string $text): string
    {
        // 1. Remove URLs (http://, https://, domain.com)
        $text = preg_replace('/\bhttps?:\/\/[^\s]+/i', '', $text);
        $text = preg_replace('/\b[a-zA-Z0-9\-\.]+\.(com|org|net|io|co|in|apk|apkpure\.com|moregameslike\.com)\b/i', '', $text);

        // 2. Remove citation brackets [1], [APKPure.com], etc.
        $text = preg_replace('/\[[^\]]*\]/', '', $text);
        $text = preg_replace('/\([^\)]*(?:source|http|\.com)[^\)]*\)/i', '', $text);

        // 3. Remove repeated identical words back-to-back (e.g. "APKPure.com APKPure.com")
        $text = preg_replace('/\b(\w+)\s+\1\b/i', '$1', $text);

        // 4. Normalize spaces
        $text = preg_replace('/\s+/', ' ', $text);

        return trim($text);
    }
}
