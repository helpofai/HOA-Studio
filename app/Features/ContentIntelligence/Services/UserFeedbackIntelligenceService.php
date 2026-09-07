<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - User Feedback Intelligence Service
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

use App\Features\ContentIntelligence\Models\UserStylePreference;
use Illuminate\Support\Collection;

class UserFeedbackIntelligenceService
{
    /**
     * Analyze a manual edit performed by the user on AI-generated prose.
     * Extracts authorial patterns (sentence brevity, active voice, fluff removal, bulletization).
     */
    public function analyzeDiffAndRecordPreference(int $userId, string $originalText, string $editedText): ?UserStylePreference
    {
        $origWords = str_word_count($originalText);
        $editWords = str_word_count($editedText);

        if ($origWords < 10 || $editWords < 5) {
            return null;
        }

        // 1. Detect Sentence Brevity Preference (Shortening by >= 25%)
        if ($origWords > 25 && ($editWords / $origWords) <= 0.75) {
            return $this->recordPreferenceEvidence(
                userId: $userId,
                key: 'prefer_concise_sentences',
                description: 'Author prefers concise, punchy sentences without unnecessary rhetorical qualifiers.',
                metadata: [
                    'original_word_count' => $origWords,
                    'edited_word_count' => $editWords,
                    'reduction_ratio' => round($editWords / $origWords, 2),
                ]
            );
        }

        // 2. Detect Fluff Elimination
        $fluffTriggers = ['in today\'s fast-paced world', 'it is important to remember', 'delve into', 'tapestry of', 'moreover, furthermore'];
        $hadFluff = false;
        foreach ($fluffTriggers as $fluff) {
            if (stripos($originalText, $fluff) !== false && stripos($editedText, $fluff) === false) {
                $hadFluff = true;
                break;
            }
        }

        if ($hadFluff) {
            return $this->recordPreferenceEvidence(
                userId: $userId,
                key: 'eliminate_fluff_phrases',
                description: 'Author consistently strips AI cliché transitions and corporate buzzwords.',
                metadata: ['fluff_detected' => true]
            );
        }

        // 3. Detect Bulletization Preference
        $origBullets = substr_count($originalText, "\n- ") + substr_count($originalText, "\n* ");
        $editBullets = substr_count($editedText, "\n- ") + substr_count($editedText, "\n* ");

        if ($origBullets === 0 && $editBullets >= 2) {
            return $this->recordPreferenceEvidence(
                userId: $userId,
                key: 'prefer_bulleted_breakdowns',
                description: 'Author prefers breaking multi-step explanations or comparisons into unordered bullet points.',
                metadata: ['bullets_added' => $editBullets]
            );
        }

        // 4. Default Prose Polishing
        return $this->recordPreferenceEvidence(
            userId: $userId,
            key: 'tailored_lexicon_preference',
            description: 'Author actively refines technical syntax and domain-specific vocabulary.',
            metadata: ['diff_ratio' => round(abs($editWords - $origWords) / max(1, $origWords), 2)]
        );
    }

    /**
     * Record or increment an observed user writing style preference.
     *
     * @param  array<string, mixed>  $metadata
     */
    public function recordPreferenceEvidence(int $userId, string $key, string $description, array $metadata = []): UserStylePreference
    {
        $pref = UserStylePreference::where('user_id', $userId)
            ->where('preference_key', $key)
            ->first();

        if (! $pref) {
            return UserStylePreference::create([
                'user_id' => $userId,
                'preference_key' => $key,
                'observed_diff_count' => 1,
                'confidence' => 0.45,
                'rule_description' => $description,
                'is_active' => true,
                'metadata' => $metadata,
            ]);
        }

        $newCount = $pref->observed_diff_count + 1;
        $newConfidence = min(0.98, $pref->confidence + 0.09);

        $pref->update([
            'observed_diff_count' => $newCount,
            'confidence' => $newConfidence,
            'rule_description' => $description,
            'metadata' => array_merge($pref->metadata ?? [], $metadata, ['last_updated' => now()->toIso8601String()]),
        ]);

        return $pref->fresh();
    }

    /**
     * Retrieve all active preferences with strong confidence (>= 0.60) to guide prompt drafting.
     *
     * @return Collection<int, UserStylePreference>
     */
    public function getActivePreferences(int $userId): Collection
    {
        return UserStylePreference::where('user_id', $userId)
            ->where('is_active', true)
            ->where('confidence', '>=', 0.60)
            ->orderByDesc('confidence')
            ->get();
    }

    /**
     * Enable or disable a learned style preference.
     */
    public function togglePreference(int $preferenceId, bool $isActive): UserStylePreference
    {
        $pref = UserStylePreference::findOrFail($preferenceId);
        $pref->update(['is_active' => $isActive]);

        return $pref;
    }
}
