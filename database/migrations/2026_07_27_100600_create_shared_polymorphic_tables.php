<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Shared polymorphic: FAQs + media attachments (galleries/covers).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $table) {
            $table->id();
            $table->string('faqable_type', 64);
            $table->unsignedBigInteger('faqable_id');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['faqable_type', 'faqable_id', 'sort'], 'faqs_morph_sort_idx');
        });

        Schema::create('faq_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('faq_id')->constrained('faqs')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('question');
            $table->longText('answer')->nullable();
            $table->timestamps();

            $table->unique(['faq_id', 'language_id']);
        });

        Schema::create('media_attachments', function (Blueprint $table) {
            $table->id();
            $table->string('mediable_type', 64);
            $table->unsignedBigInteger('mediable_id');
            $table->foreignId('media_id')->constrained('media')->cascadeOnDelete();
            $table->string('role', 32)->default('gallery'); // cover|gallery|map|avatar|og|collage
            $table->string('caption')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['mediable_type', 'mediable_id', 'role'], 'media_attach_morph_role_idx');
            $table->index(['mediable_type', 'mediable_id', 'sort'], 'media_attach_morph_sort_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_attachments');
        Schema::dropIfExists('faq_translations');
        Schema::dropIfExists('faqs');
    }
};
