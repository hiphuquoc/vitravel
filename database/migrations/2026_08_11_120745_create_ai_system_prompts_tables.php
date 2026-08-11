<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_system_prompts', function (Blueprint $table) {
            $table->id();
            $table->string('key', 64)->unique();
            $table->string('name');
            $table->string('category', 64)->default('general')->index();
            $table->text('description')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->longText('system');
            $table->longText('user');
            $table->string('output_format', 32)->default('json');
            /** @var string JSON list of template variable names */
            $table->json('variables')->nullable();
            /** @var string JSON list of entity_type hints */
            $table->json('entity_types')->nullable();
            $table->boolean('is_active')->default(true)->index();
            /** true = admin đã sửa — sync từ file seed sẽ bỏ qua trừ khi --force */
            $table->boolean('is_customized')->default(false);
            $table->timestamp('seeded_at')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable()->index();
            $table->timestamps();
        });

        Schema::create('ai_usage_logs', function (Blueprint $table) {
            $table->id();
            $table->string('prompt_key', 64)->index();
            $table->string('feature', 64)->nullable()->index();
            $table->string('entity_type', 64)->nullable()->index();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('provider', 32)->nullable();
            $table->string('model', 128)->nullable();
            $table->unsignedInteger('latency_ms')->nullable();
            $table->boolean('success')->default(true);
            $table->string('error_code', 64)->nullable();
            $table->text('error_message')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();

            $table->index(['created_at', 'prompt_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_usage_logs');
        Schema::dropIfExists('ai_system_prompts');
    }
};
