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

use App\Features\Admin\Livewire\AdminDashboardPage;
use App\Features\Admin\Livewire\AdminSettingsPage;
use App\Features\Admin\Livewire\AdminUsageLogsPage;
use App\Features\Admin\Livewire\AdminUsersPage;
use App\Features\AI\Http\Controllers\AiStreamController;
use App\Features\AI\Http\Controllers\AiProviderController;
use App\Features\Auth\Livewire\ForgotPasswordPage;
use App\Features\Auth\Livewire\LoginPage;
use App\Features\Auth\Livewire\ProfilePage;
use App\Features\Auth\Livewire\RegisterPage;
use App\Features\Dashboard\Livewire\DashboardPage;
use App\Features\Documents\Livewire\DocumentEditor;
use App\Features\Documents\Livewire\DocumentsPage;
use App\Features\Projects\Livewire\ProjectsPage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Public Landing Page
Route::get('/', function () {
    return view('welcome');
})->name('home');

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', LoginPage::class)->name('login');
    Route::get('/register', RegisterPage::class)->name('register');
    Route::get('/forgot-password', ForgotPasswordPage::class)->name('password.request');
});

use App\Features\BrandVoice\Livewire\BrandVoicePage;
use App\Features\Documents\Http\Controllers\ExportDocumentController;
use App\Features\Documents\Livewire\PublicDocumentPage;
use App\Features\KnowledgeBase\Livewire\KnowledgeBasePage;
use App\Features\Templates\Livewire\TemplatesHubPage;
use App\Features\Usage\Livewire\UserUsagePage;

// Authenticated User Workspace Routes (/dashboard/*)
Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', DashboardPage::class)->name('dashboard');
    Route::get('/editor', \App\Features\Documents\Http\Controllers\OpenEditorController::class)->name('editor');
    Route::get('/documents', DocumentsPage::class)->name('documents.index');
    Route::get('/documents/{id}', DocumentEditor::class)->name('documents.editor');
    Route::get('/templates', TemplatesHubPage::class)->name('templates.index');
    Route::get('/brand-voices', BrandVoicePage::class)->name('brand-voices.index');
    Route::get('/knowledge-base', KnowledgeBasePage::class)->name('knowledge-base.index');
    Route::get('/ai-models', \App\Features\AI\Livewire\UserAiModelsPage::class)->name('ai-models.index');
    Route::get('/ai-models/omniroute', \App\Features\AI\Livewire\UserOmniRouteSetupPage::class)->name('ai-models.omniroute');
    Route::get('/ai-settings/omniroute', \App\Features\AI\Livewire\UserOmniRouteSetupPage::class)->name('ai-settings.omniroute');
    Route::get('/usage', UserUsagePage::class)->name('usage.index');
    Route::get('/projects', ProjectsPage::class)->name('projects.index');
    Route::get('/profile', ProfilePage::class)->name('profile');
    Route::get('/documents/{id}/export/{format}', [ExportDocumentController::class, 'export'])->name('documents.export');
    Route::get('/documents/{id}/print-pdf', [ExportDocumentController::class, 'printPdf'])->name('documents.print-pdf');

    // OmniRoute AI API
    Route::post('/api/ai/transform', [AiStreamController::class, 'transform'])->name('ai.transform');
    Route::post('/api/ai/stream-transform', [AiStreamController::class, 'streamTransform'])->name('ai.stream-transform');
    Route::post('/api/ai/stream', [AiStreamController::class, 'stream'])->name('ai.stream');
    Route::get('/api/ai/providers/models', [AiProviderController::class, 'getModels'])->name('ai.providers.models');
});

// Public Document Sharing Routes
Route::get('/share/{token}', PublicDocumentPage::class)->name('public.share');
Route::get('/share/{token}/export/{format}', [ExportDocumentController::class, 'exportPublic'])->name('public.export');

// Common Authenticated Actions
Route::middleware('auth')->group(function () {
    Route::post('/logout', function () {
        Auth::logout();
        if (request()->hasSession()) {
            request()->session()->invalidate();
            request()->session()->regenerateToken();
        }

        return redirect('/');
    })->name('logout');
});

use App\Features\Admin\Livewire\AdminAiSettingsPage;
use App\Features\Admin\Livewire\AdminOmniRouteSetupPage;
use App\Features\Admin\Livewire\AdminSystemInfoPage;
use App\Features\Admin\Livewire\AdminUpdatesPage;

// Admin Control Center Routes (Role: Admin) (/admin/*)
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardPage::class)->name('dashboard');
    Route::get('/users', AdminUsersPage::class)->name('users');
    Route::get('/ai-settings', AdminAiSettingsPage::class)->name('ai-settings.index');
    Route::get('/ai-settings/omniroute', AdminOmniRouteSetupPage::class)->name('ai-settings.omniroute');
    Route::get('/usage', AdminUsageLogsPage::class)->name('usage');
    Route::get('/settings', AdminSettingsPage::class)->name('settings');
    Route::get('/system-info', AdminSystemInfoPage::class)->name('system-info');
    Route::get('/updates', AdminUpdatesPage::class)->name('updates');
    Route::get('/api/terminal-logs', \App\Features\Admin\Controllers\AdminTerminalLogsController::class)->name('api.terminal-logs');
});

