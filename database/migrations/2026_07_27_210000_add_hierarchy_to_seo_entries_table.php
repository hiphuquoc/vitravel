<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_entries', function (Blueprint $table) {
            $table->string('type', 64)->nullable()->after('reference_id');
            $table->foreignId('parent_id')->nullable()->after('type')->constrained('seo_entries')->nullOnDelete();
            $table->unsignedTinyInteger('level')->default(1)->after('parent_id');

            $table->index(['type', 'parent_id'], 'seo_entries_type_parent_idx');
        });
    }

    public function down(): void
    {
        Schema::table('seo_entries', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropIndex('seo_entries_type_parent_idx');
            $table->dropColumn(['type', 'parent_id', 'level']);
        });
    }
};
