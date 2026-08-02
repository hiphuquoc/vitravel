<?php

/**
 * Reset admin password + đảm bảo bảng admin_api_tokens.
 * Usage: php scripts/reset-admin-password.php [password]
 */
declare(strict_types=1);

use App\Models\User;
use App\Support\ProjectSeed;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

$password = $argv[1] ?? '111111';

if (! Schema::hasTable('admin_api_tokens')) {
    echo "Creating admin_api_tokens…\n";
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
}

try {
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output();
} catch (Throwable $e) {
    echo 'Migrate note: '.$e->getMessage()."\n";
}

$admin = ProjectSeed::meta()['admin'] ?? [];
$email = $admin['email'] ?? 'admin@vitravel.dev';
$name = $admin['name'] ?? 'Admin ViTravel';

$user = User::query()->updateOrCreate(
    ['email' => $email],
    [
        'name' => $name,
        'password' => $password,
        'role' => 'admin',
        'is_active' => true,
    ]
);

echo "OK — login: {$user->email} / {$password}\n";
echo "Table admin_api_tokens: ".(Schema::hasTable('admin_api_tokens') ? 'yes' : 'NO')."\n";
