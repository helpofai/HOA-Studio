<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Admin System Info & Docs Page
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

namespace App\Features\Admin\Livewire;

use App\Features\Admin\Services\CoreUpdateService;
use App\Features\Admin\Services\SystemInfoService;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('layouts.admin')]
#[Title('System Info & Documentation — HelpOfAi Studio')]
class AdminSystemInfoPage extends Component
{
    public array $diagnostics = [];
    public array $docs = [];
    public array $versionMeta = [];
    public string $activeTab = 'server'; // 'server', 'readme', 'changelog', 'documents', 'others'
    public string $otherDocKey = 'production';

    public function mount(SystemInfoService $infoService, CoreUpdateService $updateService)
    {
        $this->diagnostics = $infoService->getSystemDiagnostics();
        $this->docs = $infoService->getDocumentationFiles();
        $this->versionMeta = $updateService->getVersionMetadata();
    }

    public function render()
    {
        return view('admin.system-info');
    }
}
