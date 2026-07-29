<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('redirect_info', function (Blueprint $table) {
            $table->id();
            $table->string('url_old', 512);
            $table->string('url_new', 512);
            $table->index('url_old');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirect_info');
    }
};
