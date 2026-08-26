<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Authentication Security Logs Migration
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
    public function up(): void
    {
        if (!Schema::hasTable('auth_security_logs')) {
            Schema::create('auth_security_logs', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->index();
                $table->string('email')->nullable()->index();
                $table->string('event_type', 50)->index();
                $table->text('user_agent')->nullable();
                $table->json('details')->nullable();
                $table->boolean('is_blocked')->default(false)->index();
                $table->timestamp('blocked_until')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('blocked_ips')) {
            Schema::create('blocked_ips', function (Blueprint $table) {
                $table->id();
                $table->string('ip_address', 45)->unique();
                $table->string('reason')->nullable();
                $table->string('blocked_by')->default('system'); // 'system' or 'admin'
                $table->timestamp('blocked_until')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_ips');
        Schema::dropIfExists('auth_security_logs');
    }
};
