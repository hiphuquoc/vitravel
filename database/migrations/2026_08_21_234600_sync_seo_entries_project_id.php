<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SeoEntry;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Đồng bộ project_id của toàn bộ SeoEntry khớp với reference model
        $entries = SeoEntry::withoutGlobalScope('project')->get();
        foreach ($entries as $e) {
            if ($e->reference_type && $e->reference_id) {
                $ref = $e->reference()->withoutGlobalScope('project')->first();
                if ($ref && isset($ref->project_id) && (int) $e->project_id !== (int) $ref->project_id) {
                    $e->update(['project_id' => $ref->project_id]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
