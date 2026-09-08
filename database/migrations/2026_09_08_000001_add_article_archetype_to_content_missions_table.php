<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Add Article Archetype Migration
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
        if (Schema::hasTable('content_missions')) {
            if (! Schema::hasColumn('content_missions', 'article_archetype')) {
                Schema::table('content_missions', function (Blueprint $table) {
                    $table->string('article_archetype')->default('auto_detect')->after('content_type')->index();
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('content_missions')) {
            if (Schema::hasColumn('content_missions', 'article_archetype')) {
                Schema::table('content_missions', function (Blueprint $table) {
                    $table->dropColumn('article_archetype');
                });
            }
        }
    }
};
