<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Content Mission Model
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

namespace App\Features\ContentIntelligence\Models;

use App\Features\BrandVoice\Models\BrandProfile;
use App\Features\ContentIntelligence\DTOs\ContentMissionDTO;
use App\Features\ContentIntelligence\Enums\ResearchBudgetTier;
use App\Features\ContentIntelligence\Enums\RiskLevel;
use App\Features\Projects\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class ContentMission extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'content_missions';

    protected $fillable = [
        'user_id',
        'project_id',
        'topic',
        'primary_objective',
        'secondary_objectives',
        'target_audience',
        'market_geo',
        'language',
        'content_type',
        'business_goal',
        'search_goal',
        'brand_profile_id',
        'freshness_requirement',
        'trust_requirement',
        'evidence_requirement',
        'target_word_count_min',
        'target_word_count_max',
        'risk_level',
        'research_budget_tier',
        'success_criteria',
        'custom_constraints',
        'status',
    ];

    protected $casts = [
        'secondary_objectives' => 'array',
        'target_audience' => 'array',
        'success_criteria' => 'array',
        'custom_constraints' => 'array',
        'target_word_count_min' => 'integer',
        'target_word_count_max' => 'integer',
        'risk_level' => RiskLevel::class,
        'research_budget_tier' => ResearchBudgetTier::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function brandProfile(): BelongsTo
    {
        return $this->belongsTo(BrandProfile::class);
    }

    public function workflowRuns(): HasMany
    {
        return $this->hasMany(WorkflowRun::class, 'mission_id');
    }

    /**
     * Hydrate a strongly typed DTO from the model instance.
     */
    public function toDTO(): ContentMissionDTO
    {
        return new ContentMissionDTO(
            topic: $this->topic,
            primaryObjective: $this->primary_objective,
            secondaryObjectives: $this->secondary_objectives ?? [],
            targetAudience: $this->target_audience ?? [],
            marketGeo: $this->market_geo,
            language: $this->language,
            contentType: $this->content_type,
            businessGoal: $this->business_goal,
            searchGoal: $this->search_goal,
            brandProfileId: $this->brand_profile_id,
            freshnessRequirement: $this->freshness_requirement,
            trustRequirement: $this->trust_requirement,
            evidenceRequirement: $this->evidence_requirement,
            targetWordCountRange: [
                'min' => $this->target_word_count_min,
                'max' => $this->target_word_count_max,
            ],
            riskLevel: $this->risk_level instanceof RiskLevel ? $this->risk_level : RiskLevel::from($this->risk_level),
            researchBudgetTier: $this->research_budget_tier instanceof ResearchBudgetTier ? $this->research_budget_tier : ResearchBudgetTier::from($this->research_budget_tier),
            successCriteria: $this->success_criteria ?? [],
            customConstraints: $this->custom_constraints ?? []
        );
    }
}
