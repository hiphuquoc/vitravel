<?php

use App\Services\StayCrawl\StayCrawlerMediaFolderMigrator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Di chuyển ảnh crawler khách sạn cũ sang thư mục riêng (multi-project).
     */
    public function up(): void
    {
        app(StayCrawlerMediaFolderMigrator::class)->migrate();
    }

    public function down(): void
    {
        // Không hoàn tác — path cũ stays/gallery|cover có thể đã bị ghi đè.
    }
};
