<?php

/*
|--------------------------------------------------------------------------
| HelpOfAi (HOA) Professional Software - Create Blog Posts Table Migration
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
        if (!Schema::hasTable('blog_posts')) {
            Schema::create('blog_posts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->cascadeOnDelete();
                $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('excerpt')->nullable();
                $table->longText('content_html');
                $table->longText('content_markdown')->nullable();
                $table->string('featured_image')->nullable();
                $table->string('category')->default('General')->index();
                $table->json('tags')->nullable();
                $table->string('status')->default('published')->index(); // published, draft
                $table->boolean('is_featured')->default(false)->index();
                $table->unsignedInteger('views_count')->default(0);
                $table->unsignedInteger('reading_time_minutes')->default(1);
                $table->string('seo_title')->nullable();
                $table->text('seo_description')->nullable();
                $table->timestamp('published_at')->nullable()->index();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
