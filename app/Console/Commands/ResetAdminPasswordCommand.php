<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Support\ProjectSeed;
use Illuminate\Console\Command;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ResetAdminPasswordCommand extends Command
{
    protected $signature = 'admin:reset-password {password=111111}';

    protected $description = 'Reset mật khẩu admin console + đảm bảo bảng admin_api_tokens';

    public function handle(): int
    {
        if (! Schema::hasTable('admin_api_tokens')) {
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
            $this->info('Created table admin_api_tokens');
        }

        $this->call('migrate', ['--force' => true]);

        $adminMeta = ProjectSeed::meta()['admin'] ?? [];
        $email = $adminMeta['email'] ?? 'admin@vitravel.dev';
        $name = $adminMeta['name'] ?? 'Admin ViTravel';
        $password = (string) $this->argument('password');

        $user = User::query()->updateOrCreate(
            ['email' => $email],
            [
                'name' => $name,
                'password' => $password,
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        $this->info("OK — {$user->email} / {$password}");

        return self::SUCCESS;
    }
}
