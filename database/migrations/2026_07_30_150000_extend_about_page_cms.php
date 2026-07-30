<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->foreignId('reasons_image_id')->nullable()->after('policy_image_id')->constrained('media')->nullOnDelete();
            $table->foreignId('about_banner_media_id')->nullable()->after('reasons_image_id')->constrained('media')->nullOnDelete();
        });

        Schema::table('company_profile_translations', function (Blueprint $table) {
            $table->string('about_page_title')->nullable()->after('sales_policy_content');
            $table->string('about_page_subtitle')->nullable()->after('about_page_title');
            $table->string('about_seo_title')->nullable()->after('about_page_subtitle');
            $table->text('about_seo_description')->nullable()->after('about_seo_title');
            $table->string('values_section_title')->nullable()->after('about_seo_description');
            $table->string('values_hub_label')->nullable()->after('values_section_title');
            $table->string('reasons_section_title')->nullable()->after('values_hub_label');
            $table->string('reasons_cta_label')->nullable()->after('reasons_section_title');
            $table->string('reasons_cta_url')->nullable()->after('reasons_cta_label');
            $table->string('sales_policy_cta_label')->nullable()->after('reasons_cta_url');
            $table->string('sales_policy_cta_url')->nullable()->after('sales_policy_cta_label');
            $table->string('reference_section_title')->nullable()->after('sales_policy_cta_url');
            $table->string('reference_section_subtitle')->nullable()->after('reference_section_title');
        });
    }

    public function down(): void
    {
        Schema::table('company_profile_translations', function (Blueprint $table) {
            $table->dropColumn([
                'about_page_title',
                'about_page_subtitle',
                'about_seo_title',
                'about_seo_description',
                'values_section_title',
                'values_hub_label',
                'reasons_section_title',
                'reasons_cta_label',
                'reasons_cta_url',
                'sales_policy_cta_label',
                'sales_policy_cta_url',
                'reference_section_title',
                'reference_section_subtitle',
            ]);
        });

        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('about_banner_media_id');
            $table->dropConstrainedForeignId('reasons_image_id');
        });
    }
};
