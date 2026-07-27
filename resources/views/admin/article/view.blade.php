@extends('layouts.admin')

@section('title', $title)
@section('page_title', $title)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">{{ $title }}</h1>
        </div>
        <a href="{{ route('admin.articles.list') }}" class="admin-btn admin-btn--secondary">← Quay lại</a>
    </div>

    @include('admin.partials.language-tabs', ['languages' => $languages, 'locale' => $locale])

    <form method="POST" action="{{ route('admin.articles.save') }}" enctype="multipart/form-data" class="space-y-6">
        @csrf
        <input type="hidden" name="id" value="{{ $article?->id }}">
        <input type="hidden" name="language" value="{{ $locale }}">

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Ảnh đại diện</h2>
            @include('admin.components.formImageUpload', [
                'name' => 'image',
                'label' => 'Ảnh đại diện bài viết',
                'currentImage' => $article?->coverUrl(),
                'removeName' => 'remove_image',
                'aspectRatio' => '800/533',
                'tooltip' => 'Ảnh đại diện listing blog / chia sẻ mạng xã hội.',
                'hint' => 'JPG, PNG, WebP — tối đa '.config('media.max_upload_kb').'KB. Tự tối ưu về WebP ≤1920px.',
            ])
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Thông tin chung</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-form-label">Chuyên mục blog</label>
                    <select name="blog_category_id" class="admin-form-input">
                        <option value="">— Chọn —</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(old('blog_category_id', $article?->blog_category_id) == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Quốc gia</label>
                    <select name="country_id" class="admin-form-input">
                        <option value="">— Chọn —</option>
                        @foreach ($countries as $country)
                            <option value="{{ $country->id }}" @selected(old('country_id', $article?->country_id) == $country->id)>{{ $country->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-form-label">Tác giả</label>
                    <input type="text" name="author_name" value="{{ old('author_name', $article?->author_name) }}" class="admin-form-input">
                </div>
                <div>
                    <label class="admin-form-label">Trạng thái *</label>
                    <select name="status" class="admin-form-input" required>
                        @foreach (['draft' => 'Nháp', 'published' => 'Xuất bản', 'archived' => 'Lưu trữ'] as $val => $label)
                            <option value="{{ $val }}" @selected(old('status', $article?->status ?? 'draft') === $val)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Nội dung ({{ strtoupper($locale) }})</h2>
            <div class="grid gap-4">
                <div>
                    <label class="admin-form-label">Tiêu đề *</label>
                    <input type="text" name="title" id="field-title" value="{{ old('title', $translation?->title) }}" class="admin-form-input" required>
                </div>
                <div>
                    <label class="admin-form-label">Mô tả ngắn</label>
                    <textarea name="excerpt" rows="2" class="admin-form-input">{{ old('excerpt', $translation?->excerpt) }}</textarea>
                </div>
                <div>
                    <label class="admin-form-label">Nội dung</label>
                    <textarea name="content" rows="12" class="admin-form-input">{{ old('content', $translation?->content) }}</textarea>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">SEO</h2>
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-form-label">Slug</label>
                    <div class="flex gap-2">
                        <input type="text" name="seo_slug" id="field-seo-slug" value="{{ old('seo_slug', $seoTranslation?->slug) }}" class="admin-form-input">
                        <button type="button" class="admin-btn admin-btn--secondary slug-btn" data-source="field-title" data-target="field-seo-slug">Tạo slug</button>
                    </div>
                </div>
                <div>
                    <label class="admin-form-label">SEO Title</label>
                    <input type="text" name="seo_title" value="{{ old('seo_title', $seoTranslation?->seo_title) }}" class="admin-form-input">
                </div>
                <div class="md:col-span-2">
                    <label class="admin-form-label">SEO Description</label>
                    <textarea name="seo_description" rows="2" class="admin-form-input">{{ old('seo_description', $seoTranslation?->seo_description) }}</textarea>
                </div>
                <div class="md:col-span-2">
                    <label class="admin-form-label">Keywords</label>
                    <input type="text" name="seo_keywords" value="{{ old('seo_keywords', $seoTranslation?->keywords) }}" class="admin-form-input">
                </div>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-lg font-semibold">Tags & Gói liên quan</h2>
            <div class="grid gap-6 md:grid-cols-2">
                <div>
                    <label class="admin-form-label">Loại nội dung</label>
                    <div class="max-h-40 space-y-1 overflow-y-auto rounded border border-slate-200 p-3">
                        @foreach ($contentTypeTags as $tag)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="content_type_tag_ids[]" value="{{ $tag->id }}"
                                    @checked(in_array($tag->id, old('content_type_tag_ids', $article?->contentTypeTags?->pluck('id')->all() ?? [])))>
                                {{ $tag->label }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label class="admin-form-label">Từ khóa</label>
                    <div class="max-h-40 space-y-1 overflow-y-auto rounded border border-slate-200 p-3">
                        @foreach ($keywordTags as $tag)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="keyword_tag_ids[]" value="{{ $tag->id }}"
                                    @checked(in_array($tag->id, old('keyword_tag_ids', $article?->keywordTags?->pluck('id')->all() ?? [])))>
                                {{ $tag->label }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div class="md:col-span-2">
                    <label class="admin-form-label">Gói tour liên quan</label>
                    <div class="max-h-48 space-y-1 overflow-y-auto rounded border border-slate-200 p-3">
                        @foreach ($packages as $pkg)
                            <label class="flex items-center gap-2 text-sm">
                                <input type="checkbox" name="related_package_ids[]" value="{{ $pkg->id }}"
                                    @checked(in_array($pkg->id, old('related_package_ids', $article?->relatedPackages?->pluck('id')->all() ?? [])))>
                                [{{ $pkg->type }}] {{ $pkg->title }}
                            </label>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="flex gap-3">
            <button type="submit" class="admin-btn admin-btn--primary">Lưu</button>
            @if ($article)
                <a href="{{ route('admin.articles.delete', ['id' => $article->id]) }}" class="admin-btn admin-btn--secondary text-red-600" onclick="return confirm('Xóa bài viết này?')">Xóa</a>
            @endif
        </div>
    </form>
@endsection

@push('scripts')
<script>
document.querySelectorAll('.slug-btn').forEach(btn => {
    btn.addEventListener('click', () => {
        const source = document.getElementById(btn.dataset.source);
        const target = document.getElementById(btn.dataset.target);
        if (!source?.value) return;
        fetch('{{ route('admin.helper.slug') }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
            body: JSON.stringify({str: source.value})
        }).then(r => r.json()).then(d => { if (d.slug) target.value = d.slug; });
    });
});
</script>
@endpush
