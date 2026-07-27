<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hero_pills', function (Blueprint $table) {
            $table->foreignId('tour_category_id')->nullable()->after('id')->constrained('tour_categories')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->after('tour_category_id')->constrained('countries')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('hero_pills', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tour_category_id');
            $table->dropConstrainedForeignId('country_id');
        });
    }
};
