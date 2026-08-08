<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-project (runtime tenancy) foundation:
 * projects / project_domains / project_user + project_id on root content tables.
 */
return new class extends Migration
{
    /** @var list<string> */
    private array $rootTables = [
        'countries',
        'destinations',
        'travel_styles',
        'tour_categories',
        'blog_categories',
        'content_type_tags',
        'keyword_tags',
        'cruise_types',
        'packages',
        'service_categories',
        'services',
        'articles',
        'comments',
        'company_profiles',
        'company_values',
        'reasons_to_choose_us',
        'reference_persons',
        'usps',
        'hero_pills',
        'offices',
        'team_members',
        'static_pages',
        'reviews',
        'review_platforms',
        'experience_albums',
        'experience_videos',
        'faqs',
        'home_slides',
        'home_sections',
        'home_featured_tours',
        'home_featured_cruises',
        'home_featured_countries',
        'home_featured_review_platforms',
        'seo_entries',
        'redirect_info',
        'media',
        'quick_inquiry_leads',
        'custom_tour_requests',
        'contact_messages',
    ];

    /** @var list<string> */
    private array $translationTables = [
        'seo_entry_translations',
        'country_translations',
        'destination_translations',
        'travel_style_translations',
        'tour_category_translations',
        'blog_category_translations',
        'content_type_tag_translations',
        'keyword_tag_translations',
    ];

    public function up(): void
    {
        if (! Schema::hasTable('projects')) {
            Schema::create('projects', function (Blueprint $table) {
                $table->id();
                $table->string('code', 64)->unique();
                $table->string('name');
                $table->string('seed_profile')->nullable();
                $table->boolean('is_active')->default(true);
                $table->json('config')->nullable();
                $table->string('primary_domain')->nullable();
                $table->string('media_prefix')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('project_domains')) {
            Schema::create('project_domains', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->string('domain', 191)->unique();
                $table->boolean('is_primary')->default(false);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('project_user')) {
            Schema::create('project_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->string('role')->default('admin');
                $table->timestamps();
                $table->unique(['project_id', 'user_id']);
            });
        }

        foreach (array_merge($this->rootTables, $this->translationTables) as $table) {
            $this->addProjectIdColumn($table);
        }

        $this->rewriteUniques();
    }

    public function down(): void
    {
        $this->restoreUniques();

        foreach (array_merge($this->rootTables, $this->translationTables) as $table) {
            $this->dropProjectIdColumn($table);
        }

        Schema::dropIfExists('project_user');
        Schema::dropIfExists('project_domains');
        Schema::dropIfExists('projects');
    }

    private function addProjectIdColumn(string $table): void
    {
        if (! Schema::hasTable($table) || Schema::hasColumn($table, 'project_id')) {
            return;
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->unsignedBigInteger('project_id')->nullable()->index();
            $blueprint->foreign('project_id')->references('id')->on('projects')->nullOnDelete();
        });
    }

    private function dropProjectIdColumn(string $table): void
    {
        if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'project_id')) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropForeign(['project_id']);
            });
        } catch (\Throwable) {
            // ignore missing FK
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) {
                $blueprint->dropIndex(['project_id']);
            });
        } catch (\Throwable) {
            // ignore missing index
        }

        Schema::table($table, function (Blueprint $blueprint) {
            $blueprint->dropColumn('project_id');
        });
    }

    private function rewriteUniques(): void
    {
        // seo_entry_translations: prefix unique on slug_full
        $this->dropIndexIfExists('seo_entry_translations', 'seo_trans_lang_slug_full_unique');
        if (Schema::hasTable('seo_entry_translations') && Schema::hasColumn('seo_entry_translations', 'project_id')) {
            try {
                DB::statement(
                    'CREATE UNIQUE INDEX seo_trans_proj_lang_slug_full_unique ON seo_entry_translations (project_id, language_id, slug_full(191))'
                );
            } catch (\Throwable $e) {
                report($e);
            }
        }

        $this->swapUnique('countries', ['code'], ['project_id', 'code'], 'countries_code_unique', 'countries_project_id_code_unique');
        $this->swapUnique(
            'country_translations',
            ['language_id', 'slug'],
            ['project_id', 'language_id', 'slug'],
            'country_translations_language_id_slug_unique',
            'country_trans_proj_lang_slug_unique'
        );
        $this->swapUnique(
            'destination_translations',
            ['language_id', 'slug'],
            ['project_id', 'language_id', 'slug'],
            'destination_translations_language_id_slug_unique',
            'dest_trans_proj_lang_slug_unique'
        );
        $this->swapUnique(
            'travel_styles',
            ['code'],
            ['project_id', 'code'],
            'travel_styles_code_unique',
            'travel_styles_project_id_code_unique'
        );
        $this->swapUnique(
            'travel_style_translations',
            ['language_id', 'slug'],
            ['project_id', 'language_id', 'slug'],
            'travel_style_lang_slug_unique',
            'travel_style_trans_proj_lang_slug_unique'
        );
        $this->swapUnique(
            'tour_category_translations',
            ['language_id', 'slug'],
            ['project_id', 'language_id', 'slug'],
            'tour_cat_lang_slug_unique',
            'tour_cat_trans_proj_lang_slug_unique'
        );
        $this->swapUnique(
            'blog_category_translations',
            ['language_id', 'slug'],
            ['project_id', 'language_id', 'slug'],
            'blog_cat_lang_slug_unique',
            'blog_cat_trans_proj_lang_slug_unique'
        );
        $this->swapUnique(
            'content_type_tag_translations',
            ['language_id', 'slug'],
            ['project_id', 'language_id', 'slug'],
            'content_tag_lang_slug_unique',
            'content_tag_trans_proj_lang_slug_unique'
        );
        $this->swapUnique(
            'keyword_tag_translations',
            ['language_id', 'slug'],
            ['project_id', 'language_id', 'slug'],
            'keyword_tag_lang_slug_unique',
            'keyword_tag_trans_proj_lang_slug_unique'
        );
        $this->swapUnique(
            'cruise_types',
            ['slug'],
            ['project_id', 'slug'],
            'cruise_types_slug_unique',
            'cruise_types_project_id_slug_unique'
        );
        $this->swapUnique(
            'service_categories',
            ['cluster', 'slug'],
            ['project_id', 'cluster', 'slug'],
            'service_categories_cluster_slug_unique',
            'service_categories_project_id_cluster_slug_unique'
        );
        $this->swapUnique(
            'home_sections',
            ['key'],
            ['project_id', 'key'],
            'home_sections_key_unique',
            'home_sections_project_id_key_unique'
        );
        $this->swapUnique(
            'content_type_tags',
            ['code'],
            ['project_id', 'code'],
            'content_type_tags_code_unique',
            'content_type_tags_project_id_code_unique'
        );
        $this->swapUnique(
            'review_platforms',
            ['code'],
            ['project_id', 'code'],
            'review_platforms_code_unique',
            'review_platforms_project_id_code_unique'
        );
    }

    private function restoreUniques(): void
    {
        $this->dropIndexIfExists('seo_entry_translations', 'seo_trans_proj_lang_slug_full_unique');
        if (Schema::hasTable('seo_entry_translations')) {
            try {
                DB::statement(
                    'CREATE UNIQUE INDEX seo_trans_lang_slug_full_unique ON seo_entry_translations (language_id, slug_full(191))'
                );
            } catch (\Throwable) {
                // ignore
            }
        }

        $this->swapUnique('countries', ['project_id', 'code'], ['code'], 'countries_project_id_code_unique', 'countries_code_unique');
        $this->swapUnique(
            'country_translations',
            ['project_id', 'language_id', 'slug'],
            ['language_id', 'slug'],
            'country_trans_proj_lang_slug_unique',
            'country_translations_language_id_slug_unique'
        );
        $this->swapUnique(
            'destination_translations',
            ['project_id', 'language_id', 'slug'],
            ['language_id', 'slug'],
            'dest_trans_proj_lang_slug_unique',
            'destination_translations_language_id_slug_unique'
        );
        $this->swapUnique(
            'travel_styles',
            ['project_id', 'code'],
            ['code'],
            'travel_styles_project_id_code_unique',
            'travel_styles_code_unique'
        );
        $this->swapUnique(
            'travel_style_translations',
            ['project_id', 'language_id', 'slug'],
            ['language_id', 'slug'],
            'travel_style_trans_proj_lang_slug_unique',
            'travel_style_lang_slug_unique'
        );
        $this->swapUnique(
            'tour_category_translations',
            ['project_id', 'language_id', 'slug'],
            ['language_id', 'slug'],
            'tour_cat_trans_proj_lang_slug_unique',
            'tour_cat_lang_slug_unique'
        );
        $this->swapUnique(
            'blog_category_translations',
            ['project_id', 'language_id', 'slug'],
            ['language_id', 'slug'],
            'blog_cat_trans_proj_lang_slug_unique',
            'blog_cat_lang_slug_unique'
        );
        $this->swapUnique(
            'content_type_tag_translations',
            ['project_id', 'language_id', 'slug'],
            ['language_id', 'slug'],
            'content_tag_trans_proj_lang_slug_unique',
            'content_tag_lang_slug_unique'
        );
        $this->swapUnique(
            'keyword_tag_translations',
            ['project_id', 'language_id', 'slug'],
            ['language_id', 'slug'],
            'keyword_tag_trans_proj_lang_slug_unique',
            'keyword_tag_lang_slug_unique'
        );
        $this->swapUnique(
            'cruise_types',
            ['project_id', 'slug'],
            ['slug'],
            'cruise_types_project_id_slug_unique',
            'cruise_types_slug_unique'
        );
        $this->swapUnique(
            'service_categories',
            ['project_id', 'cluster', 'slug'],
            ['cluster', 'slug'],
            'service_categories_project_id_cluster_slug_unique',
            'service_categories_cluster_slug_unique'
        );
        $this->swapUnique(
            'home_sections',
            ['project_id', 'key'],
            ['key'],
            'home_sections_project_id_key_unique',
            'home_sections_key_unique'
        );
        $this->swapUnique(
            'content_type_tags',
            ['project_id', 'code'],
            ['code'],
            'content_type_tags_project_id_code_unique',
            'content_type_tags_code_unique'
        );
        $this->swapUnique(
            'review_platforms',
            ['project_id', 'code'],
            ['code'],
            'review_platforms_project_id_code_unique',
            'review_platforms_code_unique'
        );
    }

    /**
     * @param  list<string>  $oldColumns
     * @param  list<string>  $newColumns
     */
    private function swapUnique(
        string $table,
        array $oldColumns,
        array $newColumns,
        string $oldIndexName,
        string $newIndexName,
    ): void {
        if (! Schema::hasTable($table)) {
            return;
        }

        // MySQL FK on language_id often rides the old (language_id, slug) unique —
        // ensure a dedicated language_id index before dropping that unique.
        if (in_array('language_id', $oldColumns, true)) {
            $this->ensureColumnIndex($table, 'language_id');
        }

        $this->dropIndexIfExists($table, $oldIndexName);

        // Also try dropping by column list (Laravel default names).
        try {
            Schema::table($table, function (Blueprint $blueprint) use ($oldColumns) {
                $blueprint->dropUnique($oldColumns);
            });
        } catch (\Throwable) {
            // ignore — index may already be gone or named differently
        }

        if ($newColumns === []) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($newColumns, $newIndexName) {
                $blueprint->unique($newColumns, $newIndexName);
            });
        } catch (\Throwable $e) {
            report($e);
        }
    }

    private function ensureColumnIndex(string $table, string $column): void
    {
        $indexName = "{$table}_{$column}_index";
        $indexes = collect(DB::select("SHOW INDEX FROM `{$table}`"));
        $hasDedicated = $indexes->contains(
            fn ($row) => $row->Key_name === $indexName
                || ($row->Column_name === $column && (int) $row->Seq_in_index === 1
                    && $indexes->where('Key_name', $row->Key_name)->count() === 1)
        );
        if ($hasDedicated) {
            return;
        }

        try {
            Schema::table($table, function (Blueprint $blueprint) use ($column, $indexName) {
                $blueprint->index([$column], $indexName);
            });
        } catch (\Throwable) {
            try {
                DB::statement("CREATE INDEX `{$indexName}` ON `{$table}` (`{$column}`)");
            } catch (\Throwable) {
                // ignore
            }
        }
    }

    private function dropIndexIfExists(string $table, string $indexName): void
    {
        if (! Schema::hasTable($table)) {
            return;
        }

        try {
            if (method_exists(Schema::getConnection()->getSchemaBuilder(), 'hasIndex')
                && Schema::hasIndex($table, $indexName)) {
                Schema::table($table, function (Blueprint $blueprint) use ($indexName) {
                    $blueprint->dropUnique($indexName);
                });

                return;
            }
        } catch (\Throwable) {
            // fall through to raw DROP
        }

        try {
            DB::statement("ALTER TABLE `{$table}` DROP INDEX `{$indexName}`");
        } catch (\Throwable) {
            // ignore missing index
        }
    }
};
