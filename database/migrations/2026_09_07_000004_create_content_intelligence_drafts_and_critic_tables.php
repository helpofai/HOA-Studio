<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Section Drafts and Critic Migration
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
        if (! Schema::hasTable('section_drafts')) {
            Schema::create('section_drafts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('workflow_run_id')->constrained('workflow_runs')->cascadeOnDelete();
                $table->string('section_id')->index();
                $table->string('heading');
                $table->longText('content_html');
                $table->longText('content_markdown')->nullable();
                $table->unsignedInteger('word_count')->default(0);
                $table->decimal('critic_score', 5, 2)->default(0.00);
                $table->json('critic_feedback')->nullable();
                $table->unsignedInteger('revision_count')->default(0);
                $table->string('status')->default('draft')->index();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('section_drafts');
    }
};
