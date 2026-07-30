<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('team_members', function (Blueprint $table) {
            $table->string('phone', 50)->nullable()->after('department');
            $table->string('email')->nullable()->after('phone');
            $table->string('area')->nullable()->after('email');
            $table->unsignedInteger('years_experience')->nullable()->after('area');
            $table->json('languages')->nullable()->after('years_experience');
            $table->unsignedInteger('stat_clients')->default(0)->after('languages');
            $table->unsignedInteger('stat_tours')->default(0)->after('stat_clients');
            $table->unsignedInteger('stat_awards')->default(0)->after('stat_tours');
            $table->boolean('is_verified')->default(true)->after('stat_awards');
        });

        Schema::table('team_member_translations', function (Blueprint $table) {
            $table->longText('bio_html')->nullable()->after('short_bio');
        });

        Schema::create('team_member_achievements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_member_id')->constrained('team_members')->cascadeOnDelete();
            $table->text('content');
            $table->unsignedSmallInteger('ordering')->default(0);
            $table->timestamps();
        });

        Schema::create('team_member_skills', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_member_id')->constrained('team_members')->cascadeOnDelete();
            $table->string('skill');
            $table->unsignedTinyInteger('percent')->default(0);
            $table->unsignedSmallInteger('ordering')->default(0);
            $table->timestamps();
        });

        Schema::create('team_member_experiences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_member_id')->constrained('team_members')->cascadeOnDelete();
            $table->string('title');
            $table->string('company')->nullable();
            $table->unsignedSmallInteger('ordering')->default(0);
            $table->timestamps();
        });

        Schema::create('team_member_experience_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_member_experience_id')->constrained('team_member_experiences')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('team_member_degrees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_member_id')->constrained('team_members')->cascadeOnDelete();
            $table->string('title');
            $table->string('school')->nullable();
            $table->unsignedSmallInteger('ordering')->default(0);
            $table->timestamps();
        });

        Schema::create('team_member_degree_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_member_degree_id')->constrained('team_member_degrees')->cascadeOnDelete();
            $table->text('content');
            $table->timestamps();
        });

        Schema::create('team_member_activity_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('team_member_id')->constrained('team_members')->cascadeOnDelete();
            $table->foreignId('media_id')->nullable()->constrained('media')->nullOnDelete();
            $table->unsignedSmallInteger('ordering')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('team_member_activity_images');
        Schema::dropIfExists('team_member_degree_items');
        Schema::dropIfExists('team_member_degrees');
        Schema::dropIfExists('team_member_experience_items');
        Schema::dropIfExists('team_member_experiences');
        Schema::dropIfExists('team_member_skills');
        Schema::dropIfExists('team_member_achievements');

        Schema::table('team_member_translations', function (Blueprint $table) {
            $table->dropColumn('bio_html');
        });

        Schema::table('team_members', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'email', 'area', 'years_experience', 'languages',
                'stat_clients', 'stat_tours', 'stat_awards', 'is_verified',
            ]);
        });
    }
};
