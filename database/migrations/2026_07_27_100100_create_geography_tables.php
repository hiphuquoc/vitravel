<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Địa lý: quốc gia điểm đến + điểm đến/thành phố con.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->nullable()->unique(); // VN, TH, KH...
            $table->string('home_grid_size', 16)->default('normal'); // large|normal
            $table->foreignId('banner_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('show_in_customize_form')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort']);
        });

        Schema::create('country_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->string('tagline')->nullable();
            $table->longText('intro_text')->nullable();
            $table->longText('long_form_content')->nullable();
            $table->timestamps();

            $table->unique(['country_id', 'language_id']);
            $table->unique(['language_id', 'slug']);
        });

        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->foreignId('image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['country_id', 'is_active', 'sort']);
        });

        Schema::create('destination_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('destination_id')->constrained('destinations')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->longText('intro_text')->nullable();
            $table->timestamps();

            $table->unique(['destination_id', 'language_id']);
            $table->unique(['language_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('destination_translations');
        Schema::dropIfExists('destinations');
        Schema::dropIfExists('country_translations');
        Schema::dropIfExists('countries');
    }
};
