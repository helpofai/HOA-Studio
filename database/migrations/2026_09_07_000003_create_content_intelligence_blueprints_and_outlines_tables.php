<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Blueprints and Outlines Migration
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
        if (! Schema::hasTable('content_blueprints')) {
            Schema::create('content_blueprints', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mission_id')->constrained('content_missions')->cascadeOnDelete();
                $table->text('article_angle');
                $table->text('unique_value_proposition');
                $table->json('target_transformation')->nullable();
                $table->json('required_sections');
                $table->json('optional_sections')->nullable();
                $table->json('required_entities')->nullable();
                $table->json('internal_links')->nullable();
                $table->json('external_sources')->nullable();
                $table->json('faq_requirements')->nullable();
                $table->json('quality_targets')->nullable();
                $table->string('status')->default('approved')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('content_outlines')) {
            Schema::create('content_outlines', function (Blueprint $table) {
                $table->id();
                $table->foreignId('blueprint_id')->constrained('content_blueprints')->cascadeOnDelete();
                $table->foreignId('mission_id')->constrained('content_missions')->cascadeOnDelete();
                $table->unsignedInteger('total_sections')->default(0);
                $table->unsignedInteger('target_word_count')->default(2000);
                $table->json('section_nodes');
                $table->string('status')->default('ready')->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_outlines');
        Schema::dropIfExists('content_blueprints');
    }
};
