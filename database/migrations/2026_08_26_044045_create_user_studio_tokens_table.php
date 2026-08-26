<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - User Studio Connect Tokens Migration
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
        if (!Schema::hasTable('user_studio_tokens')) {
            Schema::create('user_studio_tokens', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->string('name')->default('WordPress Integration');
                $table->string('token_prefix', 16)->index();
                $table->string('token_hash', 64)->unique()->index();
                $table->json('abilities')->nullable();
                $table->timestamp('last_used_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->index(['user_id', 'is_active']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_studio_tokens');
    }
};
