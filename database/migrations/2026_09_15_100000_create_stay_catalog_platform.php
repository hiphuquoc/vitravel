<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stay_crawl_items', function (Blueprint $table) {
            $table->string('source', 32)->nullable()->after('canonical_url');
            $table->string('source_hotel_key', 191)->nullable()->after('source');
            $table->string('booking_cc', 8)->nullable()->after('source_hotel_key');
            $table->unsignedBigInteger('stay_property_id')->nullable()->after('service_id');
            $table->index(['source', 'source_hotel_key']);
            $table->index('stay_property_id');
        });

        Schema::table('stay_crawl_jobs', function (Blueprint $table) {
            $table->string('job_type', 32)->default('listing')->after('service_category_id');
            $table->unsignedBigInteger('stay_area_id')->nullable()->after('job_type');
            $table->index('job_type');
            $table->index('stay_area_id');
        });

        Schema::table('services', function (Blueprint $table) {
            $table->decimal('lat', 10, 7)->nullable()->after('attrs');
            $table->decimal('lng', 10, 7)->nullable()->after('lat');
            $table->unsignedBigInteger('stay_property_id')->nullable()->after('lng');
            $table->unsignedBigInteger('stay_area_id')->nullable()->after('stay_property_id');
            $table->index(['lat', 'lng']);
            $table->index('stay_property_id');
            $table->index('stay_area_id');
        });

        Schema::create('stay_amenity_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_amenity_id')->constrained('stay_amenities')->cascadeOnDelete();
            $table->string('alias', 191);
            $table->string('normalized', 191);
            $table->timestamps();
            $table->unique('normalized');
            $table->index('stay_amenity_id');
        });

        Schema::create('stay_place_aliases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_place_id')->constrained('stay_places')->cascadeOnDelete();
            $table->string('alias', 191);
            $table->string('normalized', 191);
            $table->timestamps();
            $table->unique('normalized');
            $table->index('stay_place_id');
        });

        Schema::create('stay_areas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('stay_areas')->nullOnDelete();
            $table->string('slug', 120);
            $table->string('level', 32);
            $table->string('country_code', 8)->nullable();
            $table->string('booking_dest_id', 32)->nullable();
            $table->string('booking_dest_type', 32)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->json('bbox')->nullable();
            $table->unsignedInteger('radius_meters')->nullable();
            $table->json('aliases')->nullable();
            $table->string('list_url', 500)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique('slug');
            $table->index(['parent_id', 'level']);
            $table->index('booking_dest_id');
            $table->index('country_code');
        });

        Schema::create('stay_area_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_area_id')->constrained('stay_areas')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('intro')->nullable();
            $table->timestamps();
            $table->unique(['stay_area_id', 'language_id'], 'stay_area_trans_unique');
        });

        Schema::create('stay_area_related', function (Blueprint $table) {
            $table->foreignId('stay_area_id')->constrained('stay_areas')->cascadeOnDelete();
            $table->foreignId('related_area_id')->constrained('stay_areas')->cascadeOnDelete();
            $table->primary(['stay_area_id', 'related_area_id']);
        });

        Schema::create('stay_taxons', function (Blueprint $table) {
            $table->id();
            $table->string('group', 64);
            $table->string('code', 191);
            $table->foreignId('stay_area_id')->nullable()->constrained('stay_areas')->nullOnDelete();
            $table->unsignedBigInteger('service_category_id')->nullable();
            $table->text('filter_url')->nullable();
            $table->string('source', 32)->default('booking.com');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('create_page')->default(false);
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->unique(['source', 'code', 'stay_area_id'], 'stay_taxon_source_code_area');
            $table->index(['group', 'is_active']);
        });

        Schema::create('stay_taxon_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_taxon_id')->constrained('stay_taxons')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->timestamps();
            $table->unique(['stay_taxon_id', 'language_id'], 'stay_taxon_trans_unique');
        });

        Schema::create('stay_properties', function (Blueprint $table) {
            $table->id();
            $table->string('source', 32)->default('booking.com');
            $table->string('source_hotel_key', 191);
            $table->string('booking_cc', 8)->nullable();
            $table->string('canonical_url', 500);
            $table->text('source_url')->nullable();
            $table->foreignId('primary_stay_area_id')->nullable()->constrained('stay_areas')->nullOnDelete();
            $table->string('country_code', 8)->nullable();
            $table->string('property_type', 32)->nullable();
            $table->unsignedTinyInteger('star_rating')->nullable();
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('review_count')->default(0);
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->decimal('price_from', 12, 2)->nullable();
            $table->string('currency', 3)->default('VND');
            $table->string('status', 20)->default('published');
            $table->json('completeness')->nullable();
            $table->timestamp('last_crawled_at')->nullable();
            $table->json('attrs')->nullable();
            $table->timestamps();
            $table->unique(['source', 'source_hotel_key'], 'stay_property_source_key');
            $table->index('primary_stay_area_id');
            $table->index(['lat', 'lng']);
            $table->index('property_type');
        });

        Schema::create('stay_property_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_property_id')->constrained('stay_properties')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title');
            $table->string('location_label')->nullable();
            $table->longText('content')->nullable();
            $table->timestamps();
            $table->unique(['stay_property_id', 'language_id'], 'stay_property_trans_unique');
        });

        Schema::create('stay_property_area', function (Blueprint $table) {
            $table->foreignId('stay_property_id')->constrained('stay_properties')->cascadeOnDelete();
            $table->foreignId('stay_area_id')->constrained('stay_areas')->cascadeOnDelete();
            $table->string('role', 16)->default('primary');
            $table->primary(['stay_property_id', 'stay_area_id']);
        });

        Schema::create('stay_property_taxon', function (Blueprint $table) {
            $table->foreignId('stay_property_id')->constrained('stay_properties')->cascadeOnDelete();
            $table->foreignId('stay_taxon_id')->constrained('stay_taxons')->cascadeOnDelete();
            $table->primary(['stay_property_id', 'stay_taxon_id']);
        });

        Schema::create('stay_category_bindings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_category_id')->constrained('service_categories')->cascadeOnDelete();
            $table->foreignId('stay_area_id')->constrained('stay_areas')->cascadeOnDelete();
            $table->foreignId('stay_taxon_id')->nullable()->constrained('stay_taxons')->nullOnDelete();
            $table->boolean('include_child_areas')->default(true);
            $table->boolean('include_related_areas')->default(false);
            $table->string('sync_mode', 16)->default('auto');
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->index('service_category_id');
        });

        Schema::create('stay_category_binding_areas', function (Blueprint $table) {
            $table->foreignId('stay_category_binding_id')->constrained('stay_category_bindings')->cascadeOnDelete();
            $table->foreignId('stay_area_id')->constrained('stay_areas')->cascadeOnDelete();
            $table->primary(['stay_category_binding_id', 'stay_area_id'], 'stay_bind_extra_area_pk');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stay_category_binding_areas');
        Schema::dropIfExists('stay_category_bindings');
        Schema::dropIfExists('stay_property_taxon');
        Schema::dropIfExists('stay_property_area');
        Schema::dropIfExists('stay_property_translations');
        Schema::dropIfExists('stay_properties');
        Schema::dropIfExists('stay_taxon_translations');
        Schema::dropIfExists('stay_taxons');
        Schema::dropIfExists('stay_area_related');
        Schema::dropIfExists('stay_area_translations');
        Schema::dropIfExists('stay_areas');
        Schema::dropIfExists('stay_place_aliases');
        Schema::dropIfExists('stay_amenity_aliases');

        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex(['lat', 'lng']);
            $table->dropIndex(['stay_property_id']);
            $table->dropIndex(['stay_area_id']);
            $table->dropColumn(['lat', 'lng', 'stay_property_id', 'stay_area_id']);
        });

        Schema::table('stay_crawl_jobs', function (Blueprint $table) {
            $table->dropIndex(['job_type']);
            $table->dropIndex(['stay_area_id']);
            $table->dropColumn(['job_type', 'stay_area_id']);
        });

        Schema::table('stay_crawl_items', function (Blueprint $table) {
            $table->dropIndex(['source', 'source_hotel_key']);
            $table->dropIndex(['stay_property_id']);
            $table->dropColumn(['source', 'source_hotel_key', 'booking_cc', 'stay_property_id']);
        });
    }
};
