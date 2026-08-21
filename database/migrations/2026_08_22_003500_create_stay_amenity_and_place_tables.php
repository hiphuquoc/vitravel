<?php

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
        // 1. Tags Tiện ích (Stay Amenities)
        Schema::create('stay_amenities', function (Blueprint $table) {
            $table->id();
            $table->string('group_key', 32)->default('general'); // popular, room, outdoor, kitchen, dining, media, parking, general, family, business, safety, pool_beach, wellness, other
            $table->string('icon', 64)->nullable();
            $table->boolean('is_highlight')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['group_key', 'is_highlight', 'sort']);
        });

        Schema::create('stay_amenity_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_amenity_id')->constrained('stay_amenities')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->timestamps();

            $table->unique(['stay_amenity_id', 'language_id'], 'stay_amenity_trans_unique');
            $table->index(['language_id', 'name']);
            $table->index(['language_id', 'slug']);
        });

        // 2. Pivot Tiện ích - Khách sạn (Service)
        Schema::create('stay_amenity_service', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('stay_amenity_id')->constrained('stay_amenities')->cascadeOnDelete();
            $table->boolean('is_popular')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);

            $table->primary(['service_id', 'stay_amenity_id'], 'stay_amenity_svc_primary');
            $table->index(['service_id', 'is_popular']);
        });

        // 3. Pivot Tiện ích - Hạng phòng (ServiceOption)
        Schema::create('stay_amenity_service_option', function (Blueprint $table) {
            $table->foreignId('service_option_id')->constrained('service_options')->cascadeOnDelete();
            $table->foreignId('stay_amenity_id')->constrained('stay_amenities')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);

            $table->primary(['service_option_id', 'stay_amenity_id'], 'stay_amenity_opt_primary');
        });

        // 4. Tags Địa danh lân cận (Stay Places)
        Schema::create('stay_places', function (Blueprint $table) {
            $table->id();
            $table->string('category', 32)->default('landmark'); // beach, landmark, nature, dining, transport, other
            $table->string('icon', 64)->nullable();
            $table->decimal('lat', 10, 7)->nullable();
            $table->decimal('lng', 10, 7)->nullable();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->timestamps();

            $table->index(['category', 'project_id']);
        });

        Schema::create('stay_place_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stay_place_id')->constrained('stay_places')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->timestamps();

            $table->unique(['stay_place_id', 'language_id'], 'stay_place_trans_unique');
            $table->index(['language_id', 'name']);
            $table->index(['language_id', 'slug']);
        });

        // 5. Pivot Địa danh - Khách sạn kèm khoảng cách tính theo Mét (distance_meters)
        Schema::create('stay_place_service', function (Blueprint $table) {
            $table->foreignId('service_id')->constrained('services')->cascadeOnDelete();
            $table->foreignId('stay_place_id')->constrained('stay_places')->cascadeOnDelete();
            $table->unsignedInteger('distance_meters')->default(0);
            $table->unsignedSmallInteger('sort')->default(0);

            $table->primary(['service_id', 'stay_place_id'], 'stay_place_svc_primary');
            $table->index(['service_id', 'distance_meters']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stay_place_service');
        Schema::dropIfExists('stay_place_translations');
        Schema::dropIfExists('stay_places');
        Schema::dropIfExists('stay_amenity_service_option');
        Schema::dropIfExists('stay_amenity_service');
        Schema::dropIfExists('stay_amenity_translations');
        Schema::dropIfExists('stay_amenities');
    }
};
