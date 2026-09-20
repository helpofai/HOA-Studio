<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Antigravity Usage Metrics Migration
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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('antigravity_accounts')) {
            Schema::table('antigravity_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('antigravity_accounts', 'total_tokens_used')) {
                    $table->unsignedBigInteger('total_tokens_used')->default(0)->after('is_quota_exhausted');
                }
                if (!Schema::hasColumn('antigravity_accounts', 'total_requests_count')) {
                    $table->unsignedInteger('total_requests_count')->default(0)->after('total_tokens_used');
                }
                if (!Schema::hasColumn('antigravity_accounts', 'daily_token_limit')) {
                    $table->unsignedBigInteger('daily_token_limit')->nullable()->after('total_requests_count');
                }
                if (!Schema::hasColumn('antigravity_accounts', 'last_used_at')) {
                    $table->timestamp('last_used_at')->nullable()->after('daily_token_limit');
                }
            });
        }

        if (Schema::hasTable('antigravity_telemetry')) {
            Schema::table('antigravity_telemetry', function (Blueprint $table) {
                if (!Schema::hasColumn('antigravity_telemetry', 'antigravity_account_id')) {
                    $table->foreignId('antigravity_account_id')->nullable()->after('ai_model_id')->constrained('antigravity_accounts')->nullOnDelete();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('antigravity_telemetry')) {
            Schema::table('antigravity_telemetry', function (Blueprint $table) {
                if (Schema::hasColumn('antigravity_telemetry', 'antigravity_account_id')) {
                    $table->dropForeign(['antigravity_account_id']);
                    $table->dropColumn('antigravity_account_id');
                }
            });
        }

        if (Schema::hasTable('antigravity_accounts')) {
            Schema::table('antigravity_accounts', function (Blueprint $table) {
                if (Schema::hasColumn('antigravity_accounts', 'total_tokens_used')) {
                    $table->dropColumn(['total_tokens_used', 'total_requests_count', 'daily_token_limit', 'last_used_at']);
                }
            });
        }
    }
};
