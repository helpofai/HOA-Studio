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

class OmniRouteUrlResolver
{
    /**
     * Resolve root base URL (without /v1) and OpenAI base URL (with /v1)
     * Supporting http://localhost:20128, http://127.0.0.1:20128, with or without /v1 and trailing slashes.
     */
    public static function resolve(?string $rawUrl = null): array
    {
        $input = trim($rawUrl ?: config('omniroute.base_url', 'http://localhost:20128/v1'));
        
        if (empty($input)) {
            $input = 'http://localhost:20128/v1';
        }

        // Clean trailing slashes
        $clean = rtrim($input, '/');

        // Check if ends with /v1
        if (preg_match('#/v1$#i', $clean)) {
            $openAiBase = $clean;
            $rootBase = preg_replace('#/v1$#i', '', $clean);
        } else {
            $rootBase = $clean;
            $openAiBase = "{$clean}/v1";
        }

        // Generate IPv4-safe URL for Windows cURL (translates localhost -> 127.0.0.1)
        $curlOpenAiBase = str_replace('://localhost', '://127.0.0.1', $openAiBase);
        $curlRootBase = str_replace('://localhost', '://127.0.0.1', $rootBase);

        return [
            'display_url' => $openAiBase,
            'root_url' => $rootBase,
            'openai_base' => $openAiBase,
            'curl_openai_base' => $curlOpenAiBase,
            'curl_root_base' => $curlRootBase,
            'chat_completions_endpoint' => "{$curlOpenAiBase}/chat/completions",
            'models_endpoint' => "{$curlOpenAiBase}/models",
            'combos_endpoint' => "{$curlRootBase}/api/combos",
            'health_endpoint' => "{$curlRootBase}/api/health",
        ];
    }
}