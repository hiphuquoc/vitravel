<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('home_featured_team_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('team_member_id')->unique()->constrained('team_members')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index('sort');
        });

        Schema::create('home_featured_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('review_id')->unique()->constrained('reviews')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index('sort');
        });

        Schema::create('home_featured_videos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->foreignId('experience_video_id')->unique()->constrained('experience_videos')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index('sort');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('home_featured_videos');
        Schema::dropIfExists('home_featured_reviews');
        Schema::dropIfExists('home_featured_team_members');
    }
};
