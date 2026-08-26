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

namespace App\Features\Auth\Livewire;

use App\Features\Auth\Actions\LoginUser;
use App\Features\Auth\Services\AuthSecurityService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Sign In — HelpOfAi Studio')]
class LoginPage extends Component
{
    public string $email = '';
    public string $password = '';
    public bool $remember = false;

    // Security & Anti-Bot Properties
    public string $honeypot = ''; // Hidden anti-bot trap field
    public ?int $formLoadedAt = null;
    public ?string $turnstileToken = null;

    public function mount()
    {
        $this->formLoadedAt = time();
    }

    protected array $rules = [
        'email' => 'required|email|max:255',
        'password' => 'required|string|max:255',
    ];

    public function login(LoginUser $action, AuthSecurityService $security)
    {
        // 0. Check if client IP is blocked
        $security->checkIpBlock();

        // 1. Verify Anti-Bot Honeypot and timing speed
        $security->verifyHoneypot($this->honeypot, $this->formLoadedAt);

        // 2. Verify Cloudflare Turnstile token if enabled
        $security->verifyTurnstile($this->turnstileToken);

        $this->validate();

        $action->execute($this->email, $this->password, $this->remember);

        return redirect()->intended('/dashboard');
    }

    public function render()
    {
        $siteKey = \App\Features\Auth\Services\AuthSecurityService::getTurnstileSiteKey();
        $isEnabled = \App\Features\Auth\Services\AuthSecurityService::isTurnstileEnabled();

        return view('auth.login', [
            'turnstileSiteKey' => $isEnabled ? $siteKey : '',
        ]);
    }
}