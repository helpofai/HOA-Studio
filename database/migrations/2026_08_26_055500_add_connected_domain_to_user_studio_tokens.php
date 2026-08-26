<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Add domain column to user_studio_tokens
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
        if (Schema::hasTable('user_studio_tokens')) {
            Schema::table('user_studio_tokens', function (Blueprint $table) {
                if (!Schema::hasColumn('user_studio_tokens', 'connected_domain')) {
                    $table->string('connected_domain')->nullable()->after('name');
                }
                if (!Schema::hasColumn('user_studio_tokens', 'last_ip')) {
                    $table->string('last_ip', 45)->nullable()->after('connected_domain');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('user_studio_tokens')) {
            Schema::table('user_studio_tokens', function (Blueprint $table) {
                if (Schema::hasColumn('user_studio_tokens', 'connected_domain')) {
                    $table->dropColumn('connected_domain');
                }
                if (Schema::hasColumn('user_studio_tokens', 'last_ip')) {
                    $table->dropColumn('last_ip');
                }
            });
        }
    }
};
