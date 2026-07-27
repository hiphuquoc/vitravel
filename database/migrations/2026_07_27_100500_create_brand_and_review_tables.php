<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Reviews, brand/About, team, offices, gallery, video, USP, hero, static pages.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->string('reviewable_type', 64); // package|company
            $table->unsignedBigInteger('reviewable_id')->nullable();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('author_name');
            $table->string('author_country')->nullable();
            $table->string('author_country_code', 8)->nullable();
            $table->foreignId('avatar_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedTinyInteger('rating')->default(5);
            $table->date('reviewed_on')->nullable();
            $table->string('question_title')->nullable();
            $table->longText('content');
            $table->boolean('is_featured')->default(false);
            $table->boolean('show_on_home')->default(false);
            $table->string('status', 20)->default('published');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['reviewable_type', 'reviewable_id', 'status'], 'reviews_morph_status_idx');
            $table->index(['show_on_home', 'status']);
            $table->index('rating');
        });

        Schema::create('review_platforms', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique(); // tripadvisor|google|trustpilot
            $table->string('name');
            $table->decimal('rating', 3, 2)->nullable();
            $table->unsignedInteger('review_count')->nullable();
            $table->string('url')->nullable();
            $table->foreignId('logo_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('company_profiles', function (Blueprint $table) {
            $table->id();
            $table->string('license_number')->nullable();
            $table->foreignId('intro_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('mission_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('vision_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('policy_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('company_profile_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_profile_id')->constrained('company_profiles')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('greeting_title')->nullable();
            $table->longText('intro_text')->nullable();
            $table->string('mission_title')->nullable();
            $table->longText('mission_text')->nullable();
            $table->string('vision_title')->nullable();
            $table->longText('vision_text')->nullable();
            $table->string('sales_policy_title')->nullable();
            $table->longText('sales_policy_content')->nullable();
            $table->timestamps();

            $table->unique(['company_profile_id', 'language_id'], 'company_profile_trans_unique');
        });

        Schema::create('company_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('company_value_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_value_id')->constrained('company_values')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['company_value_id', 'language_id'], 'company_value_trans_unique');
        });

        Schema::create('reasons_to_choose_us', function (Blueprint $table) {
            $table->id();
            $table->foreignId('section_image_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('reason_to_choose_us_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reason_to_choose_us_id')->constrained('reasons_to_choose_us')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['reason_to_choose_us_id', 'language_id'], 'reason_trans_unique');
        });

        Schema::create('reference_persons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('photo_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('skype')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('usps', function (Blueprint $table) {
            $table->id();
            $table->string('icon')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('usp_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usp_id')->constrained('usps')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['usp_id', 'language_id']);
        });

        Schema::create('hero_pills', function (Blueprint $table) {
            $table->id();
            $table->string('target_url')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('hero_pill_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hero_pill_id')->constrained('hero_pills')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('label');
            $table->timestamps();

            $table->unique(['hero_pill_id', 'language_id']);
        });

        Schema::create('team_members', function (Blueprint $table) {
            $table->id();
            $table->string('department', 64)->nullable();
            $table->foreignId('avatar_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('show_on_home')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('team_member_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_member_id')->constrained('team_members')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('role')->nullable();
            $table->text('short_bio')->nullable();
            $table->timestamps();

            $table->unique(['team_member_id', 'language_id']);
        });

        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('phone', 50)->nullable();
            $table->string('whatsapp', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('map_embed_url', 1024)->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('office_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('office_id')->constrained('offices')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('city_label');
            $table->string('address_line')->nullable();
            $table->timestamps();

            $table->unique(['office_id', 'language_id']);
        });

        Schema::create('experience_albums', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->foreignId('cover_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('customer_name')->nullable();
            $table->date('trip_date')->nullable();
            $table->unsignedInteger('photo_count')->default(0);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('status', 20)->default('published');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('experience_album_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_album_id')->constrained('experience_albums')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['experience_album_id', 'language_id'], 'exp_album_trans_unique');
        });

        Schema::create('experience_album_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_album_id')->constrained('experience_albums')->cascadeOnDelete();
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['experience_album_id', 'sort']);
        });

        Schema::create('experience_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->nullable()->constrained('countries')->nullOnDelete();
            $table->string('youtube_id', 32)->nullable();
            $table->string('video_url')->nullable();
            $table->foreignId('thumbnail_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->boolean('show_on_home')->default(false);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->string('status', 20)->default('published');
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('experience_video_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('experience_video_id')->constrained('experience_videos')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['experience_video_id', 'language_id'], 'exp_video_trans_unique');
        });

        Schema::create('static_pages', function (Blueprint $table) {
            $table->id();
            $table->string('template', 64)->nullable();
            $table->foreignId('banner_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('status', 20)->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('static_page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('static_page_id')->constrained('static_pages')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title');
            $table->longText('body')->nullable();
            $table->timestamps();

            $table->unique(['static_page_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('static_page_translations');
        Schema::dropIfExists('static_pages');
        Schema::dropIfExists('experience_video_translations');
        Schema::dropIfExists('experience_videos');
        Schema::dropIfExists('experience_album_photos');
        Schema::dropIfExists('experience_album_translations');
        Schema::dropIfExists('experience_albums');
        Schema::dropIfExists('office_translations');
        Schema::dropIfExists('offices');
        Schema::dropIfExists('team_member_translations');
        Schema::dropIfExists('team_members');
        Schema::dropIfExists('hero_pill_translations');
        Schema::dropIfExists('hero_pills');
        Schema::dropIfExists('usp_translations');
        Schema::dropIfExists('usps');
        Schema::dropIfExists('reference_persons');
        Schema::dropIfExists('reason_to_choose_us_translations');
        Schema::dropIfExists('reasons_to_choose_us');
        Schema::dropIfExists('company_value_translations');
        Schema::dropIfExists('company_values');
        Schema::dropIfExists('company_profile_translations');
        Schema::dropIfExists('company_profiles');
        Schema::dropIfExists('review_platforms');
        Schema::dropIfExists('reviews');
    }
};
