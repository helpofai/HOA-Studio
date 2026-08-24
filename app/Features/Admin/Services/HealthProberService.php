<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Core Update & Health Prober Service
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

namespace App\Features\Admin\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class HealthProberService
{
    /**
     * Run all synthetic system diagnostics after an update.
     *
     * @return array{passed: bool, checks: array<string, array{name: string, status: string, message: string, duration_ms: float}>}
     */
    public function probeSystem(): array
    {
        $checks = [];
        $allPassed = true;

        // 1. Database Connection & Schema Integrity Check
        $dbStart = microtime(true);
        try {
            DB::connection()->getPdo();
            $hasUsersTable = Schema::hasTable('users');
            $hasDocsTable = Schema::hasTable('documents');
            $hasProvidersTable = Schema::hasTable('ai_providers');

            if ($hasUsersTable && $hasDocsTable && $hasProvidersTable) {
                $checks['database'] = [
                    'name' => 'Database Schema & Tables Integrity',
                    'status' => 'pass',
                    'message' => 'Connected successfully and verified core database tables.',
                    'duration_ms' => round((microtime(true) - $dbStart) * 1000, 2),
                ];
            } else {
                $allPassed = false;
                $checks['database'] = [
                    'name' => 'Database Schema & Tables Integrity',
                    'status' => 'fail',
                    'message' => 'Connected to database but one or more critical tables are missing.',
                    'duration_ms' => round((microtime(true) - $dbStart) * 1000, 2),
                ];
            }
        } catch (\Throwable $e) {
            $allPassed = false;
            $checks['database'] = [
                'name' => 'Database Schema & Tables Integrity',
                'status' => 'fail',
                'message' => 'Database connection failed: ' . $e->getMessage(),
                'duration_ms' => round((microtime(true) - $dbStart) * 1000, 2),
            ];
        }

        // 2. Essential User Authentication & Data Access Check
        $userStart = microtime(true);
        try {
            $userCount = User::count();
            $checks['auth_model'] = [
                'name' => 'User Entity Query Verification',
                'status' => 'pass',
                'message' => "Successfully queried User model ({$userCount} users registered).",
                'duration_ms' => round((microtime(true) - $userStart) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            $allPassed = false;
            $checks['auth_model'] = [
                'name' => 'User Entity Query Verification',
                'status' => 'fail',
                'message' => 'Querying User model failed: ' . $e->getMessage(),
                'duration_ms' => round((microtime(true) - $userStart) * 1000, 2),
            ];
        }

        // 3. Frontend Build Manifest Verification
        $assetStart = microtime(true);
        $manifestPath = public_path('build/manifest.json');
        if (file_exists($manifestPath) && is_readable($manifestPath)) {
            $manifestData = json_decode(file_get_contents($manifestPath), true);
            if (is_array($manifestData) && !empty($manifestData)) {
                $checks['assets'] = [
                    'name' => 'Vite Production Manifest Integrity',
                    'status' => 'pass',
                    'message' => 'Vite manifest is present, readable, and contains compiled entrypoints.',
                    'duration_ms' => round((microtime(true) - $assetStart) * 1000, 2),
                ];
            } else {
                $checks['assets'] = [
                    'name' => 'Vite Production Manifest Integrity',
                    'status' => 'warning',
                    'message' => 'Vite manifest file exists but appears empty.',
                    'duration_ms' => round((microtime(true) - $assetStart) * 1000, 2),
                ];
            }
        } else {
            $checks['assets'] = [
                'name' => 'Vite Production Manifest Integrity',
                'status' => 'warning',
                'message' => 'public/build/manifest.json is not found. Assets may require compiling.',
                'duration_ms' => round((microtime(true) - $assetStart) * 1000, 2),
            ];
        }

        // 4. File Storage & Cache Directory Write Permissions
        $fsStart = microtime(true);
        try {
            $testFile = storage_path('framework/cache/hoa_health_probe.tmp');
            file_put_contents($testFile, 'HEALTH_PROBE_OK');
            $readContent = file_get_contents($testFile);
            @unlink($testFile);

            if ($readContent === 'HEALTH_PROBE_OK') {
                $checks['storage_write'] = [
                    'name' => 'Storage Directory Write Permissions',
                    'status' => 'pass',
                    'message' => 'Read/write permissions in storage/framework directories verified.',
                    'duration_ms' => round((microtime(true) - $fsStart) * 1000, 2),
                ];
            } else {
                $allPassed = false;
                $checks['storage_write'] = [
                    'name' => 'Storage Directory Write Permissions',
                    'status' => 'fail',
                    'message' => 'Write test in storage directory produced mismatched content.',
                    'duration_ms' => round((microtime(true) - $fsStart) * 1000, 2),
                ];
            }
        } catch (\Throwable $e) {
            $allPassed = false;
            $checks['storage_write'] = [
                'name' => 'Storage Directory Write Permissions',
                'status' => 'fail',
                'message' => 'Storage directory write test failed: ' . $e->getMessage(),
                'duration_ms' => round((microtime(true) - $fsStart) * 1000, 2),
            ];
        }

        // 5. OmniRoute Gateway Readiness
        $gwStart = microtime(true);
        try {
            $baseUrl = rtrim(config('omniroute.base_url', 'http://127.0.0.1:20128'), '/');
            $res = Http::timeout(2)->get("{$baseUrl}/v1/models");
            $isOnline = $res->successful() || $res->status() === 401;

            $checks['omniroute_gateway'] = [
                'name' => 'OmniRoute AI Gateway Connection',
                'status' => $isOnline ? 'pass' : 'info',
                'message' => $isOnline 
                    ? 'OmniRoute Gateway responds normally (' . round((microtime(true) - $gwStart) * 1000) . 'ms).' 
                    : 'OmniRoute gateway is in Standalone Mode.',
                'duration_ms' => round((microtime(true) - $gwStart) * 1000, 2),
            ];
        } catch (\Throwable $e) {
            $checks['omniroute_gateway'] = [
                'name' => 'OmniRoute AI Gateway Connection',
                'status' => 'info',
                'message' => 'OmniRoute local gateway offline. Standalone BYOK mode active.',
                'duration_ms' => round((microtime(true) - $gwStart) * 1000, 2),
            ];
        }

        return [
            'passed' => $allPassed,
            'checks' => $checks,
        ];
    }
}
