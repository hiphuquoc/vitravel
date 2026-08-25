<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ManagesCoverImage;
use App\Http\Controllers\Admin\Concerns\ManagesTranslations;
use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\ServiceTranslation;
use App\Services\ServicePurgeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ServiceController extends Controller
{
    use ManagesCoverImage, ManagesTranslations;

    public function list(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString() ?: 'vi';
        $cluster = $request->string('cluster')->toString();
        $clusters = config('services_catalog.clusters', []);

        if ($cluster !== '' && ! isset($clusters[$cluster])) {
            abort(404);
        }

        $query = Service::query()
            ->with([
                'category',
                'country.translations',
                'translations',
                'seoEntry.translations',
                'mediaAttachments.media',
            ])
            ->orderBy('cluster')
            ->orderBy('sort')
            ->orderByDesc('id');

        if ($cluster !== '') {
            $query->forCluster($cluster);
        }

        if ($request->filled('service_category_id')) {
            $query->where('service_category_id', $request->integer('service_category_id'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status')->toString());
        }

        if ($request->filled('search')) {
            $search = $request->string('search')->toString();
            $query->where(function ($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                    ->orWhereHas('translations', fn ($t) => $t->where('title', 'like', "%{$search}%"));
            });
        }

        $services = $query->paginate(20)->withQueryString();

        $categories = ServiceCategory::query()
            ->when($cluster !== '', fn ($q) => $q->forCluster($cluster))
            ->orderBy('sort')
            ->get();

        $title = $cluster !== ''
            ? ('Sản phẩm — '.($clusters[$cluster]['label'] ?? $cluster))
            : 'Sản phẩm dịch vụ';

        return view('admin.service.list', [
            'services' => $services,
            'categories' => $categories,
            'clusters' => $clusters,
            'cluster' => $cluster,
            'locale' => $locale,
            'title' => $title,
        ]);
    }

    public function view(Request $request): View
    {
        $locale = $request->string('language', 'vi')->toString() ?: 'vi';
        $clusters = config('services_catalog.clusters', []);
        $cluster = $request->string('cluster')->toString();

        $id = $request->integer('id');
        $service = $id > 0
            ? Service::query()
                ->with([
                    'categories',
                    'category.seoEntry.translations',
                    'country.translations',
                    'translations',
                    'seoEntry.translations',
                    'seoEntry.parent',
                    'mediaAttachments.media',
                ])
                ->findOrFail($id)
            : null;

        if ($service) {
            $cluster = $service->cluster;
        }

        if ($cluster === '' || ! isset($clusters[$cluster])) {
            $cluster = array_key_first($clusters) ?: 'stay';
        }

        $hubKey = $clusters[$cluster]['hub_key'] ?? null;
        $hubSeo = $hubKey ? $this->seoService()->ensureHub($hubKey, $locale) : null;

        $categories = ServiceCategory::query()
            ->forCluster($cluster)
            ->orderBy('sort')
            ->get();

        $countries = Country::query()->with('translations')->orderBy('sort')->get();
        $translation = $service?->translation($locale);
        $seoTranslation = $service?->seoEntry?->translation($locale);

        $parentTypes = array_values(array_filter(['service_category', $hubKey]));
        $parents = $this->seoService()->parentOptions($parentTypes, null, $cluster);

        $defaultParentId = $service?->seoEntry?->parent_id
            ?: ($service?->category?->seoEntry?->id ?: $hubSeo?->id);

        $clusterOptions = collect($clusters)->mapWithKeys(
            fn (array $cfg, string $key) => [$key => $cfg['label'] ?? $key]
        )->all();

        return view('admin.service.view', [
            'service' => $service,
            'locale' => $locale,
            'language' => $locale,
            'translation' => $translation,
            'seoTranslation' => $seoTranslation,
            'parents' => $parents,
            'defaultParentId' => $defaultParentId,
            'categories' => $categories,
            'countries' => $countries,
            'cluster' => $cluster,
            'clusterOptions' => $clusterOptions,
            'hubKey' => $hubKey,
            'propertyTypeOptions' => config('stay.property_types', []),
            'title' => $service ? 'Chỉnh sửa sản phẩm dịch vụ' : 'Thêm sản phẩm dịch vụ',
        ]);
    }

    public function createAndUpdate(Request $request): RedirectResponse
    {
        if ((int) $request->input('id') <= 0) {
            $request->merge(['id' => null]);
        }

        $this->assertUploadedFileOk($request);

        $clusters = array_keys(config('services_catalog.clusters', []));
        $locale = $request->string('language', 'vi')->toString() ?: 'vi';

        $request->merge([
            'seo_slug' => Str::slug((string) ($request->input('seo_slug') ?: $request->input('title', ''))),
        ]);

        $validated = $request->validate([
            'id' => 'nullable|integer|exists:services,id',
            'cluster' => ['required', 'string', Rule::in($clusters)],
            'service_category_id' => 'nullable|integer|exists:service_categories,id',
            'service_category_ids' => 'nullable|array',
            'service_category_ids.*' => 'integer|exists:service_categories,id',
            'property_types' => 'nullable|array',
            'property_types.*' => 'string|max:64',
            'property_type' => 'nullable|string|max:64',
            'checkin_from' => 'nullable|string|max:32',
            'checkout_until' => 'nullable|string|max:32',
            'address' => 'nullable|string|max:500',
            'country_id' => 'nullable|integer|exists:countries,id',
            'code' => 'nullable|string|max:64',
            'title' => 'required|string|max:255',
            'location_label' => 'nullable|string|max:255',
            'summary' => 'nullable|string|max:5000',
            'content' => 'nullable|string',
            'featured_quote_text' => 'nullable|string|max:255',
            'featured_quote_author' => 'nullable|string|max:255',
            'price_from' => 'nullable|numeric|min:0',
            'currency' => 'nullable|string|size:3',
            'rating' => 'nullable|numeric|min:0|max:5',
            'review_count' => 'nullable|integer|min:0',
            'star_rating' => 'nullable|integer|min:1|max:5',
            'discount_badge' => 'nullable|string|max:100',
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'sort' => 'nullable|integer|min:0',
            'is_featured' => 'nullable|boolean',
            'is_hot_deal' => 'nullable|boolean',
            'seo_slug' => 'nullable|string|max:191',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:320',
            'seo_keywords' => 'nullable|string|max:500',
            'seo_parent_id' => 'nullable|integer|exists:seo_entries,id',
            'rating_aggregate_count' => 'nullable|integer|min:0',
            'rating_aggregate_star' => 'nullable|numeric|min:0|max:5',
            ...$this->coverImageRules(),
        ]);

        $service = DB::transaction(function () use ($request, $validated, $locale) {
            $cluster = $validated['cluster'];
            $hubKey = config("services_catalog.clusters.{$cluster}.hub_key");
            $hubSeo = $hubKey ? $this->seoService()->ensureHub($hubKey, $locale) : null;

            $categoryIds = array_values(array_filter(
                array_map('intval', (array) ($request->input('service_category_ids', [])))
            ));

            $primaryCategoryId = ! empty($validated['service_category_id'])
                ? (int) $validated['service_category_id']
                : ($categoryIds[0] ?? null);

            if ($primaryCategoryId && ! in_array($primaryCategoryId, $categoryIds, true)) {
                array_unshift($categoryIds, $primaryCategoryId);
            }

            $category = $primaryCategoryId
                ? ServiceCategory::query()->find($primaryCategoryId)
                : null;

            if ($category && $category->cluster !== $cluster) {
                $category = null;
                $primaryCategoryId = null;
            }

            $parentId = (int) ($validated['seo_parent_id'] ?? 0) ?: null;
            if (! $parentId && $category) {
                $catSeo = $this->seoService()->ensureSeoFor($category, 'service_category', $locale, [
                    'slug' => $category->slug,
                    'title' => $category->name,
                    'seo_title' => $category->name,
                    'status' => $category->is_active ? 'published' : 'draft',
                    'parent_id' => $hubSeo?->id,
                ]);
                $parentId = $catSeo->id;
            }
            $parentId = $parentId ?: $hubSeo?->id;

            $seoSlug = $validated['seo_slug'] ?: Str::slug($validated['title']);

            $service = isset($validated['id'])
                ? Service::query()->findOrFail($validated['id'])
                : new Service;

            $attrs = is_array($service->attrs) ? $service->attrs : [];
            if ($cluster === Service::CLUSTER_STAY) {
                $propertyTypes = array_values(array_filter(
                    array_map('strval', (array) $request->input('property_types', []))
                ));
                $primaryPropertyType = (string) ($request->input('property_type') ?: ($propertyTypes[0] ?? ($attrs['property_type'] ?? 'hotel')));
                if ($primaryPropertyType && ! in_array($primaryPropertyType, $propertyTypes, true)) {
                    array_unshift($propertyTypes, $primaryPropertyType);
                }
                if ($propertyTypes !== []) {
                    $attrs['property_types'] = $propertyTypes;
                    $attrs['property_type'] = $primaryPropertyType;
                }
                if ($request->filled('checkin_from')) {
                    $attrs['checkin_from'] = (string) $request->input('checkin_from');
                }
                if ($request->filled('checkout_until')) {
                    $attrs['checkout_until'] = (string) $request->input('checkout_until');
                }
                if ($request->filled('address')) {
                    $attrs['address'] = (string) $request->input('address');
                }
            }

            $status = $validated['status'];
            $service->fill([
                'cluster' => $cluster,
                'service_category_id' => $primaryCategoryId,
                'country_id' => $validated['country_id'] ?? null,
                'code' => $validated['code'] ?? null,
                'price_from' => $validated['price_from'] ?? null,
                'currency' => strtoupper($validated['currency'] ?? 'VND'),
                'rating' => $validated['rating'] ?? $validated['rating_aggregate_star'] ?? 0,
                'review_count' => $validated['review_count'] ?? $validated['rating_aggregate_count'] ?? 0,
                'star_rating' => $validated['star_rating'] ?? null,
                'discount_badge' => $validated['discount_badge'] ?? null,
                'status' => $status,
                'is_featured' => $request->boolean('is_featured'),
                'is_hot_deal' => $request->boolean('is_hot_deal'),
                'sort' => $validated['sort'] ?? 0,
                'attrs' => $attrs,
                'published_at' => $status === 'published'
                    ? ($service->published_at ?? now())
                    : null,
            ]);
            $service->save();

            // Đồng bộ danh mục nhiều-nhiều (service_category_service)
            if ($categoryIds !== []) {
                $service->categories()->sync($categoryIds);
            } elseif ($primaryCategoryId) {
                $service->categories()->sync([$primaryCategoryId]);
            } else {
                $service->categories()->detach();
            }

            $this->saveModelTranslation(
                $service,
                ServiceTranslation::class,
                'service_id',
                $locale,
                [
                    'title' => $validated['title'],
                    'location_label' => $validated['location_label'] ?? null,
                    'summary' => $validated['summary'] ?? null,
                    'content' => $validated['content'] ?? null,
                    'featured_quote_text' => $validated['featured_quote_text'] ?? null,
                    'featured_quote_author' => $validated['featured_quote_author'] ?? null,
                    'highlights' => $this->linesToArray($request->input('highlights')),
                    'inclusions' => $this->linesToArray($request->input('inclusions')),
                    'exclusions' => $this->linesToArray($request->input('exclusions')),
                    'notes' => $this->linesToArray($request->input('notes')),
                ],
                [
                    'title', 'location_label', 'summary', 'content',
                    'featured_quote_text', 'featured_quote_author',
                    'highlights', 'inclusions', 'exclusions', 'notes',
                ],
            );

            $this->saveSeoTranslations(
                $service,
                [
                    $locale => [
                        'slug' => $seoSlug,
                        'title' => $validated['title'],
                        'seo_title' => $validated['seo_title'] ?? $validated['title'],
                        'seo_description' => $validated['seo_description'] ?? ($validated['summary'] ?? null),
                        'keywords' => $validated['seo_keywords'] ?? null,
                        'status' => $status === 'published' ? 'published' : 'draft',
                        'parent_id' => $parentId,
                    ],
                ],
                'service',
                [
                    'rating_aggregate_count' => $validated['rating_aggregate_count'] ?? $service->review_count,
                    'rating_aggregate_star' => $validated['rating_aggregate_star'] ?? $service->rating,
                ],
            );

            $this->syncCoverAttachment($service, $request, config('media.services'));

            return $service;
        });

        return redirect()
            ->route('admin.services.view', [
                'id' => $service->id,
                'cluster' => $service->cluster,
                'language' => $locale,
            ])
            ->with('success', 'Đã lưu sản phẩm dịch vụ thành công.');
    }

    public function delete(Request $request): RedirectResponse
    {
        $service = Service::query()->findOrFail($request->integer('id'));
        $cluster = $service->cluster;

        if ($service->cluster === Service::CLUSTER_STAY) {
            app(ServicePurgeService::class)->purge($service);
        } else {
            $service->delete();
        }

        return redirect()
            ->route('admin.services.list', ['cluster' => $cluster])
            ->with('success', $cluster === Service::CLUSTER_STAY
                ? 'Đã xóa chỗ nghỉ (kèm media & quan hệ).'
                : 'Đã xóa sản phẩm dịch vụ thành công.');
    }
}
