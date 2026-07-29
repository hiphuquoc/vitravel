<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Loại du thuyền (Hạ Long / Mekong / Lan Hạ…) — slug URL + banner listing.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cruise_types', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 64)->unique();
            $table->string('name');
            $table->foreignId('banner_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cruise_types');
    }
};
