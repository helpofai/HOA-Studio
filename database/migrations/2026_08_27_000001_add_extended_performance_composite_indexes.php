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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Composite index for Documents filtering (user_id, status, project_id, updated_at)
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasIndex('documents', 'idx_documents_user_filter')) {
                $table->index(['user_id', 'status', 'project_id', 'updated_at'], 'idx_documents_user_filter');
            }
        });

        // 2. Composite index for active BYOK API keys lookup (user_id, provider_slug, is_active)
        Schema::table('user_api_keys', function (Blueprint $table) {
            if (!Schema::hasIndex('user_api_keys', 'idx_user_api_keys_active_lookup')) {
                $table->index(['user_id', 'provider_slug', 'is_active'], 'idx_user_api_keys_active_lookup');
            }
        });

        // 3. Composite index for active document shares lookup (document_id, is_active)
        Schema::table('document_shares', function (Blueprint $table) {
            if (!Schema::hasIndex('document_shares', 'idx_document_shares_active')) {
                $table->index(['document_id', 'is_active'], 'idx_document_shares_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasIndex('documents', 'idx_documents_user_filter')) {
                $table->dropIndex('idx_documents_user_filter');
            }
        });

        Schema::table('user_api_keys', function (Blueprint $table) {
            if (Schema::hasIndex('user_api_keys', 'idx_user_api_keys_active_lookup')) {
                $table->dropIndex('idx_user_api_keys_active_lookup');
            }
        });

        Schema::table('document_shares', function (Blueprint $table) {
            if (Schema::hasIndex('document_shares', 'idx_document_shares_active')) {
                $table->dropIndex('idx_document_shares_active');
            }
        });
    }
};
