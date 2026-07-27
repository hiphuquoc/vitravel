<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_slides', function (Blueprint $table) {
            $table->id();
            $table->foreignId('image_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->foreignId('image_mobile_media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->string('text_align', 16)->default('center'); // left|center|right
            $table->string('link_url')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'sort']);
        });

        Schema::create('home_slide_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('home_slide_id')->constrained('home_slides')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->string('title_accent')->nullable();
            $table->text('description')->nullable();
            $table->string('button_label')->nullable();
            $table->string('image_alt')->nullable();
            $table->timestamps();

            $table->unique(['home_slide_id', 'language_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_slide_translations');
        Schema::dropIfExists('home_slides');
    }
};
