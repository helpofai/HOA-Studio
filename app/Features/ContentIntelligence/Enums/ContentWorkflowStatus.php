<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Workflow Status Enum
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

namespace App\Features\ContentIntelligence\Enums;

enum ContentWorkflowStatus: string
{
    case DRAFT = 'draft';
    case QUEUED = 'queued';
    case RUNNING = 'running';
    case PAUSED = 'paused';
    case REQUIRES_HUMAN_REVIEW = 'requires_human_review';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    public function isTerminal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::QUEUED, self::RUNNING]);
    }
}
