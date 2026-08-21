<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\SeoRedirect;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Xóa các bản ghi redirect lỗi thời trỏ tới các URL không còn tồn tại
        SeoRedirect::whereIn('id', [475, 491, 499])->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
