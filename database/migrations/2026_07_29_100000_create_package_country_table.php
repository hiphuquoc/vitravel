<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('package_country', function (Blueprint $table) {
            $table->id();
            $table->foreignId('package_id')->constrained('packages')->cascadeOnDelete();
            $table->foreignId('country_id')->constrained('countries')->cascadeOnDelete();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->unique(['package_id', 'country_id']);
            $table->index(['country_id', 'package_id']);
        });

        // Backfill: mỗi gói thuộc ít nhất quốc gia chính (country_id)
        DB::table('packages')
            ->select(['id', 'country_id'])
            ->orderBy('id')
            ->chunkById(200, function ($rows) {
                $insert = [];
                foreach ($rows as $row) {
                    if (! $row->country_id) {
                        continue;
                    }
                    $insert[] = [
                        'package_id' => $row->id,
                        'country_id' => $row->country_id,
                        'sort' => 0,
                    ];
                }
                if ($insert !== []) {
                    DB::table('package_country')->insertOrIgnore($insert);
                }
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_country');
    }
};
