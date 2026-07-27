<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Nền tảng i18n + media + SEO hub (pattern Hitour V3, greenfield sạch).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name', 50);
            $table->string('name_native', 50)->nullable();
            $table->string('flag')->nullable();
            $table->string('og_locale', 20)->nullable();
            $table->string('hreflang', 20)->nullable();
            $table->string('dir', 3)->default('ltr');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('disk', 32)->default('public');
            $table->string('path');
            $table->string('filename')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->string('alt')->nullable();
            $table->string('credit')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('disk');
            $table->index('mime_type');
        });

        Schema::create('seo_entries', function (Blueprint $table) {
            $table->id();
            $table->string('reference_type', 64);
            $table->unsignedBigInteger('reference_id');
            $table->foreignId('og_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->decimal('rating_aggregate_star', 3, 2)->nullable();
            $table->unsignedInteger('rating_aggregate_count')->nullable();
            $table->boolean('is_indexable')->default(true);
            $table->timestamps();

            $table->unique(['reference_type', 'reference_id'], 'seo_entries_ref_unique');
            $table->index(['reference_type', 'reference_id'], 'seo_entries_ref_idx');
        });

        Schema::create('seo_entry_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seo_entry_id')->constrained('seo_entries')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->text('description')->nullable();
            $table->string('seo_title')->nullable();
            $table->string('seo_description', 320)->nullable();
            $table->string('keywords', 500)->nullable();
            $table->string('slug', 191);
            $table->string('slug_full', 512);
            $table->string('canonical_url', 1024)->nullable();
            $table->string('og_image_override')->nullable();
            $table->string('status', 20)->default('draft'); // draft|published|archived
            $table->string('translation_status', 20)->default('manual'); // manual|auto|reviewed
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->unique(['seo_entry_id', 'language_id'], 'seo_trans_entry_lang_unique');
            $table->index(['language_id', 'slug'], 'seo_trans_lang_slug_idx');
            $table->index('status');
        });

        // Prefix unique — utf8mb4 MySQL key length limit
        DB::statement('CREATE UNIQUE INDEX seo_trans_lang_slug_full_unique ON seo_entry_translations (language_id, slug_full(191))');
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_entry_translations');
        Schema::dropIfExists('seo_entries');
        Schema::dropIfExists('media');
        Schema::dropIfExists('languages');
    }
};
