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
        Schema::table('documents', function (Blueprint $table) {
            if (!Schema::hasIndex('documents', 'idx_documents_user_updated')) {
                $table->index(['user_id', 'updated_at'], 'idx_documents_user_updated');
            }
        });

        Schema::table('knowledge_chunks', function (Blueprint $table) {
            if (!Schema::hasIndex('knowledge_chunks', 'idx_chunks_source_index')) {
                $table->index(['knowledge_source_id', 'chunk_index'], 'idx_chunks_source_index');
            }
        });

        Schema::table('generation_usage', function (Blueprint $table) {
            if (!Schema::hasIndex('generation_usage', 'idx_usage_user_recorded')) {
                $table->index(['user_id', 'recorded_at'], 'idx_usage_user_recorded');
            }
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            if (Schema::hasIndex('documents', 'idx_documents_user_updated')) {
                $table->dropIndex('idx_documents_user_updated');
            }
        });

        Schema::table('knowledge_chunks', function (Blueprint $table) {
            if (Schema::hasIndex('knowledge_chunks', 'idx_chunks_source_index')) {
                $table->dropIndex('idx_chunks_source_index');
            }
        });

        Schema::table('generation_usage', function (Blueprint $table) {
            if (Schema::hasIndex('generation_usage', 'idx_usage_user_recorded')) {
                $table->dropIndex('idx_usage_user_recorded');
            }
        });
    }
};