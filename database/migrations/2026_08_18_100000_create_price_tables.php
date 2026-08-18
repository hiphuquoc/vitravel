<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Bảng giá chi tiết đa chiều: ngày/khoảng/năm × tuỳ chọn × đối tượng khách × khuyến mãi.
 * Polymorphic trên packages (tour|cruise) và services — sẵn sàng quote booking.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('price_guest_types', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->string('code', 64);
            $table->unsignedTinyInteger('age_min')->nullable();
            $table->unsignedTinyInteger('age_max')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['project_id', 'code'], 'price_guest_types_project_code_unique');
        });

        Schema::create('price_guest_type_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->foreignId('price_guest_type_id')->constrained('price_guest_types')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->string('description')->nullable();
            $table->timestamps();

            $table->unique(['price_guest_type_id', 'language_id'], 'price_guest_type_trans_unique');
        });

        Schema::create('price_tables', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->string('priceable_type', 32);
            $table->unsignedBigInteger('priceable_id');
            $table->string('currency', 3)->default('VND');
            $table->string('unit', 32)->default('per_person');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['priceable_type', 'priceable_id'], 'price_tables_priceable_unique');
            $table->index(['project_id', 'priceable_type']);
        });

        Schema::create('price_variants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->foreignId('price_table_id')->constrained('price_tables')->cascadeOnDelete();
            $table->string('code', 64);
            $table->string('source', 32)->default('custom'); // custom|cabin|service_option
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['price_table_id', 'code'], 'price_variants_table_code_unique');
            $table->index(['price_table_id', 'sort']);
        });

        Schema::create('price_variant_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->foreignId('price_variant_id')->constrained('price_variants')->cascadeOnDelete();
            $table->foreignId('language_id')->constrained('languages')->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->timestamps();

            $table->unique(['price_variant_id', 'language_id'], 'price_variant_trans_unique');
        });

        Schema::create('price_periods', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->foreignId('price_table_id')->constrained('price_tables')->cascadeOnDelete();
            $table->string('kind', 16); // date|range|year
            $table->date('starts_on');
            $table->date('ends_on');
            $table->unsignedSmallInteger('year')->nullable();
            $table->string('label')->nullable();
            $table->boolean('is_promo')->default(false);
            $table->unsignedSmallInteger('priority')->default(0);
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['price_table_id', 'starts_on', 'ends_on']);
            $table->index(['price_table_id', 'is_promo', 'priority']);
        });

        Schema::create('price_rates', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->nullable()->index();
            $table->foreignId('period_id')->constrained('price_periods')->cascadeOnDelete();
            $table->foreignId('variant_id')->constrained('price_variants')->cascadeOnDelete();
            $table->foreignId('guest_type_id')->constrained('price_guest_types')->cascadeOnDelete();
            $table->decimal('amount', 12, 2);
            $table->decimal('compare_at_amount', 12, 2)->nullable();
            $table->unsignedSmallInteger('min_qty')->nullable();
            $table->unsignedSmallInteger('max_qty')->nullable();
            $table->timestamps();

            $table->unique(['period_id', 'variant_id', 'guest_type_id'], 'price_rates_cell_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_rates');
        Schema::dropIfExists('price_periods');
        Schema::dropIfExists('price_variant_translations');
        Schema::dropIfExists('price_variants');
        Schema::dropIfExists('price_tables');
        Schema::dropIfExists('price_guest_type_translations');
        Schema::dropIfExists('price_guest_types');
    }
};
