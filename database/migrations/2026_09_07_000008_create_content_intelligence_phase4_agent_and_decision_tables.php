<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Phase 4 Agent & Decision Migration
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
        // 1. Brain Decisions (Auditable, explainable decisions made by the Content Brain)
        if (! Schema::hasTable('brain_decisions')) {
            Schema::create('brain_decisions', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->foreignId('mission_id')->nullable()->constrained('content_missions')->cascadeOnDelete();
                $table->foreignId('workflow_run_id')->nullable()->constrained('workflow_runs')->cascadeOnDelete();
                $table->string('question');
                $table->string('decision');
                $table->text('reasoning');
                $table->json('inputs')->nullable();
                $table->json('alternatives')->nullable();
                $table->decimal('confidence', 5, 4)->default(0.9000);
                $table->timestamps();
            });
        }

        // 2. Agent Activities (Worker agent execution telemetry & outputs)
        if (! Schema::hasTable('agent_activities')) {
            Schema::create('agent_activities', function (Blueprint $table) {
                $table->string('id', 64)->primary();
                $table->foreignId('mission_id')->nullable()->constrained('content_missions')->cascadeOnDelete();
                $table->foreignId('workflow_run_id')->nullable()->constrained('workflow_runs')->cascadeOnDelete();
                $table->string('agent_name', 64)->index(); // researcher, analyst, writer, fact_checker, critic, seo, editor
                $table->string('task_type', 64)->index();
                $table->string('model_used', 128)->nullable();
                $table->unsignedInteger('tokens_used')->default(0);
                $table->unsignedInteger('latency_ms')->default(0);
                $table->string('status', 32)->default('completed'); // pending, running, completed, failed
                $table->json('input_payload')->nullable();
                $table->text('output_summary')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_activities');
        Schema::dropIfExists('brain_decisions');
    }
};
