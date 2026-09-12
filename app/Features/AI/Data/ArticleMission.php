<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - ArticleMission DTO
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

namespace App\Features\AI\Data;

/**
 * Stage 01: Article Mission DTO
 * Defines the core purpose, audience level, and goals of an article before research begins.
 */
class ArticleMission
{
    public function __construct(
        public string $topic,
        public string $articleType = 'comprehensive_guide',
        public string $targetAudience = 'general_readers',
        public string $knowledgeLevel = 'beginner_to_intermediate',
        public string $language = 'simple_english',
        public string $market = 'global',
        public string $primaryGoal = 'educate',
        public string $secondaryGoal = 'drive_organic_traffic',
        public string $technicalDepth = 'medium',
        public string $tone = 'professional_friendly'
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            topic: $data['topic'] ?? 'Untitled Topic',
            articleType: $data['article_type'] ?? 'comprehensive_guide',
            targetAudience: $data['target_audience'] ?? 'general_readers',
            knowledgeLevel: $data['knowledge_level'] ?? 'beginner_to_intermediate',
            language: $data['language'] ?? 'simple_english',
            market: $data['market'] ?? 'global',
            primaryGoal: $data['primary_goal'] ?? 'educate',
            secondaryGoal: $data['secondary_goal'] ?? 'drive_organic_traffic',
            technicalDepth: $data['technical_depth'] ?? 'medium',
            tone: $data['tone'] ?? 'professional_friendly'
        );
    }

    public function toArray(): array
    {
        return [
            'topic' => $this->topic,
            'article_type' => $this->articleType,
            'target_audience' => $this->targetAudience,
            'knowledge_level' => $this->knowledgeLevel,
            'language' => $this->language,
            'market' => $this->market,
            'primary_goal' => $this->primaryGoal,
            'secondary_goal' => $this->secondaryGoal,
            'technical_depth' => $this->technicalDepth,
            'tone' => $this->tone,
        ];
    }
}
