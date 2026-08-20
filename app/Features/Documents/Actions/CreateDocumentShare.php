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

namespace App\Features\Documents\Actions;

use App\Features\Documents\Models\Document;
use App\Features\Documents\Models\DocumentShare;
use Illuminate\Support\Facades\Hash;

class CreateDocumentShare
{
    public function execute(Document $document, array $options = []): DocumentShare
    {
        $password = $options['password'] ?? null;
        $expiresInDays = isset($options['expires_in_days']) ? (int) $options['expires_in_days'] : null;

        // Check if active share already exists
        $existing = $document->shares()->where('is_active', true)->first();

        if ($existing) {
            $existing->update([
                'password_hash' => $password ? Hash::make($password) : null,
                'allow_copy' => (bool) ($options['allow_copy'] ?? true),
                'allow_download' => (bool) ($options['allow_download'] ?? true),
                'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
            ]);

            return $existing->fresh();
        }

        return DocumentShare::create([
            'document_id' => $document->id,
            'share_token' => DocumentShare::generateToken(),
            'is_active' => true,
            'password_hash' => $password ? Hash::make($password) : null,
            'allow_copy' => (bool) ($options['allow_copy'] ?? true),
            'allow_download' => (bool) ($options['allow_download'] ?? true),
            'expires_at' => $expiresInDays ? now()->addDays($expiresInDays) : null,
        ]);
    }
}