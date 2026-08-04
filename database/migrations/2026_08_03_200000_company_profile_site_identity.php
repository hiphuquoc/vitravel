<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Đưa toàn bộ config/company.php lên company_profiles (seed + admin chỉnh).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->string('name')->nullable()->after('id');
            $table->string('legal_name')->nullable()->after('name');
            $table->string('tagline')->nullable()->after('legal_name');
            $table->string('contact_zalo', 40)->nullable()->after('contact_whatsapp');
            $table->string('hotline_label', 80)->nullable()->after('contact_zalo');
            $table->json('address')->nullable()->after('slogan');
            $table->json('social_links')->nullable()->after('address');
            $table->json('schema_settings')->nullable()->after('social_links');
            $table->string('footer_copyright')->nullable()->after('schema_settings');
            $table->boolean('show_dmca_badge')->default(true)->after('footer_copyright');
        });
    }

    public function down(): void
    {
        Schema::table('company_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'legal_name',
                'tagline',
                'contact_zalo',
                'hotline_label',
                'address',
                'social_links',
                'schema_settings',
                'footer_copyright',
                'show_dmca_badge',
            ]);
        });
    }
};
