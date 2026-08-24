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
        // 1. Persistent Vector Embeddings Cache (L2 Storage)
        if (!Schema::hasTable('vector_embeddings_cache')) {
            Schema::create('vector_embeddings_cache', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
                $table->string('hash', 64)->unique();
                $table->string('model', 64)->index();
                $table->unsignedInteger('dimensions')->default(1536);
                $table->json('vector');
                $table->unsignedInteger('token_count')->default(0);
                $table->unsignedBigInteger('hit_count')->default(1);
                $table->timestamp('last_accessed_at')->nullable()->index();
                $table->timestamps();
            });
        }

        // 2. Enhance knowledge_sources table with Brain categories and collection tags
        if (Schema::hasTable('knowledge_sources')) {
            Schema::table('knowledge_sources', function (Blueprint $table) {
                if (!Schema::hasColumn('knowledge_sources', 'category')) {
                    $table->string('category', 64)->default('general_docs')->after('source_type');
                }
                if (!Schema::hasColumn('knowledge_sources', 'is_active')) {
                    $table->boolean('is_active')->default(true)->after('status');
                }
                if (!Schema::hasColumn('knowledge_sources', 'metadata')) {
                    $table->json('metadata')->nullable()->after('is_active');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('vector_embeddings_cache');

        if (Schema::hasTable('knowledge_sources')) {
            Schema::table('knowledge_sources', function (Blueprint $table) {
                $table->dropColumn(['category', 'is_active', 'metadata']);
            });
        }
    }
};
