<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mở rộng độ dài cột code trong service_options và services lên 191 ký tự
        Schema::table('service_options', function (Blueprint $table) {
            $table->string('code', 191)->nullable()->change();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('code', 191)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_options', function (Blueprint $table) {
            $table->string('code', 64)->nullable()->change();
        });

        Schema::table('services', function (Blueprint $table) {
            $table->string('code', 64)->nullable()->change();
        });
    }
};
