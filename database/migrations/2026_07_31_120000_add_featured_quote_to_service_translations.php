<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_translations', function (Blueprint $table) {
            $table->string('featured_quote_text')->nullable()->after('summary');
            $table->string('featured_quote_author')->nullable()->after('featured_quote_text');
        });
    }

    public function down(): void
    {
        Schema::table('service_translations', function (Blueprint $table) {
            $table->dropColumn(['featured_quote_text', 'featured_quote_author']);
        });
    }
};
