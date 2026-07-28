<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('experience_videos', function (Blueprint $table) {
            if (! Schema::hasColumn('experience_videos', 'video_media_id')) {
                $table->foreignId('video_media_id')
                    ->nullable()
                    ->after('thumbnail_media_id')
                    ->constrained('media')
                    ->nullOnDelete();
            }

            if (! Schema::hasColumn('experience_videos', 'duration')) {
                $table->string('duration', 16)->nullable()->after('video_url');
            }

            if (! Schema::hasColumn('experience_videos', 'tag')) {
                $table->string('tag', 120)->nullable()->after('duration');
            }
        });
    }

    public function down(): void
    {
        Schema::table('experience_videos', function (Blueprint $table) {
            if (Schema::hasColumn('experience_videos', 'video_media_id')) {
                $table->dropConstrainedForeignId('video_media_id');
            }
            if (Schema::hasColumn('experience_videos', 'tag')) {
                $table->dropColumn('tag');
            }
            if (Schema::hasColumn('experience_videos', 'duration')) {
                $table->dropColumn('duration');
            }
        });
    }
};
