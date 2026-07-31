<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catalogue dịch vụ mở rộng: train | flight | stay | experience | other
 * Pattern: hub (SEO) → service_categories → services (+ options)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_categories', function (Blueprint $table) {
            $table->id();
            $table->string('cluster', 32); // train|flight|stay|experience|other
            $table->string('slug', 64);
            $table->string('name');
            $table->text('intro')->nullable();
            $table->foreignId('banner_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['cluster', 'slug']);
            $table->index(['cluster', 'is_active', 'sort']);
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('cluster', 32);
            $table->foreignId('service_category_id')->nullable()->constrained('service_categories')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('code', 64)->nullable()->index();
            $table->decimal('price_from', 12, 2)->nullable();
            $table->string('currency', 3)->default('VND');
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->unsignedTinyInteger('star_rating')->nullable(); // khách sạn 1–5
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_hot_deal')->default(false);
            $table->string('discount_badge')->nullable();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->json('attrs')->nullable(); // route, amenities, operator…
            $table->timestamps();
            $table->softDeletes();

            $table->index(['cluster', 'status', 'is_featured']);
            $table->index(['service_category_id', 'status']);
            $table->index(['country_id', 'status']);
            $table->index('published_at');
        });

        Schema::create('service_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title');
            $table->string('location_label')->nullable();
            $table->longText('summary')->nullable();
            $table->json('highlights')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->json('notes')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();

            $table->unique(['service_id', 'language_id']);
            $table->index('title');
        });

        Schema::create('service_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->string('code', 64)->nullable();
            $table->decimal('price_from', 12, 2)->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->json('attrs')->nullable();
            $table->timestamps();

            $table->index(['service_id', 'sort']);
        });

        Schema::create('service_option_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_option_id')->constrained('service_options')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('amenities')->nullable();
            $table->timestamps();

            $table->unique(['service_option_id', 'language_id'], 'svc_opt_trans_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_option_translations');
        Schema::dropIfExists('service_options');
        Schema::dropIfExists('service_translations');
        Schema::dropIfExists('services');
        Schema::dropIfExists('service_categories');
    }
};
