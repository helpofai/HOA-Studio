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

use App\Features\Auth\Actions\RegisterUser;
use App\Features\Auth\Services\AuthSecurityService;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.app')]
#[Title('Create Account — HelpOfAi Studio')]
class RegisterPage extends Component
{
    public string $name = '';
    public string $email = '';
    public string $password = '';
    public string $password_confirmation = '';
    public bool $agree = false;

    // Security & Anti-Bot Properties
    public string $honeypot = ''; // Hidden anti-bot trap field
    public ?int $formLoadedAt = null;
    public ?string $turnstileToken = null;

    public function mount()
    {
        $this->formLoadedAt = time();
    }

    protected function rules(): array
    {
        $passwordRule = Password::min(8)
            ->letters()
            ->mixedCase()
            ->numbers()
            ->symbols();

        // Only enforce HaveIBeenPwned network lookup if not in local unit tests
        if (!app()->runningUnitTests()) {
            $passwordRule->uncompromised(3);
        }

        return [
            'name' => ['required', 'string', 'min:2', 'max:70'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
            ],
            'agree' => ['accepted'],
        ];
    }

    protected array $messages = [
        'name.regex' => 'Name may only contain letters, spaces, and hyphens.',
        'email.email' => 'Please provide a valid, deliverable email address.',
    ];

    public function register(RegisterUser $action, AuthSecurityService $security)
    {
        // 0. Check if client IP is blocked
        $security->checkIpBlock();

        // 1. Verify Anti-Bot Honeypot and speed
        $security->verifyHoneypot($this->honeypot, $this->formLoadedAt);

        // 2. Verify Cloudflare Turnstile token if enabled
        $security->verifyTurnstile($this->turnstileToken);

        $this->validate();

        $action->execute([
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
        ]);

        session()->flash('status', 'Welcome to HelpOfAi Studio! Your account is ready.');

        return redirect()->to('/dashboard');
    }

    public function render()
    {
        $siteKey = \App\Features\Auth\Services\AuthSecurityService::getTurnstileSiteKey();
        $isEnabled = \App\Features\Auth\Services\AuthSecurityService::isTurnstileEnabled();

        return view('auth.register', [
            'turnstileSiteKey' => $isEnabled ? $siteKey : '',
        ]);
    }
}