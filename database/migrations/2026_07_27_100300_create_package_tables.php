<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sản phẩm thống nhất: packages (tour | cruise) + itinerary + cabin + pivots.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('type', 16); // tour|cruise
            $table->foreignId('country_id')->constrained('countries')->restrictOnDelete();
            $table->string('code', 64)->nullable()->index();
            $table->unsignedSmallInteger('duration_days')->default(1);
            $table->unsignedSmallInteger('duration_nights')->default(0);
            $table->decimal('price_from', 12, 2)->nullable();
            $table->string('currency', 3)->default('VND');
            $table->decimal('rating', 3, 2)->default(0);
            $table->unsignedInteger('review_count')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_hot_deal')->default(false);
            $table->string('discount_badge')->nullable();
            $table->string('status', 20)->default('draft'); // draft|published|archived
            $table->timestamp('published_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->unsignedSmallInteger('sort')->default(0);

            // Cruise-only (nullable)
            $table->string('cruise_type', 32)->nullable(); // halong-bay|mekong|lan-ha|myanmar-river
            $table->string('departure_port')->nullable();
            $table->string('boat_class', 32)->nullable(); // classic|deluxe|luxury|private
            $table->unsignedSmallInteger('nights_on_board')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'country_id', 'status']);
            $table->index(['type', 'is_featured', 'status']);
            $table->index(['duration_days', 'status']);
            $table->index(['cruise_type', 'status']);
            $table->index('published_at');
        });

        Schema::create('package_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title');
            $table->string('start_location')->nullable();
            $table->string('end_location')->nullable();
            $table->json('places_to_visit')->nullable();
            $table->string('featured_quote_text')->nullable();
            $table->string('featured_quote_author')->nullable();
            $table->longText('highlights_intro')->nullable();
            $table->json('highlight_bullets')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->json('notes')->nullable();
            $table->longText('summary')->nullable();
            $table->timestamps();

            $table->unique(['package_id', 'language_id']);
            $table->index('title');
        });

        Schema::create('package_itinerary_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->unsignedSmallInteger('day_number');
            $table->string('meals_included')->nullable(); // B; L; D
            $table->json('transport_icons')->nullable();
            $table->string('distance_info')->nullable();
            $table->foreignId('image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['package_id', 'day_number']);
            $table->index(['package_id', 'sort']);
        });

        Schema::create('package_itinerary_day_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_itinerary_day_id')->constrained('package_itinerary_days', 'id', 'pkg_itin_day_trans_day_fk')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('overnight_at')->nullable();
            $table->json('internal_links')->nullable(); // [{label,url}]
            $table->timestamps();

            $table->unique(['package_itinerary_day_id', 'language_id'], 'pkg_itin_day_trans_unique');
        });

        Schema::create('package_cabin_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->unsignedTinyInteger('capacity')->nullable();
            $table->decimal('price_from', 12, 2)->nullable();
            $table->string('currency', 3)->nullable();
            $table->json('amenities')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['package_id', 'sort']);
        });

        Schema::create('package_cabin_type_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_cabin_type_id')->constrained('package_cabin_types', 'id', 'pkg_cabin_type_trans_fk')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['package_cabin_type_id', 'language_id'], 'pkg_cabin_trans_unique');
        });

        Schema::create('package_tour_category', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('tour_category_id')->constrained('tour_categories')->cascadeOnDelete();
            $table->unique(['package_id', 'tour_category_id'], 'pkg_tour_cat_unique');
        });

        Schema::create('package_travel_style', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('travel_style_id')->constrained('travel_styles')->cascadeOnDelete();
            $table->unique(['package_id', 'travel_style_id'], 'pkg_travel_style_unique');
            $table->index('travel_style_id');
        });

        Schema::create('package_destination', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained('destinations')->cascadeOnDelete();
            $table->unique(['package_id', 'destination_id'], 'pkg_destination_unique');
        });

        Schema::create('package_related', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('related_package_id')->constrained('packages')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->unique(['package_id', 'related_package_id'], 'pkg_related_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_related');
        Schema::dropIfExists('package_destination');
        Schema::dropIfExists('package_travel_style');
        Schema::dropIfExists('package_tour_category');
        Schema::dropIfExists('package_cabin_type_translations');
        Schema::dropIfExists('package_cabin_types');
        Schema::dropIfExists('package_itinerary_day_translations');
        Schema::dropIfExists('package_itinerary_days');
        Schema::dropIfExists('package_translations');
        Schema::dropIfExists('packages');
    }
};
