@props([
    'title',
    'breadcrumbs' => [],   // truyền tiếp cho x-layout.breadcrumb
    'subtitle' => null,
    'bannerLabel' => null, // nhãn ảnh banner placeholder
])

{{-- Pattern chuẩn toàn site: banner ảnh + card trắng chứa H1/breadcrumb đè lên --}}
<section class="relative">
    <x-ph class="h-44 w-full sm:h-56 lg:h-64" :label="$bannerLabel ?? 'Ảnh banner: ' . $title" icon="photo" icon-class="size-12" />
    <div class="container-site">
        <div class="relative -mt-14 sm:-mt-16">
            <div class="card max-w-3xl px-6 py-5 sm:px-8">
                <h1 class="font-display text-3xl font-bold tracking-tight text-balance sm:text-4xl lg:text-5xl">{{ $title }}</h1>
                @if ($subtitle)
                    <p class="body-text mt-2">{{ $subtitle }}</p>
                @endif
                <x-layout.breadcrumb :items="$breadcrumbs" class="mt-3" />
            </div>
        </div>
    </div>
</section>
