@props([
    'title',
    'breadcrumbs' => [],   // truyền tiếp cho x-layout.breadcrumb
    'subtitle' => null,
    'bannerLabel' => null, // nhãn ảnh banner placeholder
])

{{-- Pattern chuẩn toàn site: banner ảnh + card trắng chứa H1/breadcrumb đè lên --}}
<section class="relative page-header">
    <div class="page-header__banner">
        <x-ph class="h-full w-full" :label="$bannerLabel ?? 'Ảnh banner: ' . $title" icon="photo" icon-class="size-12" />
    </div>
    <div class="container-site">
        <div class="page-header__card-wrap">
            <div class="card page-header__card">
                <x-layout.breadcrumb :items="$breadcrumbs" class="mb-3" />
                <h1 class="page-header__title">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="body-text mt-2">{{ $subtitle }}</p>
                @endif
            </div>
        </div>
    </div>
</section>
