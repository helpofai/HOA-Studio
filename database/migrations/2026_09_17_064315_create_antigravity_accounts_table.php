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
        if (!Schema::hasTable('antigravity_accounts')) {
            Schema::create('antigravity_accounts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('email')->nullable();
                $table->string('google_oauth_id')->nullable();
                $table->text('google_oauth_token')->nullable();
                $table->text('google_oauth_refresh_token')->nullable();
                $table->timestamp('token_expires_at')->nullable();
                $table->text('antigravity_key')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('is_quota_exhausted')->default(false);
                $table->timestamp('quota_reset_at')->nullable();
                $table->integer('priority_order')->default(0);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('antigravity_accounts');
    }
};
