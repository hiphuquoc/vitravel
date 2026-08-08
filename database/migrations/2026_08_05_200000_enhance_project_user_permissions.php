<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('project_user') && ! Schema::hasColumn('project_user', 'permissions')) {
            Schema::table('project_user', function (Blueprint $table) {
                $table->json('permissions')->nullable()->after('role');
            });
        }

        // Normalize legacy editor accounts → staff (project-scoped).
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('role', 'editor')->update(['role' => 'staff']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('project_user') && Schema::hasColumn('project_user', 'permissions')) {
            Schema::table('project_user', function (Blueprint $table) {
                $table->dropColumn('permissions');
            });
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'role')) {
            DB::table('users')->where('role', 'staff')->update(['role' => 'editor']);
        }
    }
};
