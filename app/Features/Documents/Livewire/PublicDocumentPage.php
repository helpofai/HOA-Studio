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

namespace App\Features\Documents\Livewire;

use App\Features\Documents\Models\DocumentShare;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Shared Document — HelpOfAi Studio')]
class PublicDocumentPage extends Component
{
    public string $token;
    public string $passwordInput = '';
    public bool $isUnlocked = false;
    public ?string $errorMessage = null;

    public function mount(string $token)
    {
        $this->token = $token;

        $share = DocumentShare::where('share_token', $token)
            ->where('is_active', true)
            ->first();

        if (!$share || $share->isExpired()) {
            abort(404, 'Shared document not found or expired.');
        }

        if (!$share->isPasswordProtected()) {
            $this->isUnlocked = true;
            $share->incrementViews();
        }
    }

    public function unlock()
    {
        $this->errorMessage = null;

        $share = DocumentShare::where('share_token', $this->token)
            ->where('is_active', true)
            ->firstOrFail();

        if ($share->verifyPassword($this->passwordInput)) {
            $this->isUnlocked = true;
            $share->incrementViews();
        } else {
            $this->errorMessage = 'Incorrect password. Please try again.';
        }
    }

    public function render()
    {
        $share = DocumentShare::with(['document.content', 'document.user'])
            ->where('share_token', $this->token)
            ->where('is_active', true)
            ->firstOrFail();

        return view('documents.public-share', [
            'share' => $share,
            'document' => $share->document,
        ]);
    }
}