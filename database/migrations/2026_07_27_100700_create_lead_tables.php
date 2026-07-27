<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * 3 lead schemas tách biệt — khớp form UI thật (không gộp chung).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quick_inquiry_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50);
            $table->string('address')->nullable();
            $table->text('message')->nullable();
            $table->string('source_page_url', 1024)->nullable();
            $table->foreignId('related_package_id')->nullable()->constrained('packages')->nullOnDelete();
            $table->string('locale', 10)->nullable();
            $table->string('status', 20)->default('new'); // new|contacted|quoted|closed|spam
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('utm')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('email');
        });

        Schema::create('custom_tour_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedSmallInteger('adults_count');
            $table->unsignedSmallInteger('children_count')->default(0);
            $table->unsignedSmallInteger('infants_count')->default(0);
            $table->string('duration_text');
            $table->date('arrival_date');
            $table->json('countries_to_visit'); // ["viet-nam","thai-lan",...]
            $table->json('accommodation_preference'); // ["4-star","5-star",...]
            $table->decimal('budget_amount', 14, 2);
            $table->string('budget_currency', 3)->default('EUR');
            $table->string('budget_unit', 20); // per-person|per-group
            $table->string('gender', 10); // mr|mrs
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email');
            $table->string('phone', 50);
            $table->string('nationality')->nullable();
            $table->string('city')->nullable();
            $table->text('additional_notes')->nullable();
            $table->string('locale', 10)->nullable();
            $table->string('status', 20)->default('new');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('utm')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('arrival_date');
            $table->index('email');
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('phone', 50);
            $table->string('address')->nullable();
            $table->text('message');
            $table->string('locale', 10)->nullable();
            $table->string('status', 20)->default('new');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('utm')->nullable();
            $table->timestamp('contacted_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index('email');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('custom_tour_requests');
        Schema::dropIfExists('quick_inquiry_leads');
    }
};
