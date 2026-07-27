<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Taxonomy: travel styles, tour categories, blog categories, content/keyword tags.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('travel_styles', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique(); // long-duration, beach, ...
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('travel_style_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('travel_style_id')->constrained('travel_styles')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['travel_style_id', 'language_id'], 'travel_style_trans_unique');
            $table->unique(['language_id', 'slug'], 'travel_style_lang_slug_unique');
        });

        Schema::create('tour_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('type', 32)->default('theme'); // duration|region|theme|day-trip|package
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['country_id', 'type', 'is_active']);
        });

        Schema::create('tour_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_category_id')->constrained('tour_categories')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->longText('description')->nullable();
            $table->longText('seo_intro')->nullable();
            $table->timestamps();

            $table->unique(['tour_category_id', 'language_id'], 'tour_cat_trans_unique');
            $table->unique(['language_id', 'slug'], 'tour_cat_lang_slug_unique');
        });

        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('level', 20)->default('country'); // country|destination
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained('destinations')->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['level', 'country_id', 'is_active']);
        });

        Schema::create('blog_category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_category_id')->constrained('blog_categories')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->longText('seo_intro')->nullable();
            $table->timestamps();

            $table->unique(['blog_category_id', 'language_id'], 'blog_cat_trans_unique');
            $table->unique(['language_id', 'slug'], 'blog_cat_lang_slug_unique');
        });

        Schema::create('content_type_tags', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('content_type_tag_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_type_tag_id')->constrained('content_type_tags')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('label');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['content_type_tag_id', 'language_id'], 'content_tag_trans_unique');
            $table->unique(['language_id', 'slug'], 'content_tag_lang_slug_unique');
        });

        Schema::create('keyword_tags', function (Blueprint $table) {
            $table->id();
            $table->string('target_url')->nullable();
            $table->unsignedInteger('weight')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('keyword_tag_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('keyword_tag_id')->constrained('keyword_tags')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('label');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['keyword_tag_id', 'language_id'], 'keyword_tag_trans_unique');
            $table->unique(['language_id', 'slug'], 'keyword_tag_lang_slug_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keyword_tag_translations');
        Schema::dropIfExists('keyword_tags');
        Schema::dropIfExists('content_type_tag_translations');
        Schema::dropIfExists('content_type_tags');
        Schema::dropIfExists('blog_category_translations');
        Schema::dropIfExists('blog_categories');
        Schema::dropIfExists('tour_category_translations');
        Schema::dropIfExists('tour_categories');
        Schema::dropIfExists('travel_style_translations');
        Schema::dropIfExists('travel_styles');
    }
};
