<?php

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
        if (!Schema::hasTable('antigravity_telemetry')) {
            Schema::create('antigravity_telemetry', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('ai_model_id')->constrained('ai_models')->onDelete('cascade');
                $table->integer('tokens_used');
                $table->integer('latency_ms');
                $table->integer('status_code');
                $table->timestamp('created_at')->useCurrent();
            });
        }
        
        // Extend existing ai_models table
        Schema::table('ai_models', function (Blueprint $table) {
            if (!Schema::hasColumn('ai_models', 'status')) {
                $table->string('status')->default('unknown')->after('is_active');
            }
            if (!Schema::hasColumn('ai_models', 'latency_ms')) {
                $table->integer('latency_ms')->default(0)->after('status');
            }
            if (!Schema::hasColumn('ai_models', 'quota_limit_per_model')) {
                $table->integer('quota_limit_per_model')->nullable()->after('latency_ms');
            }
            if (!Schema::hasColumn('ai_models', 'last_health_check_at')) {
                $table->timestamp('last_health_check_at')->nullable()->after('quota_limit_per_model');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antigravity_telemetry');
    }
};
