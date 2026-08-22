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
        Schema::table('stay_amenities', function (Blueprint $table) {
            $table->string('group_key', 128)->default('general')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stay_amenities', function (Blueprint $table) {
            $table->string('group_key', 32)->default('general')->change();
        });
    }
};

