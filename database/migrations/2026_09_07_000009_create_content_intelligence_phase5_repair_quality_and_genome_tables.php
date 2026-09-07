<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Phase 5 Migration
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
        // 1. Micro-Repairs Table (Surgical smallest-unit self-correction: Sentence -> Paragraph -> Section -> Article)
        if (! Schema::hasTable('micro_repairs')) {
            Schema::create('micro_repairs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
                $table->string('unit_type', 30)->default('sentence'); // sentence, paragraph, section, article
                $table->string('unit_pointer', 100)->nullable(); // e.g. sec-0-p-1-s-2
                $table->string('problem_category', 50)->default('readability'); // factual_error, weak_evidence, tone_mismatch, repetition, readability, missing_entity, structural_gap
                $table->text('root_cause')->nullable();
                $table->longText('original_text')->nullable();
                $table->longText('repaired_text')->nullable();
                $table->text('diff_summary')->nullable();
                $table->unsignedTinyInteger('escalation_level')->default(1); // 1=sentence, 2=paragraph, 3=section, 4=article
                $table->unsignedTinyInteger('iteration')->default(1);
                $table->string('status', 30)->default('detected'); // detected, repairing, resolved, escalated, failed
                $table->json('diagnostic_notes')->nullable();
                $table->timestamps();

                $table->index(['workflow_run_id', 'unit_type', 'status'], 'idx_repairs_run_unit_status');
            });
        }

        // 2. Quality Health Audits Table (15-Dimension Multidimensional Health Model)
        if (! Schema::hasTable('quality_health_audits')) {
            Schema::create('quality_health_audits', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
                $table->unsignedTinyInteger('overall_score')->default(80);
                $table->string('grade', 5)->default('B');
                $table->json('dimensions'); // 15 dimension breakdown with scores, weights, and explicit reasons
                $table->json('key_strengths')->nullable();
                $table->json('critical_gaps')->nullable();
                $table->json('recommendations')->nullable();
                $table->timestamps();

                $table->index(['workflow_run_id', 'overall_score'], 'idx_qha_run_score');
            });
        }

        // 3. Risk Assessments Table (LOW, MEDIUM, HIGH, CRITICAL with YMYL & Human Approval Gating)
        if (! Schema::hasTable('risk_assessments')) {
            Schema::create('risk_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
                $table->string('risk_level', 20)->default('low'); // low, medium, high, critical
                $table->unsignedTinyInteger('risk_score')->default(15);
                $table->boolean('is_ymyl')->default(false);
                $table->boolean('requires_primary_sources')->default(false);
                $table->boolean('requires_human_approval')->default(false);
                $table->boolean('is_approved_by_human')->default(false);
                $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamp('approved_at')->nullable();
                $table->json('risk_factors')->nullable();
                $table->json('mitigation_actions')->nullable();
                $table->timestamps();

                $table->index(['workflow_run_id', 'risk_level'], 'idx_risk_run_level');
            });
        }

        // 4. Content Genomes Table (Structured knowledge object for cross-article knowledge inheritance)
        if (! Schema::hasTable('content_genomes')) {
            Schema::create('content_genomes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
                $table->foreignId('workflow_run_id')->nullable()->constrained('workflow_runs')->nullOnDelete();
                $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
                $table->string('genome_signature', 64)->unique();
                $table->string('title');
                $table->json('mission_dna')->nullable();
                $table->json('topics_dna')->nullable();
                $table->json('entities_dna')->nullable();
                $table->json('claims_dna')->nullable();
                $table->json('facts_dna')->nullable();
                $table->json('sources_dna')->nullable();
                $table->json('quality_dna')->nullable();
                $table->json('reusable_fragments')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'project_id'], 'idx_genome_user_project');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('content_genomes');
        Schema::dropIfExists('risk_assessments');
        Schema::dropIfExists('quality_health_audits');
        Schema::dropIfExists('micro_repairs');
    }
};
