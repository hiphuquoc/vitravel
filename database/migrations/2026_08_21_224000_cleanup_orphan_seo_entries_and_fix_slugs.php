<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\SeoEntry;
use App\Models\SeoEntryTranslation;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Dọn dẹp các bản ghi SEO mồ côi (reference_type / reference_id không còn tồn tại trong model đích)
        $orphanEntries = SeoEntry::withoutGlobalScope('project')->get()->filter(function ($e) {
            if ($e->reference_type && $e->reference_id) {
                $ref = $e->reference()->withoutGlobalScope('project')->first();
                return $ref === null;
            }
            return false;
        });

        foreach ($orphanEntries as $entry) {
            SeoEntryTranslation::withoutGlobalScope('project')->where('seo_entry_id', $entry->id)->delete();
            $entry->delete();
        }

        // 2. Cập nhật & chuẩn hoá các URL category Phú Quốc cho đồng bộ
        // Đảm bảo các danh mục của Phú Quốc có slug_full chuẩn
        $corrections = [
            // [category_id, correct_slug, slug_full_hub_pattern]
            [165, 'vinwonders', '/ve-vui-choi-trai-nghiem-phu-quoc/vinwonders'],
            [166, 'safari-phu-quoc', '/ve-vui-choi-trai-nghiem-phu-quoc/safari-phu-quoc'],
            [167, 'tour-4-dao', '/ve-vui-choi-trai-nghiem-phu-quoc/tour-4-dao'],
            [168, 'cau-muc-dem', '/ve-vui-choi-trai-nghiem-phu-quoc/cau-muc-dem'],
            [169, 'sunset-cruise', '/ve-vui-choi-trai-nghiem-phu-quoc/sunset-cruise'],
            [170, 'vuon-tieu', '/ve-vui-choi-trai-nghiem-phu-quoc/vuon-tieu'],
            [171, 'nha-thung-nuoc-mam', '/ve-vui-choi-trai-nghiem-phu-quoc/nha-thung-nuoc-mam'],
            [172, 'spa-phu-quoc', '/ve-vui-choi-trai-nghiem-phu-quoc/spa-phu-quoc'],
            [173, 'thue-xe-may', '/dich-vu-du-lich-phu-quoc/thue-xe-may'],
            [174, 'xe-rieng-tren-dao', '/dich-vu-du-lich-phu-quoc/xe-rieng-tren-dao'],
            [175, 'gui-hanh-ly', '/dich-vu-du-lich-phu-quoc/gui-hanh-ly'],
            [176, 'y-te-cap-cuu', '/dich-vu-du-lich-phu-quoc/y-te-cap-cuu'],
            [177, 'sim-esim', '/dich-vu-du-lich-phu-quoc/sim-esim'],
            [159, 'resort-bai-truong-duong-dong', '/khach-san-resort-phu-quoc/resort-bai-truong-duong-dong'],
            [160, 'resort-bai-sao', '/khach-san-resort-phu-quoc/resort-bai-sao'],
            [161, 'resort-ong-lang', '/khach-san-resort-phu-quoc/resort-ong-lang'],
            [162, 'vinpearl-resort', '/khach-san-resort-phu-quoc/vinpearl-resort'],
            [163, 'resort-bai-khem', '/khach-san-resort-phu-quoc/resort-bai-khem'],
            [164, 'homestay-phu-quoc', '/khach-san-resort-phu-quoc/homestay-phu-quoc'],
        ];

        foreach ($corrections as [$catId, $slug, $slugFull]) {
            $cat = \App\Models\ServiceCategory::withoutGlobalScope('project')->find($catId);
            if ($cat) {
                $seo = $cat->seoEntry()->withoutGlobalScope('project')->first();
                if ($seo) {
                    $trans = $seo->translations()->withoutGlobalScope('project')->where('language_id', 1)->first();
                    if ($trans) {
                        $trans->update([
                            'slug' => $slug,
                            'slug_full' => $slugFull,
                            'status' => 'published',
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse needed for cleanup
    }
};
