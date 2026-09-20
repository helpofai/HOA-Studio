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
        if (Schema::hasTable('antigravity_accounts')) {
            Schema::table('antigravity_accounts', function (Blueprint $table) {
                if (!Schema::hasColumn('antigravity_accounts', 'project_id')) {
                    $table->string('project_id')->nullable()->after('google_oauth_id');
                }
                if (!Schema::hasColumn('antigravity_accounts', 'tier_id')) {
                    $table->string('tier_id')->nullable()->after('project_id');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('antigravity_accounts')) {
            Schema::table('antigravity_accounts', function (Blueprint $table) {
                if (Schema::hasColumn('antigravity_accounts', 'tier_id')) {
                    $table->dropColumn('tier_id');
                }
                if (Schema::hasColumn('antigravity_accounts', 'project_id')) {
                    $table->dropColumn('project_id');
                }
            });
        }
    }
};
