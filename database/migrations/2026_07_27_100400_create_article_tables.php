<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Blog / Travel Guide + comments (moderation).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained('destinations')->nullOnDelete();
            $table->foreignId('blog_category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
            $table->string('author_name')->nullable();
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('rating_count')->default(0);
            $table->unsignedInteger('view_count')->default(0);
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'published_at']);
            $table->index(['country_id', 'status']);
            $table->index(['blog_category_id', 'status']);
        });

        Schema::create('article_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title');
            $table->string('excerpt', 500)->nullable();
            $table->longText('content')->nullable();
            $table->json('inline_related_links')->nullable();
            $table->timestamps();

            $table->unique(['article_id', 'language_id']);
            $table->index('title');
        });

        Schema::create('article_content_type_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('content_type_tag_id')->constrained('content_type_tags')->cascadeOnDelete();
            $table->unique(['article_id', 'content_type_tag_id'], 'article_content_tag_unique');
        });

        Schema::create('article_keyword_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('keyword_tag_id')->constrained('keyword_tags')->cascadeOnDelete();
            $table->unique(['article_id', 'keyword_tag_id'], 'article_keyword_tag_unique');
        });

        Schema::create('article_package', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->unique(['article_id', 'package_id']);
        });

        Schema::create('article_related', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->foreignId('related_article_id')->constrained('articles')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->unique(['article_id', 'related_article_id']);
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->cascadeOnDelete();
            $table->string('full_name');
            $table->string('email');
            $table->string('phone', 50)->nullable();
            $table->text('content');
            $table->string('status', 20)->default('pending'); // pending|approved|rejected
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();

            $table->index(['article_id', 'status']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
        Schema::dropIfExists('article_related');
        Schema::dropIfExists('article_package');
        Schema::dropIfExists('article_keyword_tag');
        Schema::dropIfExists('article_content_type_tag');
        Schema::dropIfExists('article_translations');
        Schema::dropIfExists('articles');
    }
};
