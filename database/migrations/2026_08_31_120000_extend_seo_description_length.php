<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_entry_translations', function (Blueprint $table) {
            $table->string('seo_description', 400)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('seo_entry_translations', function (Blueprint $table) {
            $table->string('seo_description', 320)->nullable()->change();
        });
    }
};
