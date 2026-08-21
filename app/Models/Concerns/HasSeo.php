<?php

namespace App\Models\Concerns;

use App\Models\SeoEntry;
use App\Models\SeoEntryTranslation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphOne;

trait HasSeo
{
    public static function bootHasSeo(): void
    {
        // Khi xóa vĩnh viễn (forceDelete) hoặc xóa thông thường, tự động dọn dẹp SEO Entry & Translations liên quan
        static::deleting(function (Model $model): void {
            // Nếu là soft delete thông thường và model hỗ trợ restore, giữ SEO hoặc xóa tuỳ nhu cầu.
            // Khi forceDelete hoặc model không có SoftDeletes -> xóa sạch SEO.
            $isForce = method_exists($model, 'isForceDeleting') ? $model->isForceDeleting() : true;
            if ($isForce) {
                $entry = $model->seoEntry()->withoutGlobalScope('project')->first();
                if ($entry) {
                    SeoEntryTranslation::withoutGlobalScope('project')->where('seo_entry_id', $entry->id)->delete();
                    $entry->delete();
                }
            }
        });
    }

    public function seoEntry(): MorphOne
    {
        return $this->morphOne(SeoEntry::class, 'reference');
    }
}
