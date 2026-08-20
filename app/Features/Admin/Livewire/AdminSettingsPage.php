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

namespace App\Features\Admin\Livewire;

use App\Features\Admin\Actions\SaveSystemSettings;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('System Settings & Gateway Config — HelpOfAi Studio')]
class AdminSettingsPage extends Component
{
    public string $site_name = 'HelpOfAi Studio';
    public string $gateway_url = 'http://127.0.0.1:20128';
    public string $gateway_api_key = 'omniroute-default-key';
    public string $default_model = 'deepseek/deepseek-chat';
    public string $compression_mode = 'default';
    public int $starter_quota = 15000;
    public int $pro_quota = 100000;
    public int $enterprise_quota = 500000;
    public bool $allow_registration = true;

    public function mount()
    {
        $settings = DB::table('settings')->pluck('value', 'key')->toArray();

        $this->site_name = $settings['site_name'] ?? config('app.name', 'HelpOfAi Studio');
        $this->gateway_url = $settings['gateway_url'] ?? config('omniroute.base_url', 'http://127.0.0.1:20128');
        $this->gateway_api_key = $settings['gateway_api_key'] ?? config('omniroute.api_key', 'omniroute-default-key');
        $this->default_model = $settings['default_model'] ?? config('omniroute.default_model', 'deepseek/deepseek-chat');
        $this->compression_mode = $settings['compression_mode'] ?? config('omniroute.compression', 'default');
        $this->starter_quota = (int) ($settings['starter_quota'] ?? 15000);
        $this->pro_quota = (int) ($settings['pro_quota'] ?? 100000);
        $this->enterprise_quota = (int) ($settings['enterprise_quota'] ?? 500000);
        $this->allow_registration = (bool) ($settings['allow_registration'] ?? true);
    }

    public function saveSettings(SaveSystemSettings $action)
    {
        $action->execute([
            'site_name' => $this->site_name,
            'gateway_url' => $this->gateway_url,
            'gateway_api_key' => $this->gateway_api_key,
            'default_model' => $this->default_model,
            'compression_mode' => $this->compression_mode,
            'starter_quota' => $this->starter_quota,
            'pro_quota' => $this->pro_quota,
            'enterprise_quota' => $this->enterprise_quota,
            'allow_registration' => $this->allow_registration,
        ]);

        session()->flash('status', 'System settings updated and synchronized successfully.');
    }

    public function render()
    {
        return view('admin.settings');
    }
}