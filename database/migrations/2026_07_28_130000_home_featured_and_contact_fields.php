<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_featured_countries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->unique()->constrained('countries')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('home_featured_review_platforms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_platform_id')->unique()->constrained('review_platforms')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::table('review_platforms', function (Blueprint $table) {
            $table->text('quote')->nullable()->after('url');
            $table->string('link_label')->nullable()->after('quote');
            $table->boolean('show_on_home')->default(true)->after('is_active');
        });

        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('contact_email')->nullable()->after('license_number');
            $table->string('contact_phone', 40)->nullable()->after('contact_email');
            $table->string('contact_whatsapp', 40)->nullable()->after('contact_phone');
            $table->string('slogan')->nullable()->after('contact_whatsapp');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn(['contact_email', 'contact_phone', 'contact_whatsapp', 'slogan']);
        });

        Schema::table('review_platforms', function (Blueprint $table) {
            $table->dropColumn(['quote', 'link_label', 'show_on_home']);
        });

        Schema::dropIfExists('home_featured_review_platforms');
        Schema::dropIfExists('home_featured_countries');
    }
};
