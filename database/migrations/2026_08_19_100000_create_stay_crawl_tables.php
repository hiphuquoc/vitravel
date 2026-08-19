<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stay_crawl_sources', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->string('host', 191);
            $table->string('user_agent', 255)->nullable();
            $table->unsignedInteger('delay_ms')->default(2500);
            $table->boolean('respect_robots')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'host']);
        });

        Schema::create('stay_crawl_jobs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->foreignId('source_id')->nullable()->constrained('stay_crawl_sources')->nullOnDelete();
            $table->text('list_url');
            $table->string('canonical_url', 500)->nullable();
            $table->unsignedBigInteger('service_category_id')->nullable()->index();
            $table->string('status', 32)->default('pending')->index();
            $table->unsignedInteger('pages_crawled')->default(0);
            $table->unsignedInteger('items_found')->default(0);
            $table->text('error')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('stay_crawl_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->foreignId('job_id')->nullable()->constrained('stay_crawl_jobs')->nullOnDelete();
            $table->text('source_url');
            $table->string('canonical_url', 500);
            $table->text('list_url')->nullable();
            $table->string('status', 32)->default('queued')->index();
            $table->unsignedSmallInteger('http_status')->nullable();
            $table->string('blocked_reason', 191)->nullable();
            $table->unsignedSmallInteger('extractor_version')->default(1);
            $table->longText('raw_html')->nullable();
            $table->longText('extracted_html')->nullable();
            $table->json('raw_json')->nullable();
            $table->json('ai_json')->nullable();
            $table->unsignedBigInteger('service_id')->nullable()->index();
            $table->text('error')->nullable();
            $table->timestamp('crawled_at')->nullable();
            $table->timestamp('ai_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'canonical_url']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stay_crawl_items');
        Schema::dropIfExists('stay_crawl_jobs');
        Schema::dropIfExists('stay_crawl_sources');
    }
};
