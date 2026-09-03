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

return [
    /*
    |--------------------------------------------------------------------------
    | OmniRoute Gateway Base URL & Credentials
    |--------------------------------------------------------------------------
    | OmniRoute connects to a local gateway instance (port 20128/v1) or cloud proxy.
    */
    'base_url' => env('OMNIROUTE_BASE_URL', 'http://localhost:20128/v1'),
    'api_key' => env('OMNIROUTE_API_KEY', 'omniroute-default-key'),
    'timeout_seconds' => (int) env('OMNIROUTE_TIMEOUT', 60),
    'connect_timeout_seconds' => (int) env('OMNIROUTE_CONNECT_TIMEOUT', 3),

    /*
    |--------------------------------------------------------------------------
    | Default Model & Auto Routing Mode
    |--------------------------------------------------------------------------
    | When set to 'auto', OmniRoute dynamically selects the optimal model
    | based on token size, latency, intent, and free-tier cascade quotas.
    */
    'default_model' => env('OMNIROUTE_DEFAULT_MODEL', 'auto'),
    'default_embedding_model' => env('OMNIROUTE_EMBEDDING_MODEL', 'nebius/Qwen/Qwen3-Embedding-8B'),

    'auto_modes' => [
        'auto' => 'OmniRoute Auto (Balanced Speed, Cost & Quality)',
        'auto/coding' => 'OmniRoute Auto Coding (Code Generation & Review)',
        'auto/fast' => 'OmniRoute Auto Fast (Lowest Latency & High TPS)',
        'auto/cheap' => 'OmniRoute Auto Cheap (Free-Tier & Cost Optimized)',
        'auto/smart' => 'OmniRoute Auto Smart (Deep Reasoning & Quality-First)',
        'auto/offline' => 'OmniRoute Auto High-Capacity (Maximum Availability)',
    ],

    'combos' => [
        'creative_writing' => 'combo:creative-pro',
        'speed_fast' => 'combo:free-tier-fast',
        'deep_reasoning' => 'combo:reasoning-r1',
        'code_builder' => 'combo:code-builder',
    ],

    /*
    |--------------------------------------------------------------------------
    | Header & Feature Switches
    |--------------------------------------------------------------------------
    */
    'compression' => env('OMNIROUTE_COMPRESSION', 'default'), // off, default, engine:rtk, <combo>
    'cache_enabled' => env('OMNIROUTE_CACHE', true),
    'thinking_budget' => env('OMNIROUTE_THINKING_BUDGET', 'auto'),
    'ssl_verify' => env('OMNIROUTE_SSL_VERIFY', false),
];