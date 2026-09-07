<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Knowledge and Claims Migration
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
        if (! Schema::hasTable('source_intelligences')) {
            Schema::create('source_intelligences', function (Blueprint $table) {
                $table->id();
                $table->string('url_hash', 64)->index();
                $table->text('url');
                $table->string('title');
                $table->string('source_type')->default('industry_publication')->index();
                $table->unsignedInteger('reliability_score')->default(80);
                $table->unsignedInteger('domain_authority')->default(50);
                $table->boolean('is_primary')->default(false)->index();
                $table->date('publication_date')->nullable();
                $table->timestamp('last_verified_at')->nullable();
                $table->json('metadata')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('research_items')) {
            Schema::create('research_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mission_id')->constrained('content_missions')->cascadeOnDelete();
                $table->foreignId('source_id')->nullable()->constrained('source_intelligences')->nullOnDelete();
                $table->string('query');
                $table->longText('extracted_text');
                $table->json('key_facts')->nullable();
                $table->decimal('confidence_score', 5, 4)->default(0.9000);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('knowledge_triples')) {
            Schema::create('knowledge_triples', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mission_id')->constrained('content_missions')->cascadeOnDelete();
                $table->foreignId('source_id')->nullable()->constrained('source_intelligences')->nullOnDelete();
                $table->string('subject')->index();
                $table->string('predicate')->index();
                $table->text('object');
                $table->decimal('confidence', 5, 4)->default(0.9500);
                $table->string('epistemic_state')->default('verified')->index();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('claim_nodes')) {
            Schema::create('claim_nodes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('mission_id')->constrained('content_missions')->cascadeOnDelete();
                $table->foreignId('source_id')->nullable()->constrained('source_intelligences')->nullOnDelete();
                $table->text('statement');
                $table->string('epistemic_state')->default('verified')->index();
                $table->text('evidence_extract')->nullable();
                $table->string('section_target')->nullable()->index();
                $table->decimal('confidence_score', 5, 4)->default(0.9500);
                $table->boolean('is_controversial')->default(false)->index();
                $table->json('contradiction_details')->nullable();
                $table->string('resolution_strategy')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('claim_nodes');
        Schema::dropIfExists('knowledge_triples');
        Schema::dropIfExists('research_items');
        Schema::dropIfExists('source_intelligences');
    }
};
