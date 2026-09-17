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
        if (!Schema::hasColumn('users', 'antigravity_key')) {
            Schema::table('users', function (Blueprint $table) {
                // If deepseek_key is missing, fall back to adding after anthropic_key or password
                if (Schema::hasColumn('users', 'deepseek_key')) {
                    $table->text('antigravity_key')->nullable()->after('deepseek_key');
                } else if (Schema::hasColumn('users', 'password')) {
                    $table->text('antigravity_key')->nullable()->after('password');
                } else {
                    $table->text('antigravity_key')->nullable();
                }

                if (!Schema::hasColumn('users', 'google_oauth_id')) {
                    $table->string('google_oauth_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('users', 'google_oauth_token')) {
                    $table->text('google_oauth_token')->nullable()->after('google_oauth_id');
                }
                if (!Schema::hasColumn('users', 'google_oauth_refresh_token')) {
                    $table->text('google_oauth_refresh_token')->nullable()->after('google_oauth_token');
                }
                if (!Schema::hasColumn('users', 'avatar_url')) {
                    $table->string('avatar_url')->nullable()->after('email');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('users', 'antigravity_key')) $columns[] = 'antigravity_key';
            if (Schema::hasColumn('users', 'google_oauth_id')) $columns[] = 'google_oauth_id';
            if (Schema::hasColumn('users', 'google_oauth_token')) $columns[] = 'google_oauth_token';
            if (Schema::hasColumn('users', 'google_oauth_refresh_token')) $columns[] = 'google_oauth_refresh_token';
            if (Schema::hasColumn('users', 'avatar_url')) $columns[] = 'avatar_url';

            if (count($columns) > 0) {
                $table->dropColumn($columns);
            }
        });
    }
};
