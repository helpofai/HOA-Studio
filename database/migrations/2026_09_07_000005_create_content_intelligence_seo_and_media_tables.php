<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - SEO and Media Tables Migration
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
        if (!Schema::hasTable('content_seo_metadatas')) {
            Schema::create('content_seo_metadatas', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
                $table->foreignId('mission_id')->constrained('content_missions')->cascadeOnDelete();
                $table->string('meta_title', 190);
                $table->text('meta_description');
                $table->string('canonical_url')->nullable();
                $table->string('primary_keyword');
                $table->json('secondary_keywords')->nullable();
                $table->json('schema_json_ld')->nullable();
                $table->unsignedSmallInteger('seo_score')->default(0);
                $table->json('keyword_density')->nullable();
                $table->boolean('heading_hierarchy_valid')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('content_media_assets')) {
            Schema::create('content_media_assets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
                $table->string('section_id')->index();
                $table->string('asset_type')->index(); // diagram, table, callout, snippet
                $table->string('title');
                $table->longText('content');
                $table->string('placement')->default('in_body');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_media_assets');
        Schema::dropIfExists('content_seo_metadatas');
    }
};
