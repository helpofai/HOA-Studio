<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Expand User Studio Tokens Prefix Column
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
                $table->string('token_prefix', 64)->change();
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
                $table->string('token_prefix', 16)->change();
            });
        }
    }
};
