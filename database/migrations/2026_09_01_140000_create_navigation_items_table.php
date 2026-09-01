<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('navigation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('zone', 32); // main | more | cta
            $table->string('kind', 48); // tours_menu, cruise_menu, service_cluster, route_link, heading, blog_menu, cta_link
            $table->string('item_key', 64); // stable id: tours, cruise, stay, about, ...
            $table->string('reference', 64)->nullable(); // cluster code | route name
            $table->json('config')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['project_id', 'zone', 'item_key']);
            $table->index(['project_id', 'zone', 'is_active', 'sort']);
        });

        Schema::create('navigation_item_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('navigation_item_id')->constrained('navigation_items')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('lead_label')->nullable();
            $table->text('meta')->nullable();
            $table->timestamps();

            $table->unique(['navigation_item_id', 'language_id'], 'nav_item_lang_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('navigation_item_translations');
        Schema::dropIfExists('navigation_items');
    }
};
