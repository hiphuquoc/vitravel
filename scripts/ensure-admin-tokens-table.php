<?php

/**
 * Tạo bảng admin_api_tokens ngay (không cần artisan nếu migrate kẹt).
 * Usage: php scripts/ensure-admin-tokens-table.php
 */
declare(strict_types=1);

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

echo 'DB: '.config('database.default').' / '.config('database.connections.'.config('database.default').'.database')."\n";

if (Schema::hasTable('admin_api_tokens')) {
    echo "admin_api_tokens already exists\n";
} else {
    Schema::create('admin_api_tokens', function (Blueprint $table) {
        $table->id();
        $table->unsignedBigInteger('user_id');
        $table->string('name', 191)->default('admin-console');
        $table->string('token', 64)->unique();
        $table->timestamp('last_used_at')->nullable();
        $table->timestamp('expires_at')->nullable();
        $table->timestamps();
        $table->index('user_id');
        $table->index(['user_id', 'name']);
    });
    echo "Created admin_api_tokens\n";
}

// smoke insert/delete
try {
    $id = DB::table('admin_api_tokens')->insertGetId([
        'user_id' => 1,
        'name' => '_smoke',
        'token' => hash('sha256', 'smoke-'.microtime(true)),
        'created_at' => now(),
        'updated_at' => now(),
    ]);
    DB::table('admin_api_tokens')->where('id', $id)->delete();
    echo "Smoke insert OK\n";
} catch (Throwable $e) {
    echo 'Smoke FAILED: '.$e->getMessage()."\n";
    exit(1);
}

echo "DONE\n";
