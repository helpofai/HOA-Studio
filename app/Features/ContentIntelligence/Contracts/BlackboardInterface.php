<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Blackboard Interface
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

namespace App\Features\ContentIntelligence\Contracts;

interface BlackboardInterface
{
    /**
     * Get a state value by key from the blackboard.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Set a state value by key on the blackboard.
     */
    public function set(string $key, mixed $value): void;

    /**
     * Check if a key exists on the blackboard.
     */
    public function has(string $key): bool;

    /**
     * Append an item to an array list on the blackboard.
     */
    public function push(string $key, mixed $value): void;

    /**
     * Export full blackboard snapshot as an associative array.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
